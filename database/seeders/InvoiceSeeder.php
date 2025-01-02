<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure at least two categories exist
        $dailyCategory = Category::firstOrCreate(['name' => 'daily']);
        $seasonCategory = Category::firstOrCreate(['name' => 'season']);

        // Ensure that at least two customers exist
        $customer1 = Customer::first() ?? Customer::factory()->create();
        $customer2 = Customer::skip(1)->first() ?? Customer::factory()->create();

        // Ensure a user exists for seeding purposes
        $user = User::first() ?? User::factory()->create();

        // Set rental dates and calculate days between them
        $startDate1 = Carbon::now()->subDays(10);
        $endDate1 = Carbon::now()->subDays(5);
        $days1 = $startDate1->diffInDays($endDate1);

        $startDate2 = Carbon::now()->subDays(20);
        $endDate2 = Carbon::now()->subDays(15);
        $days2 = $startDate2->diffInDays($endDate2);

        // Create dummy invoices
        Invoice::create([
            'customer_id' => $customer1->id,
            'category_id' => $dailyCategory->id, // Associating with daily category
            'user_id' => $user->id,
            'payment_method' => 'cash',
            'rental_start_date' => $startDate1->format('Y-m-d'),
            'rental_end_date' => $endDate1->format('Y-m-d'),
            'days' => $days1,
            'total_discount' => 5,
            'total_amount' => (100 * $days1) * 1.1 * 0.95,
            'status' => 'draft'
        ]);

        Invoice::create([
            'customer_id' => $customer2->id,
            'category_id' => $seasonCategory->id, // Associating with season category
            'user_id' => $user->id,
            'payment_method' => 'credit_card',
            'rental_start_date' => $startDate2->format('Y-m-d'),
            'rental_end_date' => $endDate2->format('Y-m-d'),
            'days' => $days2,
            'total_discount' => 3,
            'total_amount' => (150 * $days2) * 1.08 * 0.97,
            'status' => 'returned'
        ]);
    }
}
