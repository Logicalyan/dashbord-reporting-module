<?php

namespace App\Traits;

use Carbon\Carbon;

trait DashboardFilterPresets
{
    /**
     * Get all available filter presets
     */
    public function getFilterPresets(): array
    {
        return [
            'today' => $this->getTodayFilter(),
            'yesterday' => $this->getYesterdayFilter(),
            'last_7_days' => $this->getLast7DaysFilter(),
            'last_14_days' => $this->getLast14DaysFilter(),
            'last_30_days' => $this->getLast30DaysFilter(),
            'last_90_days' => $this->getLast90DaysFilter(),
            'this_week' => $this->getThisWeekFilter(),
            'last_week' => $this->getLastWeekFilter(),
            'this_month' => $this->getThisMonthFilter(),
            'last_month' => $this->getLastMonthFilter(),
            'this_quarter' => $this->getThisQuarterFilter(),
            'last_quarter' => $this->getLastQuarterFilter(),
            'this_year' => $this->getThisYearFilter(),
            'last_year' => $this->getLastYearFilter(),
            'all_time' => $this->getAllTimeFilter(),
        ];
    }

    /**
     * Get comparison presets
     */
    public function getComparisonPresets(): array
    {
        return [
            'today_vs_yesterday' => $this->getTodayVsYesterdayComparison(),
            'this_week_vs_last_week' => $this->getThisWeekVsLastWeekComparison(),
            'this_month_vs_last_month' => $this->getThisMonthVsLastMonthComparison(),
            'this_quarter_vs_last_quarter' => $this->getThisQuarterVsLastQuarterComparison(),
            'this_year_vs_last_year' => $this->getThisYearVsLastYearComparison(),
            'last_7_days_vs_previous_7_days' => $this->getLast7DaysVsPrevious7DaysComparison(),
            'last_30_days_vs_previous_30_days' => $this->getLast30DaysVsPrevious30DaysComparison(),
            'q1_vs_q2' => $this->getQ1VsQ2Comparison(),
            'q2_vs_q3' => $this->getQ2VsQ3Comparison(),
            'q3_vs_q4' => $this->getQ3VsQ4Comparison(),
            'h1_vs_h2' => $this->getH1VsH2Comparison(), // Half year comparison
        ];
    }

    // ============================================
    // SINGLE PERIOD FILTERS
    // ============================================

    protected function getTodayFilter(): array
    {
        return [
            'from' => Carbon::today()->format('Y-m-d'),
            'to' => Carbon::today()->format('Y-m-d'),
        ];
    }

    protected function getYesterdayFilter(): array
    {
        return [
            'from' => Carbon::yesterday()->format('Y-m-d'),
            'to' => Carbon::yesterday()->format('Y-m-d'),
        ];
    }

    protected function getLast7DaysFilter(): array
    {
        return [
            'from' => Carbon::now()->subDays(6)->startOfDay()->format('Y-m-d'),
            'to' => Carbon::now()->endOfDay()->format('Y-m-d'),
        ];
    }

    protected function getLast14DaysFilter(): array
    {
        return [
            'from' => Carbon::now()->subDays(13)->startOfDay()->format('Y-m-d'),
            'to' => Carbon::now()->endOfDay()->format('Y-m-d'),
        ];
    }

    protected function getLast30DaysFilter(): array
    {
        return [
            'from' => Carbon::now()->subDays(29)->startOfDay()->format('Y-m-d'),
            'to' => Carbon::now()->endOfDay()->format('Y-m-d'),
        ];
    }

    protected function getLast90DaysFilter(): array
    {
        return [
            'from' => Carbon::now()->subDays(89)->startOfDay()->format('Y-m-d'),
            'to' => Carbon::now()->endOfDay()->format('Y-m-d'),
        ];
    }

    protected function getThisWeekFilter(): array
    {
        return [
            'from' => Carbon::now()->startOfWeek()->format('Y-m-d'),
            'to' => Carbon::now()->endOfWeek()->format('Y-m-d'),
        ];
    }

    protected function getLastWeekFilter(): array
    {
        return [
            'from' => Carbon::now()->subWeek()->startOfWeek()->format('Y-m-d'),
            'to' => Carbon::now()->subWeek()->endOfWeek()->format('Y-m-d'),
        ];
    }

    protected function getThisMonthFilter(): array
    {
        return [
            'from' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'to' => Carbon::now()->endOfMonth()->format('Y-m-d'),
        ];
    }

    protected function getLastMonthFilter(): array
    {
        return [
            'from' => Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d'),
            'to' => Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d'),
        ];
    }

    protected function getThisQuarterFilter(): array
    {
        return [
            'from' => Carbon::now()->startOfQuarter()->format('Y-m-d'),
            'to' => Carbon::now()->endOfQuarter()->format('Y-m-d'),
        ];
    }

