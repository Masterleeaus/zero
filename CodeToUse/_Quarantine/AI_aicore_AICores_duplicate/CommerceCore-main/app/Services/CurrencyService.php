<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class CurrencyService
{
    /**
     * Define the base currency (system default)
     */
    protected $baseCurrency = 'USD';

    /**
     * Supported currencies for the platform
     */
    protected $supportedCurrencies = [
        'USD' => ['symbol' => '$', 'name' => 'US Dollar'],
        'EUR' => ['symbol' => '€', 'name' => 'Euro'],
        'GBP' => ['symbol' => '£', 'name' => 'British Pound'],
        'AUD' => ['symbol' => 'A$', 'name' => 'Australian Dollar'],
        'CAD' => ['symbol' => 'C$', 'name' => 'Canadian Dollar'],
        'SGD' => ['symbol' => 'S$', 'name' => 'Singapore Dollar'],
        'JPY' => ['symbol' => '¥', 'name' => 'Japanese Yen'],
        'INR' => ['symbol' => '₹', 'name' => 'Indian Rupee'],
    ];

    /**
     * Convert an amount from base currency to target currency
     */
    public function convert($amount, $targetCurrency = null)
    {
        if (is_null($targetCurrency)) {
            $targetCurrency = $this->getUserCurrency();
        }

        if ($targetCurrency === $this->baseCurrency) {
            return $amount;
        }

        $rate = $this->getExchangeRate($this->baseCurrency, $targetCurrency);
        
        return $amount * $rate;
    }

    /**
     * Get the formatted string for an amount with the correct symbol
     */
    public function format($amount, $targetCurrency = null)
    {
        $targetCurrency = $targetCurrency ?? $this->getUserCurrency();
        $converted = $this->convert($amount, $targetCurrency);
        $symbol = $this->supportedCurrencies[$targetCurrency]['symbol'] ?? '$';
        
        return $symbol . number_format($converted, 2);
    }

    /**
     * Returns the symbol of the currently active currency
     */
    public function symbol()
    {
        $currency = $this->getUserCurrency();
        return $this->supportedCurrencies[$currency]['symbol'] ?? '$';
    }

    /**
     * Retrieves the target currency based on user preferences, sessions or IP geolocation
     */
    public function getUserCurrency()
    {
        if (request()->hasSession() && request()->has('currency') && array_key_exists(request()->currency, $this->supportedCurrencies)) {
            Session::put('currency', request()->currency);
            return request()->currency;
        }

        if (request()->hasSession() && Session::has('currency')) {
            return Session::get('currency');
        }

        // Advanced Localization feature mapping GeoIP to currency
        // using mock configuration logic to fallback to USD
        return config('app.currency', 'USD');
    }

    /**
     * Fetch Exchange rates (Mocking an API response and caching it for 12 hours)
     */
    protected function getExchangeRate($from, $to)
    {
        $cacheKey = "exchange_rate_{$from}_{$to}";

        return Cache::remember($cacheKey, 60 * 60 * 12, function () use ($from, $to) {
            // Ideally, fetching from openexchangerates.org or similar
            // $response = Http::get("https://api.exchangerate-api.com/v4/latest/{$from}");
            // return $response->json()['rates'][$to] ?? 1;

            // Mock rates for the sake of the platform demonstration
            $mockRates = [
                'USD_EUR' => 0.92,
                'USD_GBP' => 0.79,
                'USD_AUD' => 1.52,
                'USD_CAD' => 1.35,
                'USD_SGD' => 1.34,
                'USD_JPY' => 150.25,
                'USD_INR' => 82.90,
            ];

            return $mockRates["{$from}_{$to}"] ?? 1.00;
        });
    }

    /**
     * Get the list of all supported currencies
     */
    public function getSupported()
    {
        return $this->supportedCurrencies;
    }
}
