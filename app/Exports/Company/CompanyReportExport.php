<?php

namespace App\Exports\Company;

use App\Exports\Base\BaseMultiSheetExport;
use App\Exports\Company\Sheets\CompanyDetailSheet;
use App\Exports\Company\Sheets\CompanySummarySheet;

class CompanyReportExport extends BaseMultiSheetExport
{
    public function sheets(): array
    {
        $detailData = $this->service->getDetailData($this->dto);
        $lastRow = $detailData->count() + 1;

        return [
            new CompanySummarySheet($lastRow),
            new CompanyDetailSheet($this->service, $this->dto),
        ];
    }
}
