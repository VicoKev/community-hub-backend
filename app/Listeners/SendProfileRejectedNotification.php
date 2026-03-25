<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ProfileRejected;
use App\Services\MailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendProfileRejectedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    /**
     * Create the event listener.
     */
    public function __construct(
        protected MailService $mailService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(ProfileRejected $event): void
    {
        $profile = $event->profile->loadMissing('user');
        $user = $profile->user;

        if (! $user) {
            return;
        }

        $this->mailService->send(
            to: $user,
            view: 'emails.profile.rejected',
            subject: 'Votre profil nécessite des corrections',
            data: [
                'profile' => $profile,
                'user' => $user,
                'reason' => $event->reason,
            ],
            useQueue: false,
        );
    }
}
