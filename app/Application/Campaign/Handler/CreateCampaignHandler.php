<?php

namespace App\Application\Campaign\Handler;

use App\Application\Campaign\Command\CreateCampaignCommand;
use App\Infrastructure\Persistence\Models\Campaign;
use App\Infrastructure\Persistence\Models\User;
use App\Application\Services\NotificationServiceInterface; // Optional notification
use Illuminate\Support\Facades\DB; // For transactions

class CreateCampaignHandler
{
    public function __construct(
        private ?NotificationServiceInterface $notificationService = null // Optional
    ) {}

    public function handle(CreateCampaignCommand $command): Campaign
    {
        $dto = $command->dto;

        $user = User::findOrFail($dto->userId);

        return DB::transaction(function () use ($dto, $user) {
            $campaign = Campaign::create([
                'user_id' => $user->id,
                'title' => $dto->title,
                'description' => $dto->description,
                'goal_amount' => $dto->goalAmount,
                'current_amount' => 0, // Initial amount
                'start_date' => $dto->startDate,
                'end_date' => $dto->endDate,
                'status' => Campaign::STATUS_PENDING,
            ]);

            if ($this->notificationService) {
                // $this->notificationService->sendCampaignCreatedConfirmation($campaign, $user);
                // $adminEmails = User::where('is_admin', true)->pluck('email')->toArray();
                // $this->notificationService->sendCampaignNeedsApprovalNotification($campaign, $adminEmails);
            }

            return $campaign;
        });
    }
}
