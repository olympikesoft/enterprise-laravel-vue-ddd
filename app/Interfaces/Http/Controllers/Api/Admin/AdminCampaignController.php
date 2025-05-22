<?php

namespace App\Interfaces\Http\Controllers\Api\Admin;

use Illuminate\Routing\Controller;
use App\Application\Campaign\Command\ApproveCampaignCommand;
use App\Application\Campaign\Handler\ApproveCampaignHandler;
use App\Application\Campaign\Handler\RejectCampaignHandler;
use App\Application\Campaign\Command\RejectCampaignCommand;
use App\Application\Campaign\Command\UpdateCampaignCommand;
use App\Application\Campaign\Handler\ListCampaignsHandler;
use App\Application\Campaign\Handler\UpdateCampaignHandler;
use App\Application\Campaign\Handler\ViewCampaignDetailsHandler;
use App\Application\Campaign\Query\ListCampaignsQuery;
use App\Application\DTO\Campaign\UpdateCampaignDTO;
use App\Interfaces\Http\Requests\UpdateCampaignHttpRequest;
use App\Interfaces\Http\Resources\CampaignCollection;
use App\Interfaces\Http\Resources\CampaignResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AdminCampaignController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'admin']);
    }

    public function index(Request $request, ListCampaignsHandler $queryHandler): CampaignCollection
    {
        $query = new ListCampaignsQuery(
            status: $request->query('status'),
            sortBy: $request->query('sort_by', 'created_at'),
            sortDirection: $request->query('sort_direction', 'desc'),
            perPage: $request->query('per_page', 15)
        );

        $result = $queryHandler->handle($query);
        return new CampaignCollection($result);
    }

    public function show(int $id, ViewCampaignDetailsHandler $handler): CampaignResource
    {
        $handler = new ViewCampaignDetailsHandler();
        $campaign = $handler->handle($id);
        return new CampaignResource($campaign);
    }

    public function approve(int $id, ApproveCampaignHandler $handler): JsonResponse
    {
        $command = new ApproveCampaignCommand($id, Auth::id());
        $campaign = $handler->handle($command);
        return response()->json(new CampaignResource($campaign));
    }

    public function reject(Request $request, int $id, RejectCampaignHandler $handler): JsonResponse
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        $command = new RejectCampaignCommand((string)$id, $request->input('reason'), Auth::id());
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
