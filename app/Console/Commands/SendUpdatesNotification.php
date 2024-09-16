<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\UpdatesNotification;
use Illuminate\Console\Command;

class SendUpdatesNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-updates-notification';

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

        foreach ($users as $user) {
            $user->notify(new UpdatesNotification);
        }

        $this->info('Updates notification sent to all users');
    }
}
