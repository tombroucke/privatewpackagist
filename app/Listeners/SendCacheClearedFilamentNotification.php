<?php

namespace App\Listeners;

use App\Events\CacheClearedEvent;
use Filament\Notifications\Notification;

class SendCacheClearedFilamentNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CacheClearedEvent $event): void
    {
        Notification::make()
            ->title("The {$event->cacheType()} cache has been cleared.")
            ->info()
            ->send();
    }
}
