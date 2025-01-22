<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch or create categories
        $dailyCategory = Category::firstOrCreate(['name' => 'daily']);
        $seasonCategory = Category::firstOrCreate(['name' => 'season']);

        // Create products for the "daily" category
        Product::create([
            'name' => 'Ski',
            'description' => 'Salomon',
            'price' => 150.00,
            'type' => 'standard',
            'category_id' => $dailyCategory->id,
        ]);
        Product::create([
            'name' => 'Ski Boots',
            'description' => 'Burton',
            'price' => 200.00,
            'type' => 'standard',
            'category_id' => $dailyCategory->id,
        ]);
        Product::create([
            'name' => 'Poles',
            'description' => 'Atomic',
            'price' => 100.00,
            'type' => 'standard',
            'category_id' => $dailyCategory->id,
        ]);

        // Create products for the "season" category
        Product::create([
            'name' => 'Snowboard',
            'description' => 'Giro',
            'price' => 50.00,
            'type' => 'standard',
            'category_id' => $seasonCategory->id,
        ]);
        Product::create([
            'name' => 'Snowboard Boots',
            'description' => 'North Face',
            'price' => 20.00,
            'type' => 'standard',
            'category_id' => $seasonCategory->id,
        ]);
        Product::create([
            'name' => 'Sled',
            'description' => 'North Face',
            'price' => 200.00,
            'type' => 'standard',
            'category_id' => $seasonCategory->id,
        ]);

        // Additional products for both categories
        Product::create([
            'name' => 'Hiking Racket',
            'description' => 'North Face',
            'price' => 10.00,
            'type' => 'standard',
            'category_id' => $dailyCategory->id,
        ]);
        Product::create([
            'name' => 'Gloves',
            'description' => 'North Face',
            'price' => 150.00,
            'type' => 'standard',
            'category_id' => $seasonCategory->id,
        ]);
        Product::create([
            'name' => 'Helmet',
            'description' => 'Marmot',
            'price' => 30.00,
            'type' => 'standard',
            'category_id' => $dailyCategory->id,
        ]);
        Product::create([
            'name' => 'Goggles',
            'description' => 'Marmot',
            'price' => 60.00,
            'type' => 'standard',
            'category_id' => $seasonCategory->id,
        ]);
        Product::create([
            'name' => 'Jacket',
            'description' => 'Columbia',
            'price' => 20.00,
            'type' => 'standard',
            'category_id' => $dailyCategory->id,
        ]);
        Product::create([
            'name' => 'Pants',
            'description' => 'Columbia',
            'price' => 70.00,
            'type' => 'standard',
            'category_id' => $seasonCategory->id,
        ]);
        Product::create([
            'name' => 'Apres Ski',
            'description' => 'Columbia',
            'price' => 180.00,
            'type' => 'standard',
            'category_id' => $seasonCategory->id,
        ]);
    }
}
