<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendPasswordToUser extends Notification
{
    use Queueable;

    public string $password;

    public function __construct(string $password)
    {
        $this->password = $password;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('تم إنشاء حسابك في النظام')
            ->greeting('مرحبًا ' . $notifiable->name)
            ->line('تم إنشاء حسابك بنجاح. بيانات الدخول:')
            ->line('البريد الإلكتروني: ' . $notifiable->email)
            ->line('كلمة المرور: ' . $this->password)
            ->line('يرجى تغيير كلمة المرور بعد تسجيل الدخول.')
            ->salutation('مع تحيات فريق العمل');
    }
}
