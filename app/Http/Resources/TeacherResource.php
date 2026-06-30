<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherResource extends JsonResource
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
            'employee_id' => $this->employee_id,
            'designation' => $this->designation,
            'department' => $this->department,
            'phone' => $this->phone,
            'joining_date' => $this->joining_date?->format('Y-m-d'),
            'profile_picture' => $this->profile_picture ? asset('storage/' . $this->profile_picture) : null,
            'created_at' => $this->created_at?->toISO8601String(),
            'updated_at' => $this->updated_at?->toISO8601String(),
        ];
    }
}
