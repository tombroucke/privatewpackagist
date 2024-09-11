<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdatePackage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-package {package}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update package from source';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $slug = $this->argument('package');
        $package = \App\Models\Package::where('slug', $slug)->first();

        if (! $package) {
            $this->fail('Package not found');
        }
        $package->updater()->createRelease();
    }
}
