<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!Package::whereTitle('Basic 2 MB')->exists()) {
            Package::factory()->create([
                'title' => 'Basic 2 MB',
                'fee' => 1000,
                'description' => 'Basic package with limited features.',
            ]);
        }

        if (!Package::whereTitle('Standard 5 MB')->exists()) {
            Package::factory()->create([
                'title' => 'Standard 5 MB',
                'fee' => 2000,
                'description' => 'Standard package with additional features.',
            ]);
        }
        if (!Package::whereTitle('Premium 10 MB')->exists()) {
            Package::factory()->create([
                'title' => 'Premium 10 MB',
                'fee' => 3000,
                'description' => 'Premium package with all features.',
            ]);
        }
        if (!Package::whereTitle('Enterprise 20 MB')->exists()) {
            Package::factory()->create([
                'title' => 'Enterprise 20 MB',
                'fee' => 4000,
                'description' => 'Enterprise package with custom features.',
            ]);
        }
        if (!Package::whereTitle('Unlimited')->exists()) {
            Package::factory()->create([
                'title' => 'Unlimited',
                'fee' => 5000,
                'description' => 'Unlimited package with no restrictions.',
            ]);
        }
    }
}
