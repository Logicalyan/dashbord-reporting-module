<?php

namespace App\Exports\Company\Sheets;

use App\Exports\Base\Sheets\BaseDetailSheet;

class CompanyDetailSheet extends BaseDetailSheet
{
    protected function loadData()
    {
        return collect($this->service->getDetailData($this->dto));
    }

    protected function getColumns(): array
    {
        return [
            'Company Name' => 'company_name',
            'Email' => 'email',
            'Status' => 'status',
            'Joined Date' => 'joined_date',
            'Days Since Joined' => 'days_since_joined',
        ];
    }

    protected function getStatistics(): array
    {
        $stats = $this->service->getStatistics($this->dto);

        return [
            'Total Companies' => ['value' => $stats['total_companies']],
            'Active Companies' => ['formula' => '=COUNTIF(C2:C{lastRow},"Active")'],
            'Inactive Companies' => ['formula' => '=COUNTIF(C2:C{lastRow},"Inactive")'],
            'Avg Days Since Joined' => ['formula' => '=AVERAGE(E2:E{lastRow})'],
            'Oldest Company' => ['value' => $stats['oldest_company'] ?? 'N/A'],
            'Newest Company' => ['value' => $stats['newest_company'] ?? 'N/A'],
        ];
    }

    protected function transformRow($item): array
    {
        return [
            $item['company_name'],
            $item['email'],
            $item['status'],
            $item['joined_date'],
            $item['days_since_joined'],
        ];
    }
}
