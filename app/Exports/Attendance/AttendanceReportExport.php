<?php

namespace App\Exports\Attendance;

use App\Exports\Attendance\Sheets\AttendanceDetailSheet;
use App\Exports\Attendance\Sheets\AttendanceSummarySheet;
use App\Exports\Base\BaseMultiSheetExport;

class AttendanceReportExport extends BaseMultiSheetExport
{
    public function sheets(): array
    {
        // Get detail data count
        $detailData = $this->service->getDetailData($this->dto);
        $lastRow = $detailData->count() + 1; // +1 for header

        return [
            new AttendanceSummarySheet($lastRow),
            new AttendanceDetailSheet($this->service, $this->dto),
        ];
    }
}
