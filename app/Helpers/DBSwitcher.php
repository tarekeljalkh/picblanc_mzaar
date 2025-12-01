<?php

use Illuminate\Support\Facades\DB;

if (!function_exists('switchDatabaseTo')) {
    function switchDatabaseTo(string $dbName): void
    {
        config(['database.connections.mysql.database' => $dbName]);

        DB::purge('mysql');
        DB::reconnect('mysql');
    }
}
