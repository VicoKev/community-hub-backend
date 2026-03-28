<?php

namespace App\Providers;

use App\Events\ProfileApproved;
use App\Events\ProfileRejected;
use App\Events\ProfileSubmitted;
use App\Listeners\NotifyAdminsOfNewProfile;
use App\Listeners\SendProfileApprovedNotification;
use App\Listeners\SendProfileRejectedNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as BaseEventServiceProvider;

class EventServiceProvider extends BaseEventServiceProvider
{
    protected $listen = [
        ProfileSubmitted::class => [
            NotifyAdminsOfNewProfile::class,
        ],

        ProfileApproved::class => [
            SendProfileApprovedNotification::class,
        ],

        ProfileRejected::class => [
            SendProfileRejectedNotification::class,
        ],
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
