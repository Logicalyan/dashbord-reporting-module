<?php

namespace App\Http\Controllers\Api;

use App\DTO\Reports\CompanyReportFilterDTO;
use App\Exports\Company\CompanyReportExport;
use App\Http\Controllers\Controller;
use App\Services\Reports\CompanyReportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CompanyReportController extends Controller
{
    protected CompanyReportService $service;

    public function __construct(CompanyReportService $service)
    {
        $this->service = $service;
    }

    public function export(Request $request)
    {
        $dto = new CompanyReportFilterDTO(
            startDate: $request->query('start_date'),
            endDate: $request->query('end_date'),
            status: $request->query('status'),
            activeOnly: $request->query('active_only') ? (bool) $request->query('active_only') : null,
            sortBy: $request->query('sort_by', 'joined_at'),
            sortDir: $request->query('sort_dir', 'desc')
        );

        return Excel::download(
            new CompanyReportExport($dto, $this->service),
            'company-report-' . now()->format('Y-m-d-His') . '.xlsx',
            \Maatwebsite\Excel\Excel::XLSX,
            ['charts' => true]
        );
    }
}
