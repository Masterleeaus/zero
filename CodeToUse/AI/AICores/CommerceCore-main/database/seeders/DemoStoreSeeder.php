<?php

namespace Database\Seeders;

use App\Models\BuilderContent;
use App\Models\BuilderPage;
use App\Models\BuilderSection;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Models\StoreSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoStoreSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure standard plans exist
        $proPlan = Plan::where('slug', 'pro')->first() ?? Plan::create([
            'name' => 'Pro',
            'slug' => 'pro',
            'price' => 29.99,
            'max_products' => 500,
            'max_pages' => 25,
            'features' => ['advanced_analytics', 'custom_domain', 'priority_support'],
            'is_active' => true,
        ]);

        // Demo Owner
        $owner = User::where('email', 'demo@commercecore.com')->first();
        if (!$owner) {
            $owner = User::create([
                'name' => 'Demo Store Owner',
                'email' => 'demo@commercecore.com',
                'password' => bcrypt('password'),
                'role' => 'store_owner',
            ]);
            $owner->assignRole('store_owner');
        }

        // Demo Store
        $store = Store::where('slug', 'demo')->first();
        if (!$store) {
            $store = Store::create([
                'name' => 'CommerceCore Demo Store',
                'slug' => 'demo',
                'owner_id' => $owner->id,
                'description' => 'A showcase of CommerceCore Builder PRO capabilities.',
                'status' => 'active',
                'plan_id' => $proPlan->id,
            ]);
        }
        
        $owner->update(['store_id' => $store->id]);

        // Categories
        $electronics = Category::updateOrCreate(
            ['slug' => 'electronics', 'store_id' => $store->id],
            ['name' => 'Electronics', 'is_active' => true]
        );

        $fashion = Category::updateOrCreate(
            ['slug' => 'fashion', 'store_id' => $store->id],
            ['name' => 'Fashion', 'is_active' => true]
        );

        // Products
        $p1 = Product::updateOrCreate(
            ['store_id' => $store->id, 'slug' => 'neural-headphones'],
            [
                'name' => 'Neural Wireless Headphones',
                'category_id' => $electronics->id,
                'price' => 249.00,
                'compare_price' => 299.00,
                'stock' => 50,
                'status' => 'active',
                'featured' => true,
                'description' => 'Premium noise-cancelling headphones with AI-driven sound optimization.'
            ]
        );

        $p2 = Product::updateOrCreate(
            ['store_id' => $store->id, 'slug' => 'smart-watch-ultra'],
            [
                'name' => 'Smart Watch Ultra',
                'category_id' => $electronics->id,
                'price' => 399.00,
                'compare_price' => 449.00,
                'stock' => 30,
                'status' => 'active',
                'featured' => true,
                'description' => 'Advanced health monitoring and multi-sport tracking.'
            ]
        );

        $p3 = Product::updateOrCreate(
            ['store_id' => $store->id, 'slug' => 'minimalist-jacket'],
            [
                'name' => 'Minimalist Leather Jacket',
                'category_id' => $fashion->id,
                'price' => 199.00,
                'compare_price' => 299.00,
                'stock' => 15,
                'status' => 'active',
                'featured' => true,
                'description' => 'Sleek, sustainable leather jacket for the style-conscious professional.'
            ]
        );

        // Builder Page
        $homePage = BuilderPage::updateOrCreate(
            ['store_id' => $store->id, 'slug' => 'home'],
            [
                'page_name' => 'Homepage',
                'is_published' => true,
                'is_homepage' => true,
                'sort_order' => 0
            ]
        );

        // Sections
        $hero = BuilderSection::updateOrCreate(
            ['page_id' => $homePage->id, 'type' => 'hero', 'store_id' => $store->id],
            ['position' => 0, 'is_active' => true]
        );

        $heroContent = [
            'title' => 'The Future of E-commerce is Here',
            'subtitle' => 'Experience the power of CommerceCore with our premium demo storefront.',
            'button_text' => 'View Catalog',
            'button_url' => '/store/demo/products',
        ];

        foreach ($heroContent as $key => $value) {
            BuilderContent::updateOrCreate(
                ['section_id' => $hero->id, 'key' => $key, 'store_id' => $store->id],
                ['value' => $value]
            );
        }

        $grid = BuilderSection::updateOrCreate(
            ['page_id' => $homePage->id, 'type' => 'product_grid', 'store_id' => $store->id],
            ['position' => 1, 'is_active' => true]
        );

        $gridContent = [
            'title' => 'Featured Essentials',
            'subtitle' => 'Handpicked products from our demo collection',
            'count' => '3',
        ];

        foreach ($gridContent as $key => $value) {
            BuilderContent::updateOrCreate(
                ['section_id' => $grid->id, 'key' => $key, 'store_id' => $store->id],
                ['value' => $value]
            );
        }

        echo "\n✅ Demo Store Ready: CommerceCore Demo Store\n";
        echo "   Owner: demo@commercecore.com / password\n";
        echo "   URL: /store/demo\n";
    }
}
