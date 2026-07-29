<?php

namespace App\Console\Commands;

use Database\Seeders\DemoSchoolSeeder;
use Illuminate\Console\Command;

/**
 * Rebuilds the sales-demo school ('demo') from scratch.
 *
 * This runs on the same database as real client schools (Kitungwa, MEMA), so
 * the safety bar is high: DemoSchoolSeeder scopes every write and delete to
 * the demo school's own id, and this command adds the outer guard — refusing
 * to run against production without an explicit --force.
 */
class ResetDemoSchool extends Command
{
    protected $signature = 'demo:reset
        {--force : Required to run outside the local environment}';

    protected $description = 'Wipe and rebuild the demo school (slug: '.DemoSchoolSeeder::SLUG.') with realistic Tanzanian secondary school data. Never touches other schools.';

    public function handle(): int
    {
        if (! app()->environment('local') && ! $this->option('force')) {
            $this->error(sprintf(
                'Refusing to run outside local without --force. '.
                'This rebuilds the demo school (slug: %s) on whatever database %s is currently pointed at — '.
                'confirm that is really what you want before passing --force.',
                DemoSchoolSeeder::SLUG,
                config('database.connections.'.config('database.default').'.database')
            ));

            return self::FAILURE;
        }

        if ($this->option('force') && ! app()->environment('local')) {
            if (! $this->confirm(sprintf(
                "This will DELETE and rebuild all data for school '%s' on %s (%s). Continue?",
                DemoSchoolSeeder::SLUG,
                config('database.connections.'.config('database.default').'.database'),
                app()->environment()
            ))) {
                $this->warn('Aborted.');

                return self::FAILURE;
            }
        }

        $this->call('db:seed', ['--class' => DemoSchoolSeeder::class, '--force' => true]);

        return self::SUCCESS;
    }
}
