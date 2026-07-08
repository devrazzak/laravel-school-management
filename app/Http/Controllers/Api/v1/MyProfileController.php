<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateMyProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\MyProfileService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class MyProfileController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly MyProfileService $myProfileService) {}

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $user = $this->myProfileService->show($request->user());

        return $this->successResponse(new UserResource($user), 'Profile retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMyProfileRequest $request)
    {
        $user = $request->user();

        $this->myProfileService->update($user, $request->validated());

        return $this->successResponse(new UserResource($user), 'Profile updated successfully.');
    }
}
