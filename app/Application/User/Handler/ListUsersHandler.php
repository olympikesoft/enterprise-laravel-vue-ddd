<?php

namespace App\Application\User\Handler;

use App\Application\User\Query\ListUsersQuery;
use App\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListUsersHandler
{
    public function handle(ListUsersQuery $query): LengthAwarePaginator
    {
        $eloquentQuery = User::query();

        if ($query->roleFilter) {
            $eloquentQuery->where('role', $query->roleFilter);
        }

        if ($query->status) {
            match (strtolower($query->status)) {
                'active' => $eloquentQuery->where('is_active', true)->whereNull('banned_at'),
                'inactive' => $eloquentQuery->where('is_active', false)->whereNull('banned_at'),
                'banned' => $eloquentQuery->whereNotNull('banned_at'),
                'all_inactive' => $eloquentQuery->where(function ($q) {
                    $q->where('is_active', false)->orWhereNotNull('banned_at');
                }),
                default => null,
            };
        }

        if ($query->search) {
            $searchTerm = "%{$query->search}%";
            $eloquentQuery->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', $searchTerm)
                  ->orWhere('email', 'LIKE', $searchTerm);
            });
        }

        $allowedSorts = ['id', 'name', 'email', 'created_at', 'updated_at', 'role', 'is_active'];
        $sortBy = in_array($query->sortBy, $allowedSorts) ? $query->sortBy : 'created_at';
        $sortDirection = strtolower($query->sortDirection) === 'asc' ? 'asc' : 'desc';

        $eloquentQuery->orderBy($sortBy, $sortDirection);

        return $eloquentQuery->paginate($query->perPage);
    }
}
