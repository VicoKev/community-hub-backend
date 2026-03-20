<?php

namespace App\Listeners;

use App\Mail\InscriptionConfirmation;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;

class EnvoyerEmailConfirmationInscription
{
    public function handle(Registered $event): void
    {
        Mail::to($event->user->email)->send(new InscriptionConfirmation($event->user));
    }
}
