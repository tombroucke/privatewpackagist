<?php

namespace App\Console\Commands;

use App\Models\Package;
use Exception;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multisearch;
use function Laravel\Prompts\progress;

class PackageUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:update
                            {package? : The package to update}
                            {--all : Update all packages}
                            {--confirm : Update without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the specified package or all packages from their respective sources';

    /**
     * The failed updates.
     */
    protected array $failed = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $package = $this->argument('package');

        if (! $this->option('all') && ! $package) {
            $package = multisearch(
                label: 'Select package(s) to update',
                options: fn (string $value) => strlen($value) > 0
                    ? Package::whereLike('slug', "%{$value}%")->pluck('name', 'id')->all()
                    : Package::limit(10)->pluck('name', 'id')->all(),
                hint: 'Leave blank to update all packages',
            );
        }

        $packages = $package
            ? Package::where('slug', $package)->get()
            : Package::all();

        if ($packages->isEmpty()) {
            return match (true) {
                $package => $this->components->error("Package [{$package}] not found"),
                default => $this->components->info('There are no packages configured'),
            };
        }

        if (
            ! $this->option('confirm') &&
            ! confirm("Are you sure you want to update {$packages->count()} package(s)?")
        ) {
            return;
        }

        progress(
            label: "Updating {$packages->count()} package(s)",
            steps: $packages,
            callback: fn ($package) => $this->updatePackage($package),
            hint: 'This may take a moment',
        );

        $this->newLine();

        if ($this->failed) {
            $this->components->error('Failed to update the following package(s):');

            foreach ($this->failed as $slug => $message) {
                $this->components->error("{$slug}: {$message}");
            }

            $count = $packages->count() - count($this->failed);

            $this->components->warn("Successfully updated {$count} out of {$packages->count()} package(s)");

            return;
        }

        $this->components->info("Successfully updated {$packages->count()} package(s)");
    }

    /**
     * Update the package.
     */
    private function updatePackage(Package $package): void
    {
        try {
            $package->recipe()->update();
        } catch (Exception $e) {
            $this->failed[$package->slug] = $e->getMessage();
        }
    }
}
