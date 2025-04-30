<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Package;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = Package::all();
        
        if ($packages->count() > 0) {
            foreach ($packages as $package) {
                Customer::factory(50)->forPackage($package)->create();
            }
        }
    }
}
