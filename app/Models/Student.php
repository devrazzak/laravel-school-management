<?php

namespace App\Models;

use App\Enums\StudentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'registration_number',
        'phone',
        'date_of_birth',
        'gender',
        'department',
        'cgpa',
        'status',
        'profile_picture',
    ];

    protected $attributes = [
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }
        return $query->where(function (Builder $q) use ($term) {
            $q->whereHas('user', function (Builder $uq) use ($term) {
                $uq->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            })
                ->orWhere('registration_number', 'like', "%{$term}%")
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
