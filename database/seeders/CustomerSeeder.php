<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create dummy customers
        Customer::create(['name' => 'John Doe', 'phone' => '1234567890', 'address' => 'zalka', 'deposit_card' => '1111-2222-3333-4444']);
        Customer::create(['name' => 'Jane Smith', 'phone' => '0987654321', 'address' => 'nabatieh', 'deposit_card' => '4444-3333-2222-1111']);
        Customer::create(['name' => 'Tom Hanks', 'phone' => '5555555555', 'address' => 'faraya', 'deposit_card' => '2222-1111-4444-3333']);
    }
}
