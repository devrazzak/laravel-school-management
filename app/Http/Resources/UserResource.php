<?php

namespace App\Http\Resources;

use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'profile_picture' => $this->profile_picture ? asset('storage/' . $this->profile_picture) : null,
            // $this->mergeWhen($this->profileData() !== null, [
            //     'profile' => $this->profileData(),
            // ]),
        ];
    }

    private function profileData(): ?array
    {
        $profile = $this->resource->profile();

        return match (true) {
            $profile === null => null,
            $this->role === UserRole::Student => (new StudentResource($profile))->resolve(),
            $this->role === UserRole::Teacher => (new TeacherResource($profile))->resolve(),
            default => null,
        };
    }
}
