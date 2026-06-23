<?php

namespace App\Services;

use App\Models\Backup;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Process;
use ZipArchive;

class BackupService
{
    protected string $backupDir = 'backups';

    /**
     * Creates a full backup: mysqldump of the database + zipped copy of
     * storage/app/public (uploaded photos, documents, etc.), bundled
     * together into one ZIP file.
     */
    public function create(string $type = 'manual', ?string $label = null, ?int $userId = null): Backup
    {
        $timestamp = now()->format('Ymd_His');
        $filename = "kips_backup_{$type}_{$timestamp}.zip";
        $tempSqlPath = storage_path("app/{$this->backupDir}/db_{$timestamp}.sql");
        $zipPath = storage_path("app/{$this->backupDir}/{$filename}");

        if (!is_dir(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }

        // 1. Dump the database via mysqldump (requires it to be on PATH in production)
        $this->dumpDatabase($tempSqlPath);

        // 2. Build the ZIP: db dump + uploaded files
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if (file_exists($tempSqlPath)) {
            $zip->addFile($tempSqlPath, 'database.sql');
        }

        $uploadsPath = storage_path('app/public');
        if (is_dir($uploadsPath)) {
            $this->addFolderToZip($zip, $uploadsPath, 'uploads');
        }

        $zip->close();

        if (file_exists($tempSqlPath)) unlink($tempSqlPath);

        $backup = Backup::create([
            'filename'   => $filename,
            'type'       => $type,
            'label'      => $label,
            'size_bytes' => file_exists($zipPath) ? filesize($zipPath) : 0,
            'created_by' => $userId,
            'expires_at' => $type === 'snapshot' ? now()->addDays(7) : null,
        ]);

        $this->pruneOldBackups();

        return $backup;
    }

    /**
     * Called automatically before any BULK DELETE action, per Ahmed's spec:
     * "Pre-delete snapshot: [Action] [Date/Time] by [User]" — retained 7 days.
     */
    public function createSnapshot(string $actionDescription, ?int $userId = null): Backup
    {
        $label = "Pre-delete snapshot: {$actionDescription} on ".now()->format('d-M-Y h:i A');
        return $this->create('snapshot', $label, $userId);
    }

    protected function dumpDatabase(string $outputPath): void
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        $command = sprintf(
            'mysqldump -h%s -P%s -u%s %s %s > %s',
            $config['host'], $config['port'], $config['username'],
            $config['password'] ? '-p'.escapeshellarg($config['password']) : '',
            escapeshellarg($config['database']),
            escapeshellarg($outputPath)
        );

        Process::run($command);
    }

    protected function addFolderToZip(ZipArchive $zip, string $folder, string $zipFolderName): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folder, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if ($file->isDir()) continue;
            $relativePath = $zipFolderName.'/'.substr($file->getPathname(), strlen($folder) + 1);
            $zip->addFile($file->getPathname(), $relativePath);
        }
    }

    /** Keep only the last 30 auto/manual backups; snapshots expire separately. */
    protected function pruneOldBackups(): void
    {
        $old = Backup::where('type', '!=', 'snapshot')
            ->orderByDesc('created_at')
            ->skip(30)->take(1000)->get();

        foreach ($old as $backup) {
            Storage::delete("{$this->backupDir}/{$backup->filename}");
            $backup->delete();
        }

        // Expired snapshots
        Backup::where('type', 'snapshot')->where('expires_at', '<', now())->each(function ($b) {
            @unlink(storage_path("app/{$this->backupDir}/{$b->filename}"));
            $b->delete();
        });
    }

    public function path(Backup $backup): string
    {
        return storage_path("app/{$this->backupDir}/{$backup->filename}");
    }
}
