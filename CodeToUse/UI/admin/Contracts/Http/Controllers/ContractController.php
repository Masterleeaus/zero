<?php

namespace Modules\Contracts\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Modules\Contracts\Entities\{Contract, ContractVersion, ContractSigner, SignatureEvent};
use Modules\Contracts\Http\Requests\StoreContractRequest;

class ContractController extends AccountBaseController
{
    private function number(): string
    {
        return 'C-' . now()->format('Y') . '-' . strtoupper(Str::random(5));
    }

    public function index()
    {
        $contracts = Contract::latest()->paginate(20);
        $this->pageTitle = 'Contracts';
        return view('contracts::contracts.index', compact('contracts'));
    }

    public function create()
    {
        $this->pageTitle = 'Create Contract';
        return view('contracts::contracts.create');
    }

    public function store(StoreContractRequest $request)
    {
        $data = $request->validated();

        $contract = Contract::create([
            'number' => $this->number(),
            'title' => $data['title'],
            'client_id' => $data['client_id'] ?? null,
            'status' => 'draft',
            'effective_date' => $data['effective_date'] ?? null,
            'expiry_date' => $data['expiry_date'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        $ver = ContractVersion::create([
            'contract_id' => $contract->id,
            'version' => 1,
            'body_html' => $data['body_html'] ?? '',
            'body_text' => strip_tags($data['body_html'] ?? ''),
            'hash' => hash('sha256', (string)($data['body_html'] ?? '')),
        ]);
        $contract->current_version_id = $ver->id;
        $contract->save();

        foreach ($data['signers'] as $i => $sg) {
            ContractSigner::create([
                'contract_id' => $contract->id,
                'name' => $sg['name'],
                'email' => $sg['email'],
                'role' => $sg['role'] ?? 'signer',
                'order' => $i + 1,
            ]);
        }

        return redirect()->route('contracts.show', $contract->id)->with('success', 'Contract drafted');
    }

    public function show($id)
    {
        $contract = Contract::with(['versions','signers','events'])->findOrFail($id);
        $this->pageTitle = 'Contract ' . $contract->number;
        return view('contracts::contracts.show', compact('contract'));
    }

    public function send($id, Request $request)
    {
        $contract = Contract::with(['signers'])->findOrFail($id);
        $expires = now()->addDays((int) config('contracts.public_link_days', 30));
        $publicUrl = URL::temporarySignedRoute('contracts.public.show', $expires, ['id' => $contract->id]);

        foreach ($contract->signers as $sg) {
            \Mail::to($sg->email)->send(new \Modules\Contracts\Mail\ContractSent($contract, $publicUrl));
        }

        $contract->status = 'sent';
        $contract->sent_at = now();
        $contract->save();

        SignatureEvent::create(['contract_id' => $contract->id, 'type' => 'sent', 'payload' => ['public_url' => $publicUrl]]);
        return back()->with('success', 'Contract sent to signers');
    }

    // Public signed routes
    public function publicShow($id, Request $request)
    {
        $contract = Contract::with(['signers'])->findOrFail($id);
        $ver = $contract->versions()->orderByDesc('version')->first();
        return view('contracts::contracts.public', compact('contract','ver'));
    }

    public function sign($id, Request $request)
    {
        $contract = Contract::with(['signers'])->findOrFail($id);
        $name = (string) $request->input('name');
        $email = (string) $request->input('email');
        $sig = (string) $request->input('signature');

        $signer = $contract->signers()->where('email', $email)->first();
        abort_unless($signer, 403, 'Signer not recognised');

        $signer->signature_text = $sig ?: $name;
        $signer->signed_at = now();
        $signer->ip = $request->ip();
        $signer->user_agent = (string) $request->userAgent();
        $signer->save();

        SignatureEvent::create(['contract_id' => $contract->id, 'type' => 'signed', 'payload' => ['email' => $email, 'ip' => $request->ip()]]);

        // If all signers signed -> mark signed
        if ($contract->signers()->whereNull('signed_at')->count() === 0) {
            $contract->status = 'signed';
            $contract->signed_at = now();
            $contract->save();
        }

        // Webhook
        try {
            $url = (string) config('contracts.webhook.event_url', '');
            if (!empty($url)) {
                $payload = [
                    'event' => 'contract.signed',
                    'contract_id' => $contract->id,
                    'number' => $contract->number,
                    'signer_email' => $email,
                    'signed_at' => now()->toAtomString(),
                ];
                $secret = (string) config('contracts.webhook.secret', '');
                $sigH = hash_hmac('sha256', json_encode($payload), $secret ?: 'no-secret');
                if (class_exists('Illuminate\\Support\\Facades\\Http')) {
                    \Illuminate\Support\Facades\Http::withHeaders(['X-Contracts-Signature' => $sigH])->post($url, $payload);
                }
            }
        } catch (\Throwable $e) {}

        return redirect()->route('contracts.public.show', ['id' => $contract->id, 'signature' => $request->query('signature')])->with('success', 'Signature recorded');
    }

    public function decline($id, Request $request)
    {
        $contract = Contract::findOrFail($id);
        $contract->status = 'declined';
        $contract->declined_at = now();
        $contract->save();
        SignatureEvent::create(['contract_id' => $contract->id, 'type' => 'declined', 'payload' => ['reason' => (string)$request->input('reason','')]]);
        return view('contracts::contracts.public_result', ['contract' => $contract, 'result' => 'declined']);
    }
}
