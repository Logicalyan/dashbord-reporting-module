<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [];

        for ($i = 1; $i <= 120; $i++) {
            $companies[] = [
                'name' => 'Company ' . $i,
                'email' => 'company' . $i . '@example.com',
                'status' => fake()->boolean(75) ? 'active' : 'inactive',
                'joined_at' => now()->subDays(rand(0, 365)),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Company::insert($companies);
    }
}