    protected function getLastQuarterFilter(): array
    {
        return [
            'from' => Carbon::now()->subQuarter()->startOfQuarter()->format('Y-m-d'),
            'to' => Carbon::now()->subQuarter()->endOfQuarter()->format('Y-m-d'),
        ];
    }

    protected function getThisYearFilter(): array
    {
        return [
            'from' => Carbon::now()->startOfYear()->format('Y-m-d'),
            'to' => Carbon::now()->endOfYear()->format('Y-m-d'),
        ];
    }

    protected function getLastYearFilter(): array
    {
        return [
            'from' => Carbon::now()->subYear()->startOfYear()->format('Y-m-d'),
            'to' => Carbon::now()->subYear()->endOfYear()->format('Y-m-d'),
        ];
    }

    protected function getAllTimeFilter(): array
    {
        return [
            'from' => null, // Or set to your app's inception date
            'to' => null,
        ];
    }

    // ============================================
    // COMPARISON FILTERS
    // ============================================

    protected function getTodayVsYesterdayComparison(): array
    {
        return [
            'period1' => $this->getYesterdayFilter(),
            'period2' => $this->getTodayFilter(),
        ];
    }

    protected function getThisWeekVsLastWeekComparison(): array
    {
        return [
            'period1' => $this->getLastWeekFilter(),
            'period2' => $this->getThisWeekFilter(),
        ];
    }

    protected function getThisMonthVsLastMonthComparison(): array
    {
        return [
            'period1' => $this->getLastMonthFilter(),
            'period2' => $this->getThisMonthFilter(),
        ];
    }

    protected function getThisQuarterVsLastQuarterComparison(): array
    {
        return [
            'period1' => $this->getLastQuarterFilter(),
            'period2' => $this->getThisQuarterFilter(),
        ];
    }

    protected function getThisYearVsLastYearComparison(): array
    {
        return [
            'period1' => $this->getLastYearFilter(),
            'period2' => $this->getThisYearFilter(),
        ];
    }

    protected function getLast7DaysVsPrevious7DaysComparison(): array
    {
        return [
            'period1' => [
                'from' => Carbon::now()->subDays(13)->format('Y-m-d'),
                'to' => Carbon::now()->subDays(7)->format('Y-m-d'),
            ],
            'period2' => [
                'from' => Carbon::now()->subDays(6)->format('Y-m-d'),
                'to' => Carbon::now()->format('Y-m-d'),
            ],
        ];
    }

    protected function getLast30DaysVsPrevious30DaysComparison(): array
    {
        return [
            'period1' => [
                'from' => Carbon::now()->subDays(59)->format('Y-m-d'),
                'to' => Carbon::now()->subDays(30)->format('Y-m-d'),
            ],
            'period2' => [
                'from' => Carbon::now()->subDays(29)->format('Y-m-d'),
                'to' => Carbon::now()->format('Y-m-d'),
            ],
        ];
    }

    protected function getQ1VsQ2Comparison(): array
    {
        $year = Carbon::now()->year;

        return [
            'period1' => [
                'from' => Carbon::create($year, 1, 1)->format('Y-m-d'),
                'to' => Carbon::create($year, 3, 31)->format('Y-m-d'),
            ],
            'period2' => [
                'from' => Carbon::create($year, 4, 1)->format('Y-m-d'),
                'to' => Carbon::create($year, 6, 30)->format('Y-m-d'),
            ],
        ];
    }

    protected function getQ2VsQ3Comparison(): array
    {
        $year = Carbon::now()->year;

        return [
            'period1' => [
                'from' => Carbon::create($year, 4, 1)->format('Y-m-d'),
                'to' => Carbon::create($year, 6, 30)->format('Y-m-d'),
            ],
            'period2' => [
                'from' => Carbon::create($year, 7, 1)->format('Y-m-d'),
                'to' => Carbon::create($year, 9, 30)->format('Y-m-d'),
            ],
        ];
    }

    protected function getQ3VsQ4Comparison(): array
    {
        $year = Carbon::now()->year;

        return [
            'period1' => [
                'from' => Carbon::create($year, 7, 1)->format('Y-m-d'),
                'to' => Carbon::create($year, 9, 30)->format('Y-m-d'),
            ],
            'period2' => [
                'from' => Carbon::create($year, 10, 1)->format('Y-m-d'),
                'to' => Carbon::create($year, 12, 31)->format('Y-m-d'),
            ],
        ];
    }

    protected function getH1VsH2Comparison(): array
    {
        $year = Carbon::now()->year;

        return [
            'period1' => [
                'from' => Carbon::create($year, 1, 1)->format('Y-m-d'),
                'to' => Carbon::create($year, 6, 30)->format('Y-m-d'),
            ],
            'period2' => [
                'from' => Carbon::create($year, 7, 1)->format('Y-m-d'),
                'to' => Carbon::create($year, 12, 31)->format('Y-m-d'),
            ],
        ];
    }
}
