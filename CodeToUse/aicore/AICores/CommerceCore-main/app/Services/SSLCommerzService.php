<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SSLCommerzService
{
    protected $storeId;
    protected $storePassword;
    protected $apiUrl;
    protected $isSandbox;

    public function __construct()
    {
        $this->storeId = config('services.sslcommerz.store_id');
        $this->storePassword = config('services.sslcommerz.store_password');
        $this->isSandbox = config('services.sslcommerz.sandbox', true);

        $this->apiUrl = $this->isSandbox 
            ? 'https://sandbox.sslcommerz.com' 
            : 'https://securepay.sslcommerz.com';
    }

    /**
     * Initiate a payment and get the gateway URL
     */
    public function initiatePayment($paymentData)
    {
        $postData = [
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
            'total_amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'] ?? 'BDT',
            'tran_id' => $paymentData['transaction_id'] ?? Str::uuid()->toString(),
            'success_url' => route('payment.success'),
            'fail_url' => route('payment.fail'),
            'cancel_url' => route('payment.cancel'),
            'ipn_url' => route('payment.ipn'),
            'cus_name' => $paymentData['customer_name'] ?? 'Store Owner',
            'cus_email' => $paymentData['customer_email'] ?? 'test@example.com',
            'cus_add1' => 'Dhaka',
            'cus_city' => 'Dhaka',
            'cus_postcode' => '1000',
            'cus_country' => 'Bangladesh',
            'cus_phone' => $paymentData['customer_phone'] ?? '01711111111',
            'shipping_method' => 'NO',
            'product_name' => $paymentData['plan_name'] ?? 'SaaS Plan',
            'product_category' => 'Software',
            'product_profile' => 'non-physical-goods',
            'value_a' => $paymentData['store_db_id'] ?? '', // Pass local store ID
            'value_b' => $paymentData['plan_db_id'] ?? '', // Pass plan ID
        ];

        $response = Http::asForm()->post($this->apiUrl . '/gwprocess/v4/api.php', $postData);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['status']) && $data['status'] === 'SUCCESS') {
                return $data['GatewayPageURL'];
            }
        }

        return null;
    }

    /**
     * Validate the IPN or Callback response
     */
    public function validateSession($valId)
    {
        $response = Http::get($this->apiUrl . '/validator/api/validationserverAPI.php', [
            'val_id' => $valId,
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
            'v' => 1,
            'format' => 'json'
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }
}
