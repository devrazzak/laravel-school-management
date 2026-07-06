<?php

namespace App\Listeners;

use App\Events\UserCreatedByAdmin;
use App\Notifications\SetPasswordNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\URL;

class SendSetPasswordEmail
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
    public function handle(UserCreatedByAdmin $event): void
    {
        $user = $event->user;

        $expireHours = config('auth.set_password.expire', 24);
        echo ($expireHours);

        // Generate a signed URL for the set password route
        $signedApiUrl = URL::temporarySignedRoute(
            'api.set-password.verify',
            now()->addHours($expireHours),
            [
                'user' => $user->id,
                'email' => $user->email
            ]
        );

        // generate the frontend URL for setting the password
        // $frontendUrl = config('app.frontend_url') . '/set-password?' . http_build_query([
        //     'user' => $user->id,
        //     'email' => $user->email,
        //     'signature' => request()->getSignature($signedApiUrl),
        //     'expires' => now()->addHours($expireHours)->timestamp,

        // ]);

        $user->notify(new SetPasswordNotification($signedApiUrl));
    }
}
