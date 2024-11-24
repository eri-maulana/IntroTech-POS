<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\StockAdjustment;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
        ]);

        Customer::factory(50)->create();
        Product::factory(50)->create();
        StockAdjustment::factory(50)->create();
        Order::factory(50)->create();
    }
}
