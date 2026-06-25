<?php

namespace App\Models;

use App\Enums\StudentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'department',
        'cgpa',
        'status',
        'profile_picture',
    ];

    protected $attribute = [
        'status' => StudentStatus::Active->value,
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'cgpa' => 'decimal:2',
            'status' => StudentStatus::class,
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }
        return $query->where(function (Builder $q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('department', 'like', "%{$term}%");
        });
    }

    public function scopeStatus(Builder $query, ?StudentStatus $status): Builder
    {

        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeDepartment(Builder $query, ?string $department): Builder
    {
        return $department ? $query->where('department', $department) : $query;
    }
}
