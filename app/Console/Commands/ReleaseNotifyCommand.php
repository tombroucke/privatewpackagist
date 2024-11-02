<?php

namespace App\Console\Commands;

use App\Models\Release;
use App\Models\User;
use App\Notifications\UpdatesNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ReleaseNotifyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'release:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email with releases of previous day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::all();

        $from = now()->subDay();
        $to = now();

        $packages = $this->getPackagesWithReleases([$from, $to]);

        if ($packages->isEmpty()) {
            $this->components->info('No updates found');
            return;
        }

        foreach ($users as $user) {
            $user->notify(new UpdatesNotification($packages, $from, $to));
        }

        $this->components->info('Updates notification sent to all users');
    }

    /**
     * Retrieve packages with releases between the given dates.
     */
    private function getPackagesWithReleases(array $between): Collection
    {
        return Release::whereBetween('created_at', $between)->get()
            ->map(fn ($release) => [
                'package' => $release->package,
                'version' => $release->version,
            ])
            ->groupBy(fn ($item) => $item['package']->vendoredName())
            ->sortKeys();
    }
}
