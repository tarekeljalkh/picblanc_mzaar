<?php

use Illuminate\Support\Facades\DB;

if (!function_exists('getAvailableYears')) {
    function getAvailableYears(): array
    {
        // Runs: SHOW DATABASES LIKE 'picblanc_mzaar_%'
        $databases = DB::select("SHOW DATABASES LIKE 'picblanc_mzaar_%'");

        $years = [];

        foreach ($databases as $db) {

            // Convert object to array and extract the FIRST value
            $dbArray = (array) $db;
            $name = array_values($dbArray)[0];  // <--- SAFE and works on all drivers

            // Extract YEAR
            $year = str_replace('picblanc_mzaar_', '', $name);

            if (is_numeric($year)) {
                $years[] = (int) $year;
            }
        }

        sort($years);

        return $years;
    }
}
