<?php
// ============================================================
// database/seeders/ProductSeeder.php
//
// Run with: php artisan db:seed --class=ProductSeeder
// ============================================================

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // ── ADMIN USER ─────────────────────────────────
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@drapestore.com'],
            [
                'name'           => 'Store Admin',
                'password'       => Hash::make('Admin@123456'),
                'role'           => 'admin',
                'login_attempts' => 0,
                'locked_until'   => null,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]
        );

        // ── TEST CUSTOMER ──────────────────────────────
        DB::table('users')->updateOrInsert(
            ['email' => 'customer@test.com'],
            [
                'name'           => 'Test Customer',
                'password'       => Hash::make('Customer@123456'),
                'role'           => 'customer',
                'login_attempts' => 0,
                'locked_until'   => null,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]
        );

        // ── FASHION PRODUCTS ───────────────────────────
        $products = [
            // TOPS
            ['name' => 'Classic White Oxford Shirt',   'description' => 'Timeless crisp white oxford shirt crafted from 100% Egyptian cotton.', 'price' => 89.00,  'category' => 'tops',        'image_url' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=600&q=80', 'size_options' => 'XS,S,M,L,XL,XXL'],
            ['name' => 'Ribbed Knit Turtleneck',       'description' => 'Luxuriously soft ribbed turtleneck in merino wool blend.',             'price' => 65.00,  'category' => 'tops',        'image_url' => 'https://images.unsplash.com/photo-1576871337622-98d48d1cf531?w=600&q=80', 'size_options' => 'XS,S,M,L,XL'],
            ['name' => 'Linen Blend Crop Top',         'description' => 'Effortlessly chic crop top in a breathable linen-cotton blend.',       'price' => 45.00,  'category' => 'tops',        'image_url' => 'https://images.unsplash.com/photo-1554568218-0f1715e72254?w=600&q=80', 'size_options' => 'XS,S,M,L'],
            // BOTTOMS
            ['name' => 'High-Waist Wide Leg Trousers', 'description' => 'Elevated wide-leg trousers in fluid crepe fabric.',                    'price' => 125.00, 'category' => 'bottoms',     'image_url' => 'https://images.unsplash.com/photo-1594938298603-c8148c4b4f35?w=600&q=80', 'size_options' => 'XS,S,M,L,XL'],
            ['name' => 'Tailored Slim Chinos',         'description' => 'Clean slim-cut chinos in stretch cotton twill.',                       'price' => 85.00,  'category' => 'bottoms',     'image_url' => 'https://images.unsplash.com/photo-1473966968600-fa801b869a1a?w=600&q=80', 'size_options' => 'XS,S,M,L,XL,XXL'],
            ['name' => 'Pleated Midi Skirt',           'description' => 'Romantic pleated midi skirt in a silky satin finish.',                 'price' => 75.00,  'category' => 'bottoms',     'image_url' => 'https://images.unsplash.com/photo-1583496661160-fb5886a0aaaa?w=600&q=80', 'size_options' => 'XS,S,M,L'],
            // DRESSES
            ['name' => 'Wrap Midi Dress',              'description' => 'The perennial wrap dress silhouette in fluid jersey.',                 'price' => 110.00, 'category' => 'dresses',     'image_url' => 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=600&q=80', 'size_options' => 'XS,S,M,L,XL'],
            ['name' => 'Linen Shirt Dress',            'description' => 'Relaxed linen shirt dress for effortless summer dressing.',            'price' => 95.00,  'category' => 'dresses',     'image_url' => 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?w=600&q=80', 'size_options' => 'XS,S,M,L,XL'],
            ['name' => 'Velvet Slip Dress',            'description' => 'Bias-cut velvet slip dress with adjustable spaghetti straps.',         'price' => 135.00, 'category' => 'dresses',     'image_url' => 'https://images.unsplash.com/photo-1566479179817-5c25f6cf2c4c?w=600&q=80', 'size_options' => 'XS,S,M,L'],
            // OUTERWEAR
            ['name' => 'Oversized Wool Coat',          'description' => 'Statement coat crafted from premium boiled wool.',                     'price' => 295.00, 'category' => 'outerwear',   'image_url' => 'https://images.unsplash.com/photo-1544441893-675973e31985?w=600&q=80', 'size_options' => 'XS,S,M,L,XL'],
            ['name' => 'Cropped Leather Biker Jacket', 'description' => 'Cropped biker jacket in genuine lambskin leather.',                    'price' => 350.00, 'category' => 'outerwear',   'image_url' => 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=600&q=80', 'size_options' => 'XS,S,M,L'],
            // ACCESSORIES
            ['name' => 'Structured Leather Tote',      'description' => 'Polished everyday tote in full-grain leather.',                        'price' => 185.00, 'category' => 'accessories', 'image_url' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=600&q=80', 'size_options' => 'ONE SIZE'],
            ['name' => 'Silk Square Scarf',            'description' => 'Hand-rolled 100% silk twill scarf.',                                   'price' => 55.00,  'category' => 'accessories', 'image_url' => 'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b7f?w=600&q=80', 'size_options' => 'ONE SIZE'],
        ];

        foreach ($products as $p) {
            DB::table('products')->insert(array_merge($p, [
                'stock'      => rand(20, 100),
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('✅ Created 2 users (admin + customer) and ' . count($products) . ' products.');
    }
}