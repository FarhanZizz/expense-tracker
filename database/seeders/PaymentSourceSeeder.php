<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    $sources = ['bKash', 'Nagad', 'Rocket', 'Bank', 'Cash'];
    foreach ($sources as $name) {
        \App\Models\PaymentSource::firstOrCreate(['name' => $name]);
    }
}
}
