<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
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
            'user_id' => $this->user_id,
            'name' => $this->whenLoaded('user', fn() => $this->user->name),
            'email' => $this->whenLoaded('user', fn() => $this->user->email),
            'role' => $this->whenLoaded('user', fn() => $this->user->role),
            'phone' => $this->whenLoaded('user', fn() => $this->user->phone),
            'registration_number' => $this->registration_number,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'gender' => $this->gender,
            'department' => $this->department,
            'cgpa' => $this->cgpa,
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'profile_picture' => $this->whenLoaded('user', fn() => $this->user->profile_picture ? asset('storage/' . $this->user->profile_picture) : null),
            'created_at' => $this->created_at?->toISO8601String(),
            'updated_at' => $this->updated_at?->toISO8601String(),
        ];
    }
}
