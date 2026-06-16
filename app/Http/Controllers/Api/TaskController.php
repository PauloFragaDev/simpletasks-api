<?php

namespace App\Http\Controllers\Api;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'status'     => ['nullable', Rule::enum(TaskStatus::class)],
            'priority'   => ['nullable', Rule::enum(TaskPriority::class)],
            'sort_by'    => ['nullable', Rule::in(['created_at', 'updated_at', 'title', 'due_date', 'priority', 'status'])],
            'sort_order' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page'   => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = $request->user()->tasks();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        $sortBy    = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $tasks = $query->paginate($request->integer('per_page', 15));

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $request->user()->tasks()->create($request->validated());

        return response()->json([
            'message' => 'Task created successfully',
            'task'    => new TaskResource($task),
        ], 201);
    }

    public function show(Task $task): TaskResource|JsonResponse
    {
        $this->authorize('view', $task);

        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $task->update($request->validated());

        return response()->json([
            'message' => 'Task updated successfully',
            'task'    => new TaskResource($task),
        ]);
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully',
        ]);
    }
}
