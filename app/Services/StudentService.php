<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class StudentService
{
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = min($request->input('per_page', 10), 100); // Limit to a maximum of 100 per page
        return Student::query()
            ->with('user') // Eager load the related user data
            ->search($request->query('search'))
            ->status($request->query('status'))
            ->when(
                $request->filled('sort_by'),
                fn($q) => $q->orderBy(
                    $this->safeSortColumn($request->query('sort_by')),
                    $request->query('sort_dir') === 'desc' ? 'desc' : 'asc'
                ),
                fn($q) => $q->latest() // Default sorting by latest
            )
            ->paginate($perPage)
            ->withQueryString();
    }


    public function create(array $data): Student
    {
        return Student::create($data);
    }

    public function update(Student $student, array $data): Student
    {
        $student->update($data);
        return $student;
    }

    public function delete(Student $student): void
    {
        $student->delete(); // Soft delete
    }

    // This method ensures that only allowed columns can be used for sorting to prevent SQL injection or errors.
    private function safeSortColumn(?string $column): string
    {
        $allowed = ['first_name', 'last_name', 'email', 'cgpa', 'status', 'created_at'];
        return in_array($column, $allowed, true) ? $column : 'created_at';
    }
}
