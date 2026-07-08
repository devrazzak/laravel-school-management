<?php

namespace App\Services;

use App\Models\Counter;
use Illuminate\Support\Facades\DB;

class RegistrationNumberService
{
    public function generateStudentNumber(): string
    {
        return DB::transaction(function () {

            $year = now()->year;

            $counter = Counter::lockForUpdate()->firstOrCreate(
                [
                    'type' => 'student',
                    'year' => $year,
                ],
                [
                    'last_number' => 0,
                ]
            );

            $counter->increment('last_number');

            return sprintf(
                'STU-%d-%06d',
                $year,
                $counter->last_number
            );
        });
    }

    public function generateEmployeeId(): string
    {
        return DB::transaction(function () {

            $counter = Counter::lockForUpdate()->firstOrCreate(
                [
                    'type' => 'employee',
                    'year' => null,
                ],
                [
                    'last_number' => 0,
                ]
            );

            $counter->increment('last_number');

            return sprintf(
                'EMP-%06d',
                $counter->last_number
            );
        });
    }
}
