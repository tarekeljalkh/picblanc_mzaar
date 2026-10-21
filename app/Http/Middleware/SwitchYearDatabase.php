<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SwitchYearDatabase
{
    public function handle(Request $request, Closure $next)
    {
        // read selected year from cookie (NOT session)
        $year = $request->cookie('active_year', date('Y'));

        // your database naming format
        $dbName = 'picblanc_mayrouba_' . $year;

        // switch the main mysql connection
        switchDatabaseTo($dbName);

        return $next($request);
    }
}
