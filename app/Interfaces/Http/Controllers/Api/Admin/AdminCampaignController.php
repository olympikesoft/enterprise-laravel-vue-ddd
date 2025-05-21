<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Routing\Controller;
use App\Http\Requests\UpdateCampaignHttpRequest; // Can reuse for admin updates
use App\Http\Resources\CampaignResource;
use App\Http\Resources\CampaignCollection;
use App\Application\Campaign\Command\ApproveCampaignCommand;
use App\Application\Campaign\Handler\ApproveCampaignHandler;
use App\Application\Campaign\Handler\RejectCampaignHandler;
use App\Application\Campaign\Command\RejectCampaignCommand;
use App\Application\Campaign\Command\UpdateCampaignCommand; // For admin updates
use App\Application\Campaign\Handler\ListActiveCampaignsHandler;
use App\Application\Campaign\Handler\UpdateCampaignHandler; // For admin updates
use App\Application\Campaign\Handler\ViewCampaignDetailsHandler;
use App\Application\DTO\Campaign\UpdateCampaignDTO;         // For admin updates
use App\Infrastructure\Persistence\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; // To ensure admin

class AdminCampaignController extends Controller
{
    public function __construct()
    {
        // Ensure user is admin for all methods in this controller
        $this->middleware(['auth:sanctum', 'admin']); // Assuming an 'admin' middleware
    }

    public function index(Request $request, ListActiveCampaignsHandler $queryHandler): CampaignCollection
    {
        $query = new ViewCampaignDetailsHandler(
            status: $request->query('status'),
            userId: $request->query('user_id'),
            sortBy: $request->query('sort_by', 'created_at'),
            sortDirection: $request->query('sort_direction', 'desc'),
            perPage: $request->query('per_page', 15)
        );

        $result = $queryHandler->handle($query->id ?? null );
        return new CampaignCollection($result);
    }

    public function show(int $id, ViewCampaignDetailsHandler $handler): CampaignResource
    {
        $query = new ViewCampaignDetailsHandler($id);
        $campaign = $handler->handle($id);
        return new CampaignResource($campaign);
    }

    public function approve(int $id, ApproveCampaignHandler $handler): JsonResponse
    {
        $command = new ApproveCampaignCommand($id);
        $campaign = $handler->handle($command);
        return response()->json(new CampaignResource($campaign));
    }

    public function reject(Request $request, int $id, RejectCampaignHandler $handler): JsonResponse
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        $command = new RejectCampaignCommand($id, $request->input('reason'), Auth::id());
        $campaign = $handler->handle($command);
        return response()->json(new CampaignResource($campaign));
    }

    public function update(UpdateCampaignHttpRequest $request, int $id, UpdateCampaignHandler $handler): JsonResponse
    {
        // Admin can update any campaign, and potentially the status
        $validated = $request->validated();
        $dto = new UpdateCampaignDTO(
            campaignId: $id,
            title: $validated['title'] ?? null,
            description: $validated['description'] ?? null,
            goalAmount: isset($validated['goal_amount']) ? (float) $validated['goal_amount'] : null,
            startDate: isset($validated['start_date']) ? Carbon::parse($validated['start_date']) : null,
            endDate: isset($validated['end_date']) ? Carbon::parse($validated['end_date']) : null,
            status: $validated['status'] ?? null // Admin can update status
        );

        $command = new UpdateCampaignCommand($id,Auth::id(), $dto);
        $updatedCampaign = $handler->handle($command);

        return response()->json(new CampaignResource($updatedCampaign));
    }
}
