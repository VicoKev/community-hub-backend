<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ProfileSubmitted;
use App\Models\User;
use App\Services\MailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class NotifyAdminsOfNewProfile implements ShouldQueue
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
    public function handle(ProfileSubmitted $event): void
    {
        $profile = $event->profile->loadMissing('user');
        $user = $profile->user;

        $admins = User::role(['admin', 'super_admin'])->get();

        foreach ($admins as $admin) {
            $this->mailService->send(
                to: $admin,
                view: 'emails.admin.new-profile',
                subject: 'Nouveau profil à valider — '.$user->full_name,
                data: [
                    'admin' => $admin,
                    'profile' => $profile,
                    'user' => $user,
                ],
                useQueue: false,
            );
        }
    }
}
