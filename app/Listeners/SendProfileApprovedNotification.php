<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ProfileApproved;
use App\Services\MailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendProfileApprovedNotification implements ShouldQueue
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
    public function handle(ProfileApproved $event): void
    {
        $profile = $event->profile->loadMissing('user');
        $user = $profile->user;

        if (! $user) {
            return;
        }

        $this->mailService->send(
            to: $user,
            view: 'emails.profile.approved',
            subject: 'Votre profil a été approuvé !',
            data: [
                'profile' => $profile,
                'user' => $user,
            ],
            useQueue: false,
        );
    }
}
