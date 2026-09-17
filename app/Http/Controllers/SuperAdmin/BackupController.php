<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

/**
 * Manual full-database backups (mysqldump). Every school's data lives in
 * one database (row-level multi-tenancy via BelongsToSchool), so a backup
 * necessarily contains every school — this is why it's super-admin-only,
 * not exposed anywhere in the per-school admin area.
 */
class BackupController extends Controller
{
    private function directory(): string
    {
        return config('backup.directory', 'backups');
    }

    public function index()
    {
        $dir = $this->directory();
        $files = collect(Storage::files($dir))
            ->filter(fn ($path) => str_ends_with($path, '.sql.gz'))
            ->map(fn ($path) => [
                'name' => basename($path),
                'size' => Storage::size($path),
                'date' => Storage::lastModified($path),
            ])
            ->sortByDesc('date')
            ->values();

        return view('super_admin.backups.index', compact('files'));
    }

    public function create()
    {
        $db = config('database.connections.' . config('database.default'));
        $filename = 'backup_' . now()->format('Y-m-d_His') . '.sql.gz';

        Storage::makeDirectory($this->directory());
        $absolutePath = Storage::path($this->directory() . '/' . $filename);

        $mysqldump = config('backup.mysqldump_path', 'mysqldump');
        $command = sprintf(
            '%s --single-transaction --quick --lock-tables=false -h %s -P %s -u %s %s | gzip > %s',
            escapeshellcmd($mysqldump),
            escapeshellarg($db['host']),
            escapeshellarg((string) $db['port']),
            escapeshellarg($db['username']),
            escapeshellarg($db['database']),
            escapeshellarg($absolutePath),
        );

        // Password passed via env var, not -p on the command line, so it
        // never shows up in `ps aux` output on a shared server.
        $result = Process::env(array_filter(['MYSQL_PWD' => $db['password'] ?? '']))
            ->timeout(600)
            ->run($command);

        if (!$result->successful()) {
            Log::error('Database backup failed', ['error' => $result->errorOutput()]);
            Storage::delete($this->directory() . '/' . $filename);

            return back()->with('error', 'Backup failed — check the application log for details.');
        }

        return back()->with('success', "Backup created: {$filename}");
    }

    public function download(string $filename)
    {
        $this->assertSafeFilename($filename);
        $path = $this->directory() . '/' . $filename;

        abort_unless(Storage::exists($path), 404);

        return Storage::download($path);
    }

    public function destroy(string $filename)
    {
        $this->assertSafeFilename($filename);
        Storage::delete($this->directory() . '/' . $filename);

        return back()->with('success', 'Backup deleted.');
    }

    /** Filenames come back from our own naming scheme only — reject anything else. */
    private function assertSafeFilename(string $filename): void
    {
        abort_unless((bool) preg_match('/^backup_\d{4}-\d{2}-\d{2}_\d{6}\.sql\.gz$/', $filename), 400);
    }
}
