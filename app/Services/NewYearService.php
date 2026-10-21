<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class NewYearService
{
    protected string $prefix;
    protected string $currentDb;

    public function __construct()
    {
        $this->prefix    = env('DB_YEAR_PREFIX', 'picblanc_mayrouba_');
        $this->currentDb = $this->prefix . env('DB_YEAR_ACTIVE');
    }

    public function createNewYear(int $year): string
    {
        $newDb = $this->prefix . $year;

        // 1) CREATE THE NEW DATABASE
        DB::statement("CREATE DATABASE IF NOT EXISTS `$newDb`
            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // 2) GET ALL TABLES FROM CURRENT DB
        $tables = DB::select("SHOW TABLES");

        foreach ($tables as $t) {
            $tableName = array_values((array)$t)[0];

            // 3) COPY STRUCTURE
            DB::statement("CREATE TABLE `$newDb`.`$tableName` LIKE `$this->currentDb`.`$tableName`");

            // 4) COPY DATA
            DB::statement("INSERT INTO `$newDb`.`$tableName`
                           SELECT * FROM `$this->currentDb`.`$tableName`");
        }

        // 5) TRUNCATE ONLY SALES/INVOICE TABLES FOR THE NEW YEAR
        $resetTables = [
            'additional_items',
            'custom_items',
            'invoice_items',
            'invoice_payments',
            'invoices',
            'return_details',
        ];

        foreach ($resetTables as $table) {
            try {
                DB::statement("TRUNCATE TABLE `$newDb`.`$table`");
            } catch (\Exception $e) {
                // Ignore if table does not exist
            }
        }

        return $newDb;
    }
}
