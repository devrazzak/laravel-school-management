<?php

namespace App\Services;

use App\Enums\UserCreationType;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Events\UserCreatedByAdmin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = min($request->input('per_page', 10), 100); // Limit to a maximum of 100 per page
        return User::query()
            ->search($request->query('search'))
            ->status($request->query('status'))
            ->when(
                $request->filled('sort_by'),
                fn($q) => $q->orderBy(
                    $this->safeSortColumn($request->query('sort_by')),
                    $request->query('sort_dir') === 'desc' ? 'desc' : 'asc'
                ),
                fn($q) => $q->latest() // Default sorting by latest
            )
            ->paginate($perPage)
            ->withQueryString();
    }


    public function create(array $data, UserCreationType $type = UserCreationType::SelfRegistered): User
    {
        return DB::transaction(function () use ($data, $type) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $type === UserCreationType::SelfRegistered ? $data['password'] : null,
                'role' => $data['role'],
                'status' => $data['status'] ?? UserStatus::Active->value,
            ]);

            $this->createOrUpdateProfileForUser($user, $data['profile'] ?? []);

            $relation = $this->profileRelation($user->role);

            $user = $relation ? $user->load($relation) : $user;

            // Trigger the event if the user was created by an admin
            if ($type === UserCreationType::AdminCreation) {
                event(new UserCreatedByAdmin($user));
            }

            return $user;
        });
    }

    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $user->update([
                'name' => $data['name'] ?? $user->name,
                'email' => $data['email'] ?? $user->email,
                'role' => $data['role'] ?? $user->role,
                'status' => $data['status'] ?? $user->status,
            ]);

            $this->createOrUpdateProfileForUser($user, $data['profile'] ?? []);

            $relation = $this->profileRelation($user->role);

            return $relation ? $user->load($relation) : $user;
        });
    }

    public function delete(User $user): void
    {
        $user->delete(); // Soft delete
    }

    // This method ensures that only allowed columns can be used for sorting to prevent SQL injection or errors.
    private function safeSortColumn(?string $column): string
    {
        $allowed = ['name', 'email', 'status', 'created_at'];
        return in_array($column, $allowed, true) ? $column : 'created_at';
    }

    private function createOrUpdateProfileForUser(User $user, array $profileData): void
    {
        match ($user->role) {
            UserRole::Student => $user->student()->updateOrCreate([], $profileData),
            UserRole::Teacher => $user->teacher()->updateOrCreate([], $profileData),
            default => null,
        };
    }



    private function profileRelation(UserRole $role): ?string
    {
        return match ($role) {
            UserRole::Student => 'student',
            UserRole::Teacher => 'teacher',
            default => null,
        };
    }
}
