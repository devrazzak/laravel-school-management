<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Services\StudentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{

    use ApiResponse;

    public function __construct(private readonly StudentService $studentService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Student::class);

        $students = $this->studentService->list($request);

        return $this->paginatedResponse(
            StudentResource::collection($students),
            'Students retrieved successfully.'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentRequest $request)
    {
        $this->authorize('create', Student::class);

        $student = $this->studentService->create($request->validated());

        return $this->successResponse($student, 'Student created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student): JsonResponse
    {
        $this->authorize('view', $student);

        return $this->successResponse(new StudentResource($student), 'Student retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentRequest $request, Student $student): JsonResponse
    {
        $this->authorize('update', $student);

        $updatedStudent = $this->studentService->update($student, $request->validated());

        return $this->successResponse(new StudentResource($updatedStudent), 'Student updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student): JsonResponse
    {
        $this->authorize('delete', $student);

        $this->studentService->delete($student);

        return $this->successResponse(null, 'Student deleted successfully.');
    }
}
