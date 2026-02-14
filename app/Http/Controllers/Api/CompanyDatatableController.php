<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyDataTableController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::query();

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('joined_at', [$request->start_date, $request->end_date]);
        }

        // DataTables server-side processing
        $totalRecords = $query->count();

        // Search
        if ($request->filled('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                  ->orWhere('email', 'like', "%{$searchValue}%")
                  ->orWhere('status', 'like', "%{$searchValue}%");
            });
        }

        $filteredRecords = $query->count();

        // Ordering
        if ($request->filled('order')) {
            $orderColumnIndex = $request->input('order.0.column');
            $orderDirection = $request->input('order.0.dir');
            $columns = ['id', 'name', 'email', 'status', 'joined_at'];

            if (isset($columns[$orderColumnIndex])) {
                $query->orderBy($columns[$orderColumnIndex], $orderDirection);
            }
        }

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        if ($length != -1) {
            $query->skip($start)->take($length);
        }

        $data = $query->get()->map(function ($company) {
            return [
                'id' => $company->id,
                'name' => $company->name,
                'email' => $company->email,
                'status' => ucfirst($company->status),
                'joined_at' => $company->joined_at->format('Y-m-d'),
                'days_since_joined' => $company->joined_at->diffInDays(now()),
            ];
        });

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }
}
