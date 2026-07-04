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
            'role' => $this->role,
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'profile' => $this->profileData(),
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
