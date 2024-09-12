<?php

namespace App\Console\Commands;

use App\Models\Package;
use Illuminate\Console\Command;

class UpdatePackages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-packages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all packages from source';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $packages = Package::all();
        $bar = $this->output->createProgressBar(count($packages));
        $bar->start();

        $packages->each(function ($package) use ($bar) {
            try {
                $package->updater()->update();
            } catch (\Exception $e) {
                ray($e);
                $this->error("Failed to update {$package->slug}");
            }
            $bar->advance();
        });

        $bar->finish();
    }
}
