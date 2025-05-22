<?php

namespace App\Application\Campaign\Handler;

use App\Application\Campaign\Command\UpdateCampaignCommand;
use App\Infrastructure\Persistence\Models\Campaign; // Eloquent Model
use App\Infrastructure\Persistence\Models\User; // Eloquent Model for User
use App\Application\Services\NotificationServiceInterface; // Optional
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Exception; // General exception
use Carbon\Carbon;

class UpdateCampaignHandler
{
    public function __construct(
        private ?NotificationServiceInterface $notificationService = null // Optional
    ) {}

    /**
     * @throws ModelNotFoundException
     * @throws AuthorizationException
     * @throws Exception
     */
    public function handle(UpdateCampaignCommand $command): Campaign
    {
        $campaign = Campaign::findOrFail($command->campaignId);
        $updatingUser = User::findOrFail($command->userId);

        $dto = $command->dto;

        if ($campaign->user_id !== $updatingUser->id && !$updatingUser->is_admin) {
            throw new AuthorizationException('You are not authorized to update this campaign.');
        }

        $nonEditableStatuses = [
            Campaign::STATUS_COMPLETED,
            Campaign::STATUS_CANCELLED,
            // Campaign::STATUS_REJECTED, // Maybe rejected can be edited and resubmitted?
            // Campaign::STATUS_ACTIVE, // If active with donations, updates might be restricted
        ];
        if (in_array($campaign->status, $nonEditableStatuses) && !$updatingUser->is_admin) {
             throw new Exception("Campaign cannot be updated in its current status: {$campaign->status}.");
        }
        $updatedFields = [];
        if (!is_null($dto->title)) {
            $updatedFields['title'] = $dto->title;
        }
        if (!is_null($dto->description)) {
            $updatedFields['description'] = $dto->description;
        }
        if (!is_null($dto->goalAmount)) {
            $updatedFields['goal_amount'] = $dto->goalAmount;
        }
        if (!is_null($dto->startDate)) {
            if ($dto->endDate && $dto->startDate >= $dto->endDate) {
                throw new Exception('Start date must be before the end date.');
            }
            $updatedFields['start_date'] = $dto->startDate;
        }
        if (!is_null($dto->endDate)) {
             if (($dto->startDate ?? $campaign->start_date) >= $dto->endDate) {
                throw new Exception('End date must be after the start date.');
            }
            $updatedFields['end_date'] = $dto->endDate;
        }

        if (!is_null($dto->status) && $updatingUser->is_admin) {
            if ($dto->status === Campaign::STATUS_APPROVED) {
                if ($campaign->status !== Campaign::STATUS_PENDING) {
                    throw new Exception("Campaign can only be approved if currently pending.");
                }
                $updatedFields['approved_at'] = Carbon::now();
                $updatedFields['approved_by'] = $updatingUser->id;
            } elseif ($dto->status === Campaign::STATUS_PENDING && $campaign->status === Campaign::STATUS_REJECTED) {
                $updatedFields['approved_at'] = null;
                $updatedFields['approved_by'] = null;
            }
            $updatedFields['status'] = $dto->status;
        } elseif (!is_null($dto->status) && !$updatingUser->is_admin) {
            throw new AuthorizationException('You are not authorized to change the campaign status.');
        }


        if (!empty($updatedFields)) {
            $campaign->fill($updatedFields);
            $campaign->save();

        if ($this->notificationService) {
                 $this->notificationService->sendCampaignApprovedNotification($campaign, $updatingUser);
             }
        }

        return $campaign;
    }
}
