<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Employee;
use Faker\Factory as Faker;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $employees = Employee::all();

        foreach ($employees as $employee) {
            for ($i = 1; $i <= 30; $i++) { // generate for 30 days
                $status = $faker->randomElement(['Present','Absent','Late','Remote']);
                $checkIn = $status == 'Present' || $status == 'Late' ? $faker->time('H:i') : null;
                $checkOut = $status == 'Present' || $status == 'Late' ? $faker->time('H:i') : null;
                $hours = $checkIn && $checkOut ? rand(7,9) + rand(0,59)/60 : 0;
                $overtime = $hours > 8 ? $hours - 8 : 0;

                Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => now()->subDays(30 - $i),
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'status' => $status,
                    'hours' => round($hours,2),
                    'overtime' => round($overtime,2),
                ]);
            }
        }
    }
}

