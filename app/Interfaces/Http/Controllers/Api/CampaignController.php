<?php


namespace App\Interfaces\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use App\Application\Campaign\Command\CreateCampaignCommand;
use App\Application\Campaign\Command\DeleteCampaignCommand;
use App\Application\Campaign\Handler\CreateCampaignHandler;
use App\Application\Campaign\Command\UpdateCampaignCommand;
use App\Application\Campaign\Handler\DeleteCampaignHandler;
use App\Application\Campaign\Handler\UpdateCampaignHandler;
use App\Application\Campaign\Handler\ListActiveCampaignsHandler;
use App\Application\Campaign\Handler\ListCampaignsHandler;
use App\Application\Campaign\Handler\ListUserCampaignsHandler;
use App\Application\Campaign\Handler\ViewCampaignDetailsHandler;
use App\Application\Campaign\Query\ListCampaignsQuery;
use App\Application\Campaign\Query\ListUserCampaignsQuery;
use App\Application\DTO\Campaign\CreateCampaignDTO;
use App\Application\DTO\Campaign\UpdateCampaignDTO;
use App\Infrastructure\Persistence\Models\Campaign;
use App\Infrastructure\Persistence\Models\Donation;
use App\Interfaces\Http\Requests\StoreCampaignHttpRequest;
use App\Interfaces\Http\Requests\UpdateCampaignHttpRequest;
use App\Interfaces\Http\Resources\CampaignCollection;
use App\Interfaces\Http\Resources\CampaignResource;
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

    public function index(Request $request, ListCampaignsHandler $handler): CampaignCollection
    {
        $query = new ListCampaignsQuery(
            status: $request->query('status'),
            sortBy: $request->query('sort_by', 'created_at'),
            sortDirection: $request->query('sort_direction', 'desc'),
            perPage: $request->query('per_page', 15)
        );
        $result = $handler->handle($query);
        return new CampaignCollection($result);
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
        );

        $command = new UpdateCampaignCommand($id, (string)Auth::id(), $dto);
        $updatedCampaign = $handler->handle($command);

        return response()->json(new CampaignResource($updatedCampaign));
    }

    public function destroy(int $id, DeleteCampaignHandler $handler): JsonResponse
    {
        $campaign = Campaign::findOrFail($id);
        if ($campaign->user_id !== Auth::id()) {
            throw new AuthorizationException('You are not authorized to delete this campaign.');
        }

        if ($campaign->donations()->where('payment_status', Donation::PAYMENT_STATUS_COMPLETED)->exists()) {
             return response()->json(['message' => 'Cannot delete campaign with completed donations.'], 403);
        }

        $command = new DeleteCampaignCommand($id, Auth::id()); // Pass acting user ID
        $handler->handle($command);

        return response()->json(['message' => 'Campaign deleted successfully.'], 200); // Or 204 No Content
    }

    public function myCampaigns(Request $request, ListUserCampaignsHandler $handler): CampaignCollection
    {
        $query = new ListUserCampaignsQuery(
            userId: Auth::id(),
            status: $request->query('status'),
            sortBy: $request->query('sort_by', 'created_at'),
            sortDirection: $request->query('sort_direction', 'desc'),
            perPage: $request->query('per_page', 15)
        );
        $campaigns = $handler->handle($query);
        return new CampaignCollection($campaigns);
    }
}
