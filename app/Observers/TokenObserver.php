<?php

namespace App\Observers;

use App\Models\Token;

class TokenObserver
{
    /**
     * Handle the Token "created" event.
     */
    public function created(Token $token): void
    {
        //
    }

    /**
     * Handle the Token "updated" event.
     */
    public function updated(Token $token): void
    {
        if ($token->isDirty('deactivated_at')) {
            if ($token->deactivated_at) {
                $action = 'deactivate';
            } else {
                $action = 'activate';
            }

            $token->activity()->create([
                'action' => $action,
                'message' => 'Token has been '.$action.'d.',
                'ip_address' => request()->ip(),
                'user_id' => auth()->id(),
            ]);
        }
    }

    /**
     * Handle the Token "deleted" event.
     */
    public function deleted(Token $token): void
    {
        //
    }

    /**
     * Handle the Token "restored" event.
     */
    public function restored(Token $token): void
    {
        //
    }

    /**
     * Handle the Token "force deleted" event.
     */
    public function forceDeleted(Token $token): void
    {
        //
    }
}
