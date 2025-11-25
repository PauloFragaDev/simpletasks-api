<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    /**
     * Display a listing of the user's tasks with optional filters and pagination.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $request->user()->tasks();

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $tasks = $query->paginate($request->get('per_page', 15));

        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created task.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $request->user()->tasks()->create($request->validated());

        return response()->json([
            'message' => 'Task created successfully',
            'task' => new TaskResource($task),
        ], 201);
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task): TaskResource|JsonResponse
    {
        // Authorization: ensure the task belongs to the authenticated user
        if ($task->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        return new TaskResource($task);
    }

    /**
     * Update the specified task.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        // Authorization is handled in UpdateTaskRequest
        $task->update($request->validated());

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => new TaskResource($task),
        ]);
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Task $task): JsonResponse
    {
        // Authorization: ensure the task belongs to the authenticated user
        if ($task->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully',
        ]);
    }
}
