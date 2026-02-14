<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use Faker\Factory as Faker;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 1; $i <= 10; $i++) {
            Employee::create([
                'name' => $faker->name,
                'email' => "employee{$i}@company.test",
                'position' => $faker->randomElement([
                    'Staff IT',
                    'HR',
                    'Finance',
                    'Marketing',
                    'Operational'
                ]),
                'is_active' => true,
            ]);
        }
    }
}
