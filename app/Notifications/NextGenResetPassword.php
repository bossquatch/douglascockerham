<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class NextGenResetPassword extends ResetPassword
{
    /**
     * Build the branded password reset email.
     */
    public function toMail($notifiable): MailMessage
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Reset your NextGenEM password')
            ->view([
                'html' => 'mail.auth.reset-password',
                'text' => 'mail.auth.reset-password-text',
            ], [
                'name' => $notifiable->name,
                'resetUrl' => $resetUrl,
            ]);
    }
}
