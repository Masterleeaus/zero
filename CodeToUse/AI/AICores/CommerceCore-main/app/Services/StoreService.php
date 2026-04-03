<?php

namespace App\Services;

use App\Models\Store;
use App\Models\StoreSetting;
use Illuminate\Support\Str;

class StoreService
{
    public function create(array $data): Store
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        $store = Store::create($data);

        // Create default settings
        StoreSetting::create([
            'store_id' => $store->id,
            'settings_json' => [
                'primary_color' => '#4F46E5',
                'secondary_color' => '#7C3AED',
                'font' => 'Inter',
            ],
        ]);

        return $store;
    }

    public function update(Store $store, array $data): Store
    {
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $store->update($data);
        return $store->fresh();
    }

    public function updateSettings(Store $store, array $settings): StoreSetting
    {
        $storeSetting = $store->settings ?? new StoreSetting(['store_id' => $store->id]);
        $storeSetting->settings_json = array_merge($storeSetting->settings_json ?? [], $settings);

        if (isset($settings['theme_id'])) {
            $storeSetting->theme_id = $settings['theme_id'];
        }

        $storeSetting->save();
        return $storeSetting;
    }

    public function getStats(Store $store): array
    {
        return [
            'total_products' => $store->products()->count(),
            'active_products' => $store->products()->where('status', 'active')->count(),
            'total_orders' => $store->orders()->count(),
            'total_revenue' => $store->orders()->where('status', '!=', 'cancelled')->sum('total_price'),
            'pending_orders' => $store->orders()->where('status', 'pending')->count(),
            'delivered_orders' => $store->orders()->where('status', 'delivered')->count(),
            'total_pages' => $store->pages()->count(),
        ];
    }
}
