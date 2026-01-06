<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    public function __construct(
        protected string $token
    ) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe')
            ->greeting('Bonjour 👋')
            ->line("Vous avez demandé la réinitialisation de votre mot de passe.")
            ->action('Réinitialiser mon mot de passe', $url)
            ->line('Ce lien expirera dans 60 minutes.')
            ->line('Si vous n’êtes pas à l’origine de cette demande, ignorez cet email.');
    }
}
