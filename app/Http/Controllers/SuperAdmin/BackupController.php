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
 *
 * IMPORTANT: every call here uses Storage::disk('local') explicitly, never
 * the bare Storage:: facade. This app sets FILESYSTEM_DISK=public in .env,
 * so the bare facade's "default" disk is the web-accessible one
 * (storage/app/public, symlinked to public/storage) — using it here would
 * make full database dumps fetchable by anyone who guesses a filename,
 * with no login required. Confirmed and fixed after an initial version of
 * this controller did exactly that.
 */
class BackupController extends Controller
{
    private function disk(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return Storage::disk('local');
    }

    private function directory(): string
    {
        return config('backup.directory', 'backups');
    }

    public function index()
    {
        $dir = $this->directory();
        $files = collect($this->disk()->files($dir))
            ->filter(fn ($path) => str_ends_with($path, '.sql.gz'))
            ->map(fn ($path) => [
                'name' => basename($path),
                'size' => $this->disk()->size($path),
                'date' => $this->disk()->lastModified($path),
            ])
            ->sortByDesc('date')
            ->values();

        return view('super_admin.backups.index', compact('files'));
    }

    public function create()
    {
        $db = config('database.connections.' . config('database.default'));
        $filename = 'backup_' . now()->format('Y-m-d_His') . '.sql.gz';

        $this->disk()->makeDirectory($this->directory());
        $absolutePath = $this->disk()->path($this->directory() . '/' . $filename);

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
            $this->disk()->delete($this->directory() . '/' . $filename);

            return back()->with('error', 'Backup failed — check the application log for details.');
        }

        return back()->with('success', "Backup created: {$filename}");
    }

    public function download(string $filename)
    {
        $this->assertSafeFilename($filename);
        $path = $this->directory() . '/' . $filename;

        abort_unless($this->disk()->exists($path), 404);

        return $this->disk()->download($path);
    }

    public function destroy(string $filename)
    {
        $this->assertSafeFilename($filename);
        $this->disk()->delete($this->directory() . '/' . $filename);

        return back()->with('success', 'Backup deleted.');
    }

    /** Filenames come back from our own naming scheme only — reject anything else. */
    private function assertSafeFilename(string $filename): void
    {
        abort_unless((bool) preg_match('/^backup_\d{4}-\d{2}-\d{2}_\d{6}\.sql\.gz$/', $filename), 400);
    }
}
