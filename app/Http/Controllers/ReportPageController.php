<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use Inertia\Inertia;

class ReportPageController extends Controller
{
    public function attendance()
    {
        return Inertia::render('attendance/index', [
            'employees' => Employee::select('id', 'name', 'position', 'email')
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(fn($emp) => [
                    'id' => $emp->id,
                    'name' => $emp->name,
                    'position' => $emp->position,
                    'email' => $emp->email,
                ]),
        ]);
    }

    public function company()
    {
        return Inertia::render('companies/index', [
            'statuses' => ['active', 'inactive'],
            'stats' => [
                'total_companies' => Company::count(),
                'active_companies' => Company::active()->count(),
                'inactive_companies' => Company::inactive()->count(),
            ],
        ]);
    }
}

