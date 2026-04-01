<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Payment;
use App\Models\Store;
use App\Services\SSLCommerzService;
use App\Services\SubscriptionService;
use App\Traits\ResolvesStore;
use Illuminate\Support\Str;

class BillingController extends Controller
{
    use ResolvesStore;

    public function index(Request $request)
    {
        $user  = $request->user();
        $plans = Plan::where('is_active', true)->get();

        // ✅ Fixed: use ownedStores() for store_owner, store relationship for staff
        if ($user->isStoreOwner()) {
            $store = $user->ownedStores()->first();
        } elseif ($user->isStaff()) {
            $store = $user->store;
        } else {
            $store = null;
        }

        if (!$store) {
            return redirect()->route('admin.stores.create')
                ->with('warning', 'Please create a store before accessing billing.');
        }

        $activeSubscription = $store->activeSubscription();
        $payments           = $store->payments()->latest()->take(10)->get();

        return view('admin.billing.index', compact('plans', 'store', 'activeSubscription', 'payments'));
    }

    public function checkout(Request $request, Plan $plan, SSLCommerzService $sslService)
    {
        $user  = $request->user();

        // ✅ Fixed: proper store resolution for owner/staff
        if ($user->isStoreOwner()) {
            $store = $user->ownedStores()->firstOrFail();
        } elseif ($user->isStaff()) {
            $store = $user->store ?? abort(403, 'No store assigned.');
        } else {
            abort(403, 'Only store owners or staff can subscribe to a plan.');
        }

        $paymentData = [
            'amount'         => $plan->price,
            'currency'       => 'BDT',
            'transaction_id' => Str::uuid()->toString(),
            'customer_name'  => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->phone ?? '01711111111',
            'plan_name'      => $plan->name,
            'store_db_id'    => $store->id,
            'plan_db_id'     => $plan->id,
        ];

        // Create pending payment record
        Payment::create([
            'store_id'       => $store->id,
            'amount'         => $plan->price,
            'transaction_id' => $paymentData['transaction_id'],
            'status'         => 'pending',
        ]);

        $url = $sslService->initiatePayment($paymentData);

        if ($url) {
            return redirect($url);
        }

        return back()->with('error', 'Could not initiate payment gateway.');
    }

    public function success(Request $request, SSLCommerzService $sslService, SubscriptionService $subService)
    {
        $valId  = $request->input('val_id');
        $transId = $request->input('tran_id');

        $validation = $sslService->validateSession($valId);

        if ($validation && $validation['status'] === 'VALID') {
            $payment = Payment::where('transaction_id', $transId)->firstOrFail();

            if ($payment->status === 'pending') {
                $payment->update([
                    'status'         => 'completed',
                    'payment_method' => $validation['card_type'] ?? 'Unknown',
                    'details'        => json_encode($validation),
                ]);

                $store        = Store::find($validation['value_a']);
                $plan         = Plan::find($validation['value_b']);
                $subscription = $subService->changePlan($store, $plan);
                $payment->update(['subscription_id' => $subscription->id]);
            }

            return redirect()->route('admin.dashboard')->with('success', 'Plan upgraded successfully!');
        }

        return redirect()->route('admin.dashboard')->with('error', 'Payment validation failed.');
    }

    public function fail(Request $request)
    {
        return redirect()->route('admin.dashboard')->with('error', 'Payment failed or cancelled.');
    }

    public function cancel(Request $request)
    {
        return redirect()->route('admin.dashboard')->with('warning', 'Payment cancelled.');
    }

    public function ipn(Request $request)
    {
        return response()->json(['status' => 'received']);
    }
}
