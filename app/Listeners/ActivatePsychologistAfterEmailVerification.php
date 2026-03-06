<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Verified;

class ActivatePsychologistAfterEmailVerification
{
    public function handle(Verified $event): void
    {
        $user = $event->user;

        if (
            $user->isPsychologist()
            && $user->isPendingVerification()
            && $user->psychologistProfile?->diploma_verified
        ) {
            $user->update(['status' => 'active']);
        }
    }
}
