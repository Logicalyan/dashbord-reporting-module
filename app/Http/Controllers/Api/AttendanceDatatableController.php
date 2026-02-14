<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceDataTableController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with('employee');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('min_hours')) {
            $query->where('hours', '>=', $request->min_hours);
        }

        if ($request->filled('max_hours')) {
            $query->where('hours', '<=', $request->max_hours);
        }

        // DataTables server-side processing
        $totalRecords = $query->count();

        // Search
        if ($request->filled('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->whereHas('employee', function ($eq) use ($searchValue) {
                    $eq->where('name', 'like', "%{$searchValue}%")
                       ->orWhere('email', 'like', "%{$searchValue}%");
                })
                ->orWhere('status', 'like', "%{$searchValue}%")
                ->orWhere('date', 'like', "%{$searchValue}%");
            });
        }

        $filteredRecords = $query->count();

        // Ordering
        if ($request->filled('order')) {
            $orderColumnIndex = $request->input('order.0.column');
            $orderDirection = $request->input('order.0.dir');
            $columns = ['id', 'employee_id', 'date', 'status', 'hours', 'overtime'];

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

        $data = $query->get()->map(function ($attendance) {
            return [
                'id' => $attendance->id,
                'employee_name' => $attendance->employee->name ?? 'N/A',
                'employee_email' => $attendance->employee->email ?? 'N/A',
                'employee_position' => $attendance->employee->position ?? 'N/A',
                'date' => $attendance->date->format('Y-m-d'),
                'day_of_week' => $attendance->date->format('l'),
                'status' => $attendance->status,
                'check_in' => $attendance->check_in?->format('H:i') ?? '-',
                'check_out' => $attendance->check_out?->format('H:i') ?? '-',
                'hours' => $attendance->hours ?? 0,
                'overtime' => $attendance->overtime ?? 0,
                'total_hours' => ($attendance->hours ?? 0) + ($attendance->overtime ?? 0),
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
