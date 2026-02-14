<?php

namespace App\Helpers;

class DashboardHelper
{
    public static function growth(int $previous, int $current): float
    {
        if ($previous === 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }
}
