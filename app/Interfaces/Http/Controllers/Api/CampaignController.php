<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use App\Http\Requests\StoreCampaignHttpRequest;
use App\Http\Requests\UpdateCampaignHttpRequest;
use App\Http\Resources\CampaignResource;
use App\Http\Resources\CampaignCollection;
use App\Application\Campaign\Command\CreateCampaignCommand;
use App\Application\Campaign\Handler\CreateCampaignHandler;
use App\Application\Campaign\Command\UpdateCampaignCommand;
use App\Application\Campaign\Handler\UpdateCampaignHandler;
use App\Application\Campaign\Handler\ListActiveCampaignsHandler;
use App\Application\Campaign\Handler\ViewCampaignDetailsHandler;
use App\Application\DTO\Campaign\CreateCampaignDTO;
use App\Application\DTO\Campaign\UpdateCampaignDTO;
use App\Infrastructure\Persistence\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;

class CampaignController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }

    public function index(Request $request, ListActiveCampaignsHandler $handler): CampaignCollection
    {
        $perPage = $request->query('per_page', 15);
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDirection = $request->query('sort_direction', 'desc');

        $campaigns = $handler->handle((int)$perPage, $sortBy, $sortDirection);
        return new CampaignCollection($campaigns);
    }

    public function store(StoreCampaignHttpRequest $request, CreateCampaignHandler $handler): JsonResponse
    {
        $validated = $request->validated();
        $dto = new CreateCampaignDTO(
            title: $validated['title'],
            description: $validated['description'],
            goalAmount: (float) $validated['goal_amount'],
            startDate: Carbon::parse($validated['start_date']),
            endDate: Carbon::parse($validated['end_date']),
            userId: Auth::id()
        );
        $command = new CreateCampaignCommand($dto);
        $campaign = $handler->handle($command);

        return response()->json(new CampaignResource($campaign), 201);
    }

    public function show(int $id, ViewCampaignDetailsHandler $handler): CampaignResource
    {
        $campaign = $handler->handle($id);
        return new CampaignResource($campaign->loadCount(['donations' => function ($query) {
            $query->where('payment_status', \App\Infrastructure\Persistence\Models\Donation::PAYMENT_STATUS_COMPLETED);
        }]));
    }

    /*
    public function update(UpdateCampaignHttpRequest $request, int $id, UpdateCampaignHandler $handler): JsonResponse
    {
        $campaign = Campaign::findOrFail($id); // Fetch first for authorization check

        if ($campaign->user_id !== Auth::id()) {
            throw new AuthorizationException('You are not authorized to update this campaign.');
        }

        $validated = $request->validated();
        $dto = new UpdateCampaignDTO(
            campaignId: $id,
            title: $validated['title'] ?? null,
            description: $validated['description'] ?? null,
            goalAmount: isset($validated['goal_amount']) ? (float) $validated['goal_amount'] : null,
            startDate: isset($validated['start_date']) ? Carbon::parse($validated['start_date']) : null,
            endDate: isset($validated['end_date']) ? Carbon::parse($validated['end_date']) : null
            // Status updates are typically admin-only, so not included here for user updates
        );

        $command = new UpdateCampaignCommand($dto, Auth::id());
        $updatedCampaign = $handler->handle($command);

        return response()->json(new CampaignResource($updatedCampaign));
    }*/

    // public function destroy(int $id): JsonResponse
    // {
    //     $campaign = Campaign::findOrFail($id);
    //     if ($campaign->user_id !== Auth::id()) {
    //         throw new AuthorizationException('You are not authorized to delete this campaign.');
    //     }
    //     // Add logic for soft delete or archiving, check if donations exist etc.
    //     // $campaign->delete(); // Or a custom "cancel" status
    //     return response()->json(null, 204);
    // }
}
