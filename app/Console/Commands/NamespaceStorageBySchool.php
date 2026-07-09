<?php

namespace App\Console\Commands;

use App\Models\AptitudeQuestion;
use App\Models\BankStatement;
use App\Models\Document;
use App\Models\SchoolInfo;
use App\Models\Staff;
use App\Models\Student;
use App\Models\User;
use App\Models\School;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class NamespaceStorageBySchool extends Command
{
    protected $signature = 'storage:namespace-by-school {--dry-run}';
    protected $description = 'One-time move of existing public-disk uploads into schools/{school_id}/... paths, updating the DB columns that reference them';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('public');

        $this->migrateColumn(Student::withoutSchoolScope(), 'photo', $disk, $dryRun);
        $this->migrateColumn(Staff::withoutSchoolScope(), 'photo', $disk, $dryRun);
        $this->migrateColumn(User::withoutSchoolScope(), 'photo', $disk, $dryRun);
        $this->migrateColumn(AptitudeQuestion::withoutSchoolScope(), 'image', $disk, $dryRun);
        $this->migrateColumn(SchoolInfo::withoutSchoolScope(), 'logo', $disk, $dryRun);
        $this->migrateColumn(Document::withoutSchoolScope(), 'file_path', $disk, $dryRun);
        $this->migrateColumn(BankStatement::withoutSchoolScope(), 'file_path', $disk, $dryRun);

        // schools.logo doesn't have its own school_id — it IS the school.
        School::query()->whereNotNull('logo')->each(function (School $school) use ($disk, $dryRun) {
            $this->moveOne($disk, $school, 'logo', $school->id, $dryRun);
        });

        $this->migrateAptitudeOptions($dryRun);

        $this->info($dryRun ? 'Dry run complete — no files were moved.' : 'Done.');
        return 0;
    }

    private function migrateColumn($query, string $column, $disk, bool $dryRun, ?\Closure $schoolIdResolver = null): void
    {
        $query->whereNotNull($column)->where($column, '!=', '')->chunkById(100, function ($rows) use ($column, $disk, $dryRun, $schoolIdResolver) {
            foreach ($rows as $row) {
                $schoolId = $schoolIdResolver ? $schoolIdResolver($row) : $row->school_id;
                $this->moveOne($disk, $row, $column, $schoolId, $dryRun);
            }
        });
    }

    private function moveOne($disk, $model, string $column, ?int $schoolId, bool $dryRun): void
    {
        $oldPath = $model->{$column};

        if (!$oldPath || str_starts_with($oldPath, 'schools/')) {
            return; // already namespaced or empty
        }

        $newPath = 'schools/' . ($schoolId ?? 'unassigned') . '/' . ltrim($oldPath, '/');

        if (!$disk->exists($oldPath)) {
            $this->warn("Missing on disk, updating column only: {$oldPath}");
            if (!$dryRun) {
                $model->{$column} = $newPath;
                $model->saveQuietly();
            }
            return;
        }

        $this->line(($dryRun ? '[dry-run] ' : '') . "{$oldPath} -> {$newPath}");

        if ($dryRun) {
            return;
        }

        $disk->makeDirectory(dirname($newPath));
        $disk->move($oldPath, $newPath);

        $model->{$column} = $newPath;
        $model->saveQuietly();
    }

    private function migrateAptitudeOptions(bool $dryRun): void
    {
        AptitudeQuestion::withoutSchoolScope()->whereNotNull('options')->chunkById(100, function ($questions) use ($dryRun) {
            foreach ($questions as $question) {
                $options = $question->options;
                if (!is_array($options)) {
                    continue;
                }

                $changed = false;
                foreach ($options as $key => $opt) {
                    $path = $opt['image'] ?? null;
                    if (!$path || str_starts_with($path, 'schools/')) {
                        continue;
                    }

                    $disk = Storage::disk('public');
                    $newPath = 'schools/' . ($question->school_id ?? 'unassigned') . '/' . ltrim($path, '/');

                    $this->line(($dryRun ? '[dry-run] ' : '') . "aptitude option image: {$path} -> {$newPath}");

                    if (!$dryRun) {
                        if ($disk->exists($path)) {
                            $disk->makeDirectory(dirname($newPath));
                            $disk->move($path, $newPath);
                        }
                        $options[$key]['image'] = $newPath;
                        $changed = true;
                    }
                }

                if ($changed && !$dryRun) {
                    $question->options = $options;
                    $question->saveQuietly();
                }
            }
        });
    }
}
