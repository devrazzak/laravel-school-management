<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MyProfileService
{
    public function show(User $user): User
    {

        $relation = $user->role->relation();

        return $relation ? $user->load($relation) : $user;
    }

    public function update(User $user, array $data)
    {
        // Logic to update the user's profile information
    }
}
