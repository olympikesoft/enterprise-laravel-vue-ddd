<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCampaignHttpRequest; // Can reuse for admin updates
use App\Http\Resources\CampaignResource;
use App\Http\Resources\CampaignCollection;
use App\Application\Campaign\Command\ApproveCampaignCommand;
use App\Application\Campaign\Handler\ApproveCampaignHandler;
use App\Application\Campaign\Command\RejectCampaignCommand;
use App\Application\Campaign\Handler\RejectCampaignHandler;
use App\Application\Campaign\Command\UpdateCampaignCommand; // For admin updates
use App\Application\Campaign\Handler\UpdateCampaignHandler; // For admin updates
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

    public function index(Request $request): CampaignCollection
    {
        $query = Campaign::query()->with('user'); // Eager load user

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }
        if ($request->has('user_id')) {
            $query->where('user_id', $request->query('user_id'));
        }

        $campaigns = $query->orderBy($request->query('sort_by', 'created_at'), $request->query('sort_direction', 'desc'))
                           ->paginate($request->query('per_page', 15));

        return new CampaignCollection($campaigns);
    }

    public function show(int $id): CampaignResource
    {
        $campaign = Campaign::with(['user', 'donations'])->findOrFail($id);
        return new CampaignResource($campaign->loadCount('donations'));
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
        $command = new RejectCampaignCommand($id, $request->input('reason'));
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

        // For UpdateCampaignHandler, the userId for authorization check might not be relevant if admin is always allowed.
        // The handler might need adjustment or a separate admin update handler.
        // For simplicity, we pass Auth::id(), but the handler's authorization logic might need to consider admin role.
        // A better approach for admin updates might be a dedicated AdminUpdateCampaignCommand/Handler
        // or modify UpdateCampaignHandler to bypass ownership check if user is admin.
        $command = new UpdateCampaignCommand($dto, Auth::id()); // Auth::id() is for the logged-in admin
        $updatedCampaign = $handler->handle($command); // Ensure handler allows admin override

        return response()->json(new CampaignResource($updatedCampaign));
    }
}