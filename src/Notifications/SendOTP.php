<?php

namespace Vormkracht10\TwoFactorAuth\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use Vormkracht10\TwoFactorAuth\Actions\GenerateOTP;
use Vormkracht10\TwoFactorAuth\Enums\TwoFactorType;

class SendOTP extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if ($notifiable->two_factor_type === TwoFactorType::email) {
            return ['mail'];
        }

        if (
            $notifiable->two_factor_type === TwoFactorType::phone &&
            in_array(TwoFactorType::phone, config('filament-two-factor-auth.options'))
        ) {
            return [config('filament-two-factor-auth.sms_service')];
        }

        return [];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Your security code for :app', ['app' => config('app.name')]))
            ->markdown('filament-two-factor-auth::emails.two-factor-code', [
                'code' => $this->getTwoFactorCode($notifiable),
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws SecretKeyTooShortException
     * @throws InvalidCharactersException
     */
    public function getTwoFactorCode(User $notifiable): ?string
    {
        if (! $notifiable->two_factor_secret) {
            return null;
        }

        return GenerateOTP::for(
            decrypt($notifiable->two_factor_secret)
        );
    }
}
