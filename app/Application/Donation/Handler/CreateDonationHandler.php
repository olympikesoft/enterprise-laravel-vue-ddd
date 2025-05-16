<?php

namespace App\Application\Donation\Handler;

use App\Application\Donation\Command\CreateDonationCommand;
use App\Infrastructure\Persistence\Models\Campaign;
use App\Infrastructure\Persistence\Models\Donation;
use App\Infrastructure\Persistence\Models\User;
use App\Application\Services\PaymentServiceInterface;
use App\Application\Services\NotificationServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception; // General exception

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

        if ($campaign->status !== Campaign::STATUS_APPROVED && $campaign->status !== Campaign::STATUS_ACTIVE) { // Assuming ACTIVE is a valid status for donations
            throw new Exception("Donations cannot be made to this campaign as it is not active or approved.");
        }
        if ($campaign->end_date < now()) {
            throw new Exception("This campaign has ended.");
        }

        $user = $dto->userId ? User::find($dto->userId) : null;
        $donorName = $user ? $user->name : $dto->donorName;

        if (!$donorName) {
            throw new Exception("Donor name is required for anonymous donations.");
        }

        // Attempt payment processing
        $paymentResult = $this->paymentService->processPayment(
            amount: $dto->amount,
            currency: $dto->currency,
            paymentToken: $dto->paymentToken,
            description: "Donation to campaign: {$campaign->title}",
            metadata: [
                'campaign_id' => $campaign->id,
                'user_id' => $dto->userId,
                'donor_name' => $donorName,
            ]
        );

        return DB::transaction(function () use ($dto, $campaign, $user, $donorName, $paymentResult) {
            $donation = Donation::create([
                'campaign_id' => $campaign->id,
                'user_id' => $dto->userId,
                'donor_name' => $donorName,
                'amount' => $dto->amount,
                'message' => $dto->message,
                'payment_status' => $paymentResult['success'] ? Donation::PAYMENT_STATUS_COMPLETED : Donation::PAYMENT_STATUS_FAILED,
                'transaction_id' => $paymentResult['transactionId'],
                'payment_gateway_response' => json_encode($paymentResult['gatewayResponse']), // Store for auditing
            ]);

            if (!$paymentResult['success']) {
                // Log the failure, maybe notify admins, but the donation record is still created as 'failed'
                // throw new Exception("Payment failed: " . ($paymentResult['message'] ?? 'Unknown error'));
                // Depending on requirements, you might not throw here but let the controller handle the failed status.
            }

            if ($paymentResult['success']) {
                // Update campaign's current amount
                $campaign->current_amount = (float)$campaign->current_amount + (float)$dto->amount;
                $campaign->save();

                // Send notifications
                if ($this->notificationService) {
                    // $this->notificationService->sendDonationReceipt($donation, $campaign);
                    // $this->notificationService->sendNewDonationToCampaignOwnerNotification($donation, $campaign);

                    // Check if goal reached
                    // if ($campaign->current_amount >= $campaign->goal_amount) {
                    //    $this->notificationService->sendCampaignGoalReachedNotification($campaign);
                    // }
                }
            }
            return $donation;
        });
    }
}