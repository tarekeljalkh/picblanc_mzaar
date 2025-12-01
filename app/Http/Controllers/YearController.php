<?php

namespace App\Http\Controllers;

use App\Services\NewYearService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class YearController extends Controller
{
    /**
     * Switch active year
     */
    public function switch($year)
    {
        // Validate year format
        if (!preg_match('/^\d{4}$/', $year)) {
            return redirect()->back()->with('error', 'Invalid year format.');
        }

        // Save active year in a cookie for 1 year
        $cookie = cookie('active_year', $year, 60 * 24 * 365);

        return redirect()
            ->route('dashboard')
            ->withCookie($cookie)
            ->with('success', "Switched to year $year successfully!");
    }

    /**
     * Create a new year DB
     */
    public function create(Request $request, NewYearService $service)
    {
        // Validate year input
        $request->validate([
            'year' => 'required|digits:4|integer|min:2024|max:2100',
        ]);

        $year = (int) $request->year;
        $prefix = env('DB_YEAR_PREFIX'); // e.g., picblanc_mzaar_
        $newDb = $prefix . $year;

        // Check if database already exists
        $dbExists = DB::select("SHOW DATABASES LIKE '$newDb'");
        if ($dbExists) {
            return redirect()->back()->with('error', "Database $newDb already exists.");
        }

        // Create (copy current DB → new DB) + truncate (invoices, payments, returns...)
        try {
            $service->createNewYear($year);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Error creating database: " . $e->getMessage());
        }

        return redirect()
            ->back()
            ->with('success', "New year database $newDb created successfully!");
    }
}
