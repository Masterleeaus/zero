<?php

namespace App\Services;

use App\Models\BlockedPhone;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Support\Carbon;

class FraudDetectionService
{
    /**
     * Checks if a phone number is explicitly blocked by the store.
     */
    public function isPhoneBlocked(Store $store, string $phone): bool
    {
        return BlockedPhone::where('store_id', $store->id)
            ->where('phone_number', $phone)
            ->exists();
    }

    /**
     * Checks if an identical order has been submitted too recently.
     * Prevents multi-click spam or accidental duplicates.
     */
    public function isDuplicateSubmission(Store $store, string $phone, float $totalPrice): bool
    {
        // Check orders from the same phone amount within the last 5 minutes
        return Order::where('store_id', $store->id)
            ->where('phone', $phone)
            ->where('total_price', $totalPrice)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();
    }

    /**
     * Evaluates an incoming checkout request and returns an array of errors if it fails fraud checks.
     */
    public function evaluateOrder(Store $store, string $phone, float $totalPrice): array
    {
        $errors = [];

        if ($this->isPhoneBlocked($store, $phone)) {
            $errors[] = 'This phone number is restricted from placing orders.';
        }

        if ($this->isDuplicateSubmission($store, $phone, $totalPrice)) {
            $errors[] = 'A similar order was just placed. Please wait a few minutes before trying again.';
        }

        return $errors;
    }
}
