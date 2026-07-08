<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MyProfileService
{
    public function show(User $user): User
    {

        $relation = $user->role->relation();

        return $relation ? $user->load($relation) : $user;
    }

    public function update(User $user, array $data)
    {

        return DB::transaction(function () use ($user, $data) {
            $user->update([
                'name' => $data['name'] ?? $user->name,
                'phone' => $data['phone'] ?? $user->phone,
                "profile_picture" => $data['profile_picture'] ?? $user->profile_picture,
            ]);

            $profileData = [
                "date_of_birth" => $data['date_of_birth'] ?? null,
                "gender" => $data['gender'] ?? null,
                "department" => $data['department'] ?? null
            ];

            $this->updateProfileForUser($user, $profileData);

            $relation = $user->role->relation();

            return $relation ? $user->load($relation) : $user;
        });
    }

    private function updateProfileForUser(User $user, array $profileData): void
    {
        match ($user->role) {
            UserRole::Student => $user->student()->updateOrCreate([], $profileData),
            UserRole::Teacher => $user->teacher()->updateOrCreate([], $profileData),
            default => null,
        };
    }
}
