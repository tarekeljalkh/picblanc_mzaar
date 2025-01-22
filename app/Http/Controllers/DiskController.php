<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DiskController extends Controller
{
    public function exportDatabase()
    {
        $fileName = 'database_backup_' . Carbon::now()->format('Y_m_d_H_i_s') . '.sql';
        $storagePath = storage_path('app/' . $fileName);

        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST', '127.0.0.1');
        $dbConnection = env('DB_CONNECTION');

        $command = '';

        switch ($dbConnection) {
            case 'mysql':
                $command = sprintf(
                    'mysqldump --user=%s --password=%s --host=%s %s > %s',
                    escapeshellarg($username),
                    escapeshellarg($password),
                    escapeshellarg($host),
                    escapeshellarg($database),
                    escapeshellarg($storagePath)
                );
                break;

            case 'pgsql':
                $command = sprintf(
                    'PGPASSWORD=%s pg_dump --username=%s --host=%s --dbname=%s --no-password > %s',
                    escapeshellarg($password),
                    escapeshellarg($username),
                    escapeshellarg($host),
                    escapeshellarg($database),
                    escapeshellarg($storagePath)
                );
                break;

                // Add other database connections if necessary

            default:
                return response()->json(['error' => 'Unsupported database connection type'], 500);
        }

        // Execute the command
        $result = null;
        $output = [];
        exec($command . ' 2>&1', $output, $result);

        if ($result !== 0) {
            return response()->json(['error' => 'Failed to export the database. Detailed Output: ' . implode("\n", $output)], 500);
        }

        // Return the backup file as a download response
        return response()->download($storagePath)->deleteFileAfterSend(true);
    }

}
