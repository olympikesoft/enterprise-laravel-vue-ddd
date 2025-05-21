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

        // Extract the DTO from the command
        $dto = $command->dto;

        // Authorization: Only campaign owner or an admin can update
        if ($campaign->user_id !== $updatingUser->id && !$updatingUser->is_admin) {
            throw new AuthorizationException('You are not authorized to update this campaign.');
        }

        // Prevent updates if campaign is not in an editable state (e.g., already active with donations, completed, rejected, cancelled)
        // This logic depends heavily on business rules.
        $nonEditableStatuses = [
            Campaign::STATUS_COMPLETED,
            Campaign::STATUS_CANCELLED,
            // Campaign::STATUS_REJECTED, // Maybe rejected can be edited and resubmitted?
            // Campaign::STATUS_ACTIVE, // If active with donations, updates might be restricted
        ];
        if (in_array($campaign->status, $nonEditableStatuses) && !$updatingUser->is_admin) {
             throw new Exception("Campaign cannot be updated in its current status: {$campaign->status}.");
        }
        // Admins might have more leeway or specific fields they can update even in other statuses.

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
            // Basic validation, more complex date logic might be needed
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
                $updatedFields['approved_by_id'] = $updatingUser->id;
                $updatedFields['rejection_reason'] = null; // Clear rejection if any
            } elseif ($dto->status === Campaign::STATUS_PENDING && $campaign->status === Campaign::STATUS_REJECTED) {
                // Allow admin to move from REJECTED back to PENDING (e.g., for resubmission)
                $updatedFields['rejection_reason'] = null;
                $updatedFields['approved_at'] = null;
                $updatedFields['approved_by_id'] = null;
            }
            $updatedFields['status'] = $dto->status;
        } elseif (!is_null($dto->status) && !$updatingUser->is_admin) {
            throw new AuthorizationException('You are not authorized to change the campaign status.');
        }


        if (!empty($updatedFields)) {
            $campaign->fill($updatedFields); // Use fill for mass assignment if fields are in $fillable
            $campaign->save();

            // Optional: Send notification about update
            // if ($this->notificationService) {
            //     $this->notificationService->sendCampaignUpdatedNotification($campaign, $updatingUser);
            // }
        }

        return $campaign;
    }
}
