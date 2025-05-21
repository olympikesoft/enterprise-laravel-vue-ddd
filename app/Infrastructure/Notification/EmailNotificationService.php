<?php

namespace App\Infrastructure\Notification;

use Illuminate\Support\Facades\Mail;

class EmailNotificationService
{
    /**
     * Send an email notification.
     *
     * @param string $to
     * @param string $subject
     * @param string $view
     * @param array $data
     * @return void
     */
    public function send(string $to, string $subject, string $view, array $data = []): void
    {
        Mail::send($view, $data, function ($message) use ($to, $subject) {
            $message->to($to)
                    ->subject($subject);
        });
    }
}
