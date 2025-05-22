<?php

namespace App\Interfaces\Http\Controllers\Api\Admin;

use App\Application\Campaign\Command\ResetUserPasswordCommand as CommandResetUserPasswordCommand;
use Illuminate\Routing\Controller;
use App\Application\User\Command\CreateUserCommand;
use App\Application\User\Command\UpdateUserCommand;
use App\Application\User\Command\DeleteUserCommand;
use App\Application\User\Command\BanUserCommand;
use App\Application\User\Command\ActivateUserCommand;
use App\Application\User\Command\ResetUserPasswordCommand;
use App\Application\User\Handler\CreateUserHandler;
use App\Application\User\Handler\UpdateUserHandler;
use App\Application\User\Handler\DeleteUserHandler;
use App\Application\User\Handler\BanUserHandler;
use App\Application\User\Handler\ActivateUserHandler;
use App\Application\User\Handler\ResetUserPasswordHandler;
use App\Application\User\Handler\ListUsersHandler;
use App\Application\User\Handler\ViewUserDetailsHandler;
use App\Application\DTO\User\UpdateUserDTO;
use App\Application\DTO\User\CreateUserDTO;
use App\Infrastructure\Persistence\Models\Campaign;
use App\Infrastructure\Persistence\Models\Donation;
use App\Infrastructure\Persistence\Models\User;
use App\Interfaces\Http\Requests\Auth\RegisterRequest;
use App\Interfaces\Http\Requests\Auth\UpdateUserRequest;
use App\Interfaces\Http\Resources\UserCollection;
use App\Interfaces\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function __construct()
    {
        // Ensure user is admin for all methods in this controller
        $this->middleware(['auth:sanctum', 'admin']);
    }

    public function index(Request $request, ListUsersHandler $handler): UserCollection
    {
        $query = new \App\Application\User\Query\ListUsersQuery(
            roleFilter: $request->query('role'),
            status: $request->query('status'),
            search: $request->query('search'),
            sortBy: $request->query('sort_by', 'created_at'),
            sortDirection: $request->query('sort_direction', 'desc'),
            perPage: $request->query('per_page', 15)
        );

        $result = $handler->handle($query);
        return new UserCollection($result);
    }

    public function show(int $id, ViewUserDetailsHandler $handler): UserResource
    {
        $query = new \App\Application\User\Query\ViewUserQuery($id);
        $user = $handler->handle($query);
        return new UserResource($user);
    }

    public function store(RegisterRequest $request, CreateUserHandler $handler): JsonResponse
    {
        $validated = $request->validated();

        $dto = new CreateUserDTO(
            name: $validated['name'],
            email: $validated['email'],
            password: Hash::make($validated['password']),
            role: $validated['role'] ?? 'user',
            isActive: $validated['is_active'] ?? true
        );

        $command = new CreateUserCommand($dto, Auth::id());
        $user = $handler->handle($command);

        return response()->json(new UserResource($user), 201);
    }

    /**
     * Get dashboard statistics for the admin panel.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function stats(Request $request): JsonResponse
    {
        $totalUsers = User::count();
        $totalCampaigns = Campaign::count();
        $pendingCampaigns = Campaign::where('status', 'PENDING')->count();
        $totalDonations = Donation::sum('amount');

        return response()->json([
            'data' => [
                'totalUsers' => $totalUsers,
                'totalCampaigns' => $totalCampaigns,
                'pendingCampaigns' => $pendingCampaigns,
                'totalDonations' => (float) $totalDonations, // Cast to float for consistency
            ]
        ]);
    }


    public function update(UpdateUserRequest $request, int $id, UpdateUserHandler $handler): JsonResponse
    {
        $validated = $request->validated();

        $dto = new UpdateUserDTO(
            userId: $id,
            name: $validated['name'] ?? null,
            email: $validated['email'] ?? null,
            role: $validated['role'] ?? null,
            isActive: isset($validated['is_active']) ? (bool) $validated['is_active'] : null
        );

        $command = new UpdateUserCommand($id, Auth::id(), $dto);
        $updatedUser = $handler->handle($command);

        return response()->json(new UserResource($updatedUser));
    }

    public function destroy(int $id, DeleteUserHandler $handler): JsonResponse
    {
        $command = new DeleteUserCommand($id, Auth::id());
        $handler->handle($command);

        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    public function ban(Request $request, int $id, BanUserHandler $handler): JsonResponse
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        $command = new BanUserCommand(
            userId: $id,
            adminId: Auth::id(),
            reason: $request->input('reason')
        );

        $user = $handler->handle($command);
        return response()->json(new UserResource($user));
    }

    public function activate(int $id, ActivateUserHandler $handler): JsonResponse
    {
        $command = new ActivateUserCommand($id, Auth::id());
        $user = $handler->handle($command);

        return response()->json(new UserResource($user));
    }


}
