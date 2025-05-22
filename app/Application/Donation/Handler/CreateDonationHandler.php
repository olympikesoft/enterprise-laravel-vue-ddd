<?php

namespace App\Application\Donation\Handler;

use App\Application\Donation\Command\CreateDonationCommand;
use App\Infrastructure\Persistence\Models\Campaign;
use App\Infrastructure\Persistence\Models\Donation;
use App\Infrastructure\Persistence\Models\User;
use App\Application\Services\PaymentServiceInterface;
use App\Application\Services\NotificationServiceInterface;
use App\Infrastructure\Event\DonationMadeEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class CreateDonationHandler
{
    public function __construct(
        private PaymentServiceInterface $paymentService,
        private ?NotificationServiceInterface $notificationService = null
    ) {}

    /**
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function handle(CreateDonationCommand $command): Donation
    {
        $dto = $command->dto;

        $campaign = Campaign::findOrFail($dto->campaignId);

        // Check campaign status - allow both APPROVED and ACTIVE
        if (!in_array($campaign->status, [Campaign::STATUS_ACTIVE, Campaign::STATUS_APPROVED])) {
            throw new Exception("Donations cannot be made to this campaign as it is not active or approved.");
        }

        if ($campaign->end_date < now()) {
            throw new Exception("This campaign has ended.");
        }

        $user = $dto->userId ? User::find($dto->userId) : null;

        // Process payment
        $paymentResult = $this->paymentService->processPayment(
            amount: $dto->amount,
            currency: $dto->currency,
            description: "Donation to campaign: {$campaign->title}",
            metadata: [
                'campaign_id' => $campaign->id,
                'user_id' => $dto->userId,
                'donor_name' => $user?->name
            ]
        );

        // Log payment result for debugging
        Log::info('Payment processed', [
            'campaign_id' => $campaign->id,
            'amount' => $dto->amount,
            'success' => $paymentResult['success'],
            'transaction_id' => $paymentResult['transactionId'] ?? null
        ]);

        return DB::transaction(function () use ($dto, $campaign, $paymentResult, $user) {
            // Create donation record
            $donation = Donation::create([
                'campaign_id' => $campaign->id,
                'user_id' => $dto->userId,
                'amount' => $dto->amount,
                'message' => $dto->message,
                'payment_status' => $paymentResult['success']
                    ? Donation::PAYMENT_STATUS_COMPLETED
                    : Donation::PAYMENT_STATUS_FAILED,
                'transaction_id' => $paymentResult['transactionId'],
                'payment_gateway_response' => json_encode($paymentResult['gatewayResponse'] ?? []),
            ]);

            if (!$paymentResult['success']) {
                Log::warning('Payment failed for donation', [
                    'donation_id' => $donation->id,
                    'campaign_id' => $campaign->id,
                    'amount' => $dto->amount,
                    'error_message' => $paymentResult['message'] ?? 'Unknown payment error'
                ]);

                // You might want to send failure notification here
                if ($this->notificationService && $user) {
                    // $this->notificationService->sendPaymentFailedNotification($user, $donation, $campaign);
                }

                return $donation; // Return the failed donation
            }

            // Payment was successful - update campaign amount
            $previousAmount = $campaign->current_amount;
            $campaign->current_amount = (float)$campaign->current_amount + (float)$dto->amount;
            $campaign->save();

            Log::info('Campaign amount updated', [
                'campaign_id' => $campaign->id,
                'previous_amount' => $previousAmount,
                'new_amount' => $campaign->current_amount,
                'donation_amount' => $dto->amount
            ]);

            // Dispatch event for successful donation
            Event::dispatch(new DonationMadeEvent($donation));

            // Send success notifications (keeping existing notification service for other notifications)
            if ($this->notificationService) {
                try {
                    // Note: Donation receipt is now handled by the DonationMadeEvent listener
                    // so we can remove this line:
                    // $this->notificationService->sendDonationReceiptNotification($donation, $campaign, $user);

                    // Notify campaign owner of new donation
                    // $this->notificationService->sendNewDonationToCampaignOwnerNotification($donation, $campaign);

                    // Check if goal reached
                    if ($campaign->current_amount >= $campaign->goal_amount) {
                        Log::info('Campaign goal reached', [
                            'campaign_id' => $campaign->id,
                            'goal_amount' => $campaign->goal_amount,
                            'current_amount' => $campaign->current_amount
                        ]);
                        // $this->notificationService->sendCampaignGoalReachedNotification($campaign);
                    }
                } catch (Exception $e) {
                    // Don't fail the donation if notifications fail
                    Log::error('Failed to send notifications for donation', [
                        'donation_id' => $donation->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return $donation;
        });
    }
}
