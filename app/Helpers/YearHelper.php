<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

if (!function_exists('getAvailableYears')) {
    function getAvailableYears(): array
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return [];
        }

        return Cache::remember('available_year_databases', now()->addMinutes(5), function (): array {
            $databases = DB::select("SHOW DATABASES LIKE 'picblanc_mayrouba_%'");

            $years = [];

            foreach ($databases as $db) {
                $name = array_values((array) $db)[0];
                $year = str_replace('picblanc_mayrouba_', '', $name);

                if (is_numeric($year)) {
                    $years[] = (int) $year;
                }
            }

            sort($years);

            return $years;
        });
    }
}
