<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Mail\GenericMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailService
{
    /**
     * Envoie un mail avec options configurables.
     */
    public function send(
        array|string|User $to,
        string $view,
        string $subject,
        array $data,
        bool $useQueue = true,
        array $cc = [],
        array $bcc = [],
        int $delayInMinutes = 0,
        array $attachments = [],
        ?string $from = null,
        ?string $replyTo = null,
        ?string $locale = null
    ): void {
        try {
            if ($locale) {
                app()->setLocale($locale);
            }
            
            $mailable = new GenericMail($view, $subject, $data);

            // Gérer les pièces jointes
            foreach ($attachments as $file) {
                $mailable->attach($file['path'], [
                    'as' => $file['name'] ?? null,
                    'mime' => $file['mime'] ?? null,
                ]);
            }

            $mail = Mail::to($this->extractEmails($to));

            if (!empty($cc)) {
                $mail->cc($cc);
            }

            if (!empty($bcc)) {
                $mail->bcc($bcc);
            }

            if ($from) {
                $mail->from($from);
            }

            if ($replyTo) {
                $mail->replyTo($replyTo);
            }

            if ($useQueue) {
                if ($delayInMinutes > 0) {
                    $mail->later(now()->addMinutes($delayInMinutes), $mailable);
                } else {
                    $mail->queue($mailable);
                }
            } else {
                $mail->send($mailable);
            }
        } catch (Exception $e) {
            Log::error('Erreur lors de l\'envoi du mail', [
                'recipients' => $this->extractEmails($to),
                'view' => $view,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Extrait l'email(s) d'un User, d'une string ou d’un tableau.
     */
    private function extractEmails(array|string|User $input): array
    {
        if ($input instanceof User) {
            return [$input->email];
        }

        if (is_string($input)) {
            return [$input];
        }

        return $input;
    }
}
