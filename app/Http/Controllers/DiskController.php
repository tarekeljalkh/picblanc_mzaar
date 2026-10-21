<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DiskController extends Controller
{
    public function exportDatabase()
    {
        $fileName = 'database_backup_' . Carbon::now()->format('Y_m_d_H_i_s') . '.sql';
        $storagePath = storage_path('app/' . $fileName);

        $connection = DB::connection();
        $connectionConfig = config('database.connections.' . $connection->getName());
        $database = $connection->getDatabaseName();
        $username = $connectionConfig['username'] ?? '';
        $password = $connectionConfig['password'] ?? '';
        $host = $connectionConfig['host'] ?? '127.0.0.1';
        $dbConnection = $connection->getDriverName();

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
            Log::error('Database export failed.', [
                'connection' => $connection->getName(),
                'output' => $output,
            ]);

            return response()->json(['error' => 'Failed to export the database.'], 500);
        }

        // Return the backup file as a download response
        return response()->download($storagePath)->deleteFileAfterSend(true);
    }

}
