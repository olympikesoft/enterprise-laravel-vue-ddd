<?php

namespace App\Infrastructure\Event\Listeners;

use App\Infrastructure\Event\DonationMadeEvent;
use App\Infrastructure\Mail\DonationConfirmationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendDonationConfirmationListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'emails';

    /**
     * The time (seconds) before the job should be processed.
     *
     * @var int
     */
    public $delay = 0;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Handle the event.
     *
     * @param  DonationMadeEvent  $event
     * @return void
     */
    public function handle(DonationMadeEvent $event)
    {
        $donation = $event->donation;

        /** @var \App\Infrastructure\Persistence\Models\User $user */
        $user = $donation->user;

        try {
            // Send confirmation email
            Mail::to($user->email)->send(new DonationConfirmationMail($donation));

            Log::info('Donation confirmation email sent successfully', [
                'donation_id' => $donation->id,
                'user_id' => $user->id,
                'email' => $user->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send donation confirmation email', [
                'donation_id' => $donation->id,
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);

            // Re-throw the exception to trigger retry logic
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  DonationMadeEvent  $event
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(DonationMadeEvent $event, $exception)
    {

        Log::error('Donation confirmation email job failed permanently', [
            'donation_id' => $event->donation->id,
            'user_id' => $event->donation->user_id,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        // You could implement fallback notification logic here
        // For example, save to a failed_notifications table for manual retry
    }
}
