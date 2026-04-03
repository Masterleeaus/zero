<?php

namespace Database\Seeders;

use App\Models\BuilderContent;
use App\Models\BuilderPage;
use App\Models\BuilderSection;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreSetting;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        // ---- Plans ----
        $freePlan = Plan::create([
            'name' => 'Free',
            'slug' => 'free',
            'price' => 0,
            'max_products' => 10,
            'max_pages' => 3,
            'features' => ['basic_analytics'],
            'is_active' => true,
        ]);

        $proPlan = Plan::create([
            'name' => 'Pro',
            'slug' => 'pro',
            'price' => 29.99,
            'max_products' => 500,
            'max_pages' => 25,
            'features' => ['advanced_analytics', 'custom_domain', 'priority_support'],
            'is_active' => true,
        ]);

        $enterprisePlan = Plan::create([
            'name' => 'Enterprise',
            'slug' => 'enterprise',
            'price' => 99.99,
            'max_products' => -1,
            'max_pages' => -1,
            'features' => ['advanced_analytics', 'custom_domain', 'priority_support', 'api_access', 'white_label'],
            'is_active' => true,
        ]);

        $theme = Theme::create([
            'name' => 'Modern',
            'config_json' => [
                'primary' => '#4F46E5',
                'secondary' => '#7C3AED',
                'font' => 'Inter',
            ],
            'is_active' => true,
        ]);

        // ---- Users ----
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@commercecore.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
        ]);
        $admin->assignRole('super_admin');

        $owner1 = User::create([
            'name' => 'John Store Owner',
            'email' => 'owner@commercecore.com',
            'password' => bcrypt('password'),
            'role' => 'store_owner',
        ]);
        $owner1->assignRole('store_owner');

        $customer = User::create([
            'name' => 'Jane Customer',
            'email' => 'customer@commercecore.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);
        $customer->assignRole('customer');

        // ---- Stores ----
        $store1 = Store::create([
            'name' => 'TechVault',
            'slug' => 'techvault',
            'owner_id' => $owner1->id,
            'description' => 'Premium technology products and accessories at competitive prices.',
            'status' => 'active',
            'plan_id' => $proPlan->id,
        ]);

        $store2 = Store::create([
            'name' => 'FashionHouse',
            'slug' => 'fashionhouse',
            'owner_id' => $owner1->id,
            'description' => 'Curated fashion for the modern lifestyle.',
            'status' => 'active',
            'plan_id' => $freePlan->id,
        ]);

        $owner1->update(['store_id' => $store1->id]);

        StoreSetting::create([
            'store_id' => $store1->id,
            'theme_id' => $theme->id,
            'settings_json' => [
                'primary_color' => '#4F46E5',
                'secondary_color' => '#7C3AED',
                'font' => 'Inter',
            ],
        ]);

        // ---- Categories ----
        $techCategories = [];
        foreach (['Laptops', 'Smartphones', 'Audio', 'Accessories', 'Gaming'] as $catName) {
            $techCategories[$catName] = Category::create([
                'name' => $catName,
                'slug' => Str::slug($catName),
                'store_id' => $store1->id,
                'is_active' => true,
            ]);
        }

        $fashionCategories = [];
        foreach (['Men', 'Women', 'Kids', 'Shoes', 'Watches'] as $catName) {
            $fashionCategories[$catName] = Category::create([
                'name' => $catName,
                'slug' => Str::slug($catName),
                'store_id' => $store2->id,
                'is_active' => true,
            ]);
        }

        // ---- Products (TechVault) ----
        $techProducts = [
            ['name' => 'MacBook Pro 16"', 'price' => 2499.99, 'compare_price' => 2799.99, 'category' => 'Laptops', 'stock' => 15, 'featured' => true, 'desc' => 'The most powerful MacBook Pro ever with M3 Max chip, 36GB memory, and stunning Liquid Retina XDR display.'],
            ['name' => 'Sony WH-1000XM5', 'price' => 349.99, 'compare_price' => 399.99, 'category' => 'Audio', 'stock' => 42, 'featured' => true, 'desc' => 'Industry-leading noise cancellation headphones with premium sound quality and 30-hour battery life.'],
            ['name' => 'iPhone 15 Pro Max', 'price' => 1199.99, 'compare_price' => null, 'category' => 'Smartphones', 'stock' => 28, 'featured' => true, 'desc' => 'Titanium design, A17 Pro chip, 48MP camera system, and Action button. The ultimate iPhone.'],
            ['name' => 'Samsung Galaxy S24 Ultra', 'price' => 1299.99, 'compare_price' => 1399.99, 'category' => 'Smartphones', 'stock' => 20, 'featured' => true, 'desc' => 'Galaxy AI built-in, titanium frame, 200MP camera, S Pen included.'],
            ['name' => 'AirPods Pro 2nd Gen', 'price' => 249.99, 'compare_price' => null, 'category' => 'Audio', 'stock' => 65, 'featured' => false, 'desc' => 'Active noise cancellation, adaptive audio, personalized spatial audio.'],
            ['name' => 'Mechanical Keyboard RGB', 'price' => 129.99, 'compare_price' => 159.99, 'category' => 'Accessories', 'stock' => 34, 'featured' => false, 'desc' => 'Cherry MX switches, per-key RGB lighting, aircraft-grade aluminum frame.'],
            ['name' => 'Gaming Monitor 27" 4K', 'price' => 549.99, 'compare_price' => 699.99, 'category' => 'Gaming', 'stock' => 12, 'featured' => true, 'desc' => '144Hz refresh rate, 1ms response time, IPS panel with HDR600 support.'],
            ['name' => 'USB-C Hub 7-in-1', 'price' => 49.99, 'compare_price' => null, 'category' => 'Accessories', 'stock' => 80, 'featured' => false, 'desc' => 'HDMI 4K, USB-A 3.0, SD card reader, PD charging. Premium aluminum build.'],
            ['name' => 'Wireless Mouse Ergonomic', 'price' => 79.99, 'compare_price' => 89.99, 'category' => 'Accessories', 'stock' => 55, 'featured' => false, 'desc' => 'Vertical ergonomic design, 4000 DPI sensor, Bluetooth + USB receiver.'],
            ['name' => 'PS5 DualSense Controller', 'price' => 69.99, 'compare_price' => null, 'category' => 'Gaming', 'stock' => 40, 'featured' => true, 'desc' => 'Haptic feedback, adaptive triggers, built-in microphone. Available in Cosmic Red.'],
        ];

        foreach ($techProducts as $p) {
            Product::create([
                'store_id' => $store1->id,
                'name' => $p['name'],
                'slug' => Str::slug($p['name']),
                'price' => $p['price'],
                'compare_price' => $p['compare_price'],
                'category_id' => $techCategories[$p['category']]->id,
                'stock' => $p['stock'],
                'status' => 'active',
                'featured' => $p['featured'],
                'description' => $p['desc'],
            ]);
        }

        // ---- Products (FashionHouse) ----
        $fashionProducts = [
            ['name' => 'Premium Leather Jacket', 'price' => 299.99, 'compare_price' => 399.99, 'category' => 'Men', 'stock' => 8, 'featured' => true, 'desc' => 'Genuine Italian leather, silk-lined interior, modern slim fit.'],
            ['name' => 'Silk Evening Dress', 'price' => 189.99, 'compare_price' => null, 'category' => 'Women', 'stock' => 12, 'featured' => true, 'desc' => 'Handcrafted pure silk, flowing silhouette, available in midnight blue.'],
            ['name' => 'Classic Chronograph Watch', 'price' => 449.99, 'compare_price' => 549.99, 'category' => 'Watches', 'stock' => 6, 'featured' => true, 'desc' => 'Swiss movement, sapphire crystal, genuine leather strap. 5ATM water resistance.'],
            ['name' => 'Running Sneakers Ultra', 'price' => 159.99, 'compare_price' => null, 'category' => 'Shoes', 'stock' => 30, 'featured' => true, 'desc' => 'React foam sole, breathable mesh upper, reflective details.'],
        ];

        foreach ($fashionProducts as $p) {
            Product::create([
                'store_id' => $store2->id,
                'name' => $p['name'],
                'slug' => Str::slug($p['name']),
                'price' => $p['price'],
                'compare_price' => $p['compare_price'],
                'category_id' => $fashionCategories[$p['category']]->id,
                'stock' => $p['stock'],
                'status' => 'active',
                'featured' => $p['featured'],
                'description' => $p['desc'],
            ]);
        }

        // ---- Sample Orders ----
        $products = Product::where('store_id', $store1->id)->take(3)->get();
        for ($i = 0; $i < 5; $i++) {
            $order = Order::create([
                'store_id' => $store1->id,
                'user_id' => $customer->id,
                'order_number' => Order::generateOrderNumber(),
                'customer_name' => 'Jane Customer',
                'customer_email' => 'customer@commercecore.com',
                'phone' => '+1-555-' . str_pad(rand(1000, 9999), 4, '0'),
                'address' => '123 Main St, New York, NY 10001',
                'subtotal' => 0,
                'tax' => 0,
                'total_price' => 0,
                'status' => ['pending', 'paid', 'shipped', 'delivered', 'pending'][$i],
            ]);

            $orderTotal = 0;
            foreach ($products->random(rand(1, 3)) as $product) {
                $qty = rand(1, 3);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'price' => $product->price,
                ]);
                $orderTotal += $product->price * $qty;
            }

            $order->update([
                'subtotal' => $orderTotal,
                'tax' => round($orderTotal * 0.08, 2),
                'total_price' => round($orderTotal * 1.08, 2),
            ]);
        }

        // ---- Builder Pages & Sections ----
        $homepage = BuilderPage::create([
            'store_id' => $store1->id,
            'page_name' => 'Homepage',
            'slug' => 'homepage',
            'is_homepage' => true,
            'is_published' => true,
            'sort_order' => 0,
        ]);

        // Hero section
        $heroSection = BuilderSection::create([
            'page_id' => $homepage->id,
            'type' => 'hero',
            'position' => 0,
            'is_active' => true,
        ]);
        foreach ([
            'title' => 'Welcome to TechVault',
            'subtitle' => 'Discover premium technology products at unbeatable prices',
            'button_text' => 'Shop Now',
            'button_url' => '/store/techvault/products',
        ] as $key => $value) {
            BuilderContent::create(['section_id' => $heroSection->id, 'key' => $key, 'value' => $value]);
        }

        // Product grid section
        $gridSection = BuilderSection::create([
            'page_id' => $homepage->id,
            'type' => 'product_grid',
            'position' => 1,
            'is_active' => true,
        ]);
        foreach ([
            'title' => 'Trending Products',
            'subtitle' => 'Our most popular items this week',
            'count' => '8',
        ] as $key => $value) {
            BuilderContent::create(['section_id' => $gridSection->id, 'key' => $key, 'value' => $value]);
        }

        // Banner section
        $bannerSection = BuilderSection::create([
            'page_id' => $homepage->id,
            'type' => 'banner',
            'position' => 2,
            'is_active' => true,
        ]);
        foreach ([
            'title' => '🔥 Flash Sale — Up to 40% Off',
            'subtitle' => 'Limited time offer on selected premium tech accessories',
            'button_text' => 'View Deals',
            'button_url' => '/store/techvault/products',
        ] as $key => $value) {
            BuilderContent::create(['section_id' => $bannerSection->id, 'key' => $key, 'value' => $value]);
        }

        // CTA section
        $ctaSection = BuilderSection::create([
            'page_id' => $homepage->id,
            'type' => 'cta',
            'position' => 3,
            'is_active' => true,
        ]);
        foreach ([
            'title' => 'Join 50,000+ Happy Customers',
            'subtitle' => 'Subscribe to get exclusive deals and early access to new products',
            'button_text' => 'Browse Collection',
            'button_url' => '/store/techvault/products',
        ] as $key => $value) {
            BuilderContent::create(['section_id' => $ctaSection->id, 'key' => $key, 'value' => $value]);
        }

        echo "\n✅ Seeded: 3 users, 2 stores, 14 products, 5 orders, 10 categories, 1 builder page with 4 sections\n";
        echo "   Admin login: admin@commercecore.com / password\n";
        echo "   Owner login: owner@commercecore.com / password\n";
        echo "   Store URL: /store/techvault\n";
    }
}
