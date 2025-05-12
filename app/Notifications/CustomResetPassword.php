<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CustomResetPassword extends Notification
{
    use Queueable;

    public $token;
    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        // Registrar información para depuración
        Log::info('Enviando notificación CustomResetPassword:', [
            'email' => $notifiable->email,
            'url' => $url,
        ]);

        // Establecer el idioma manualmente si es necesario
        $locale = session('locale', config('app.locale', 'es'));
        app()->setLocale($locale);

        return (new MailMessage)
            ->subject(__('Welcome to Our Platform'))
            ->view('emails.welcome', [
                'user' => $notifiable,
                'resetUrl' => $url,
                'appName' => config('app.name'),
                'year' => date('Y'),
            ]);
    }

}
