<?php

namespace App\Notifications;

use App\Models\Release;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class UpdatesNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $now = now();
        $yesterday = now()->subDay();
        $packages = $this->getPackagesWithReleases([$yesterday, $now]);
        $message = (new MailMessage)
            ->markdown('emails.updates', ['packages' => $packages,
                'yesterday' => $yesterday,
                'now' => $now,
            ]);

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    private function getPackagesWithReleases(array $between): Collection
    {
        return Release::whereBetween('created_at', $between)->get()
            ->map(function ($release) {

                // get previous version
                $previousVersion = Release::where('package_id', $release->package_id)
                    ->where('created_at', '<', $release->created_at)
                    ->orderBy('created_at', 'desc')
                    ->first();

                return [
                    'package' => $release->package,
                    'version' => $release->version,
                ];
            })
            // group by package
            ->groupBy(function ($item) {
                return $item['package']->vendoredName();
            })
            ->sortKeys();
    }
}
