<?php
namespace App\Http\Controllers\Api;

use App\Actions\Task\CreateTaskAction;
use App\Actions\Task\DeleteTaskAction;
use App\Actions\Task\UpdateTaskAction;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    use ApiResponse;
    public function __construct(
        private CreateTaskAction $createTask,
        private UpdateTaskAction $updateTask,
        private DeleteTaskAction $deleteTask,
    ) {}

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

        $query->orderBy(
            $request->get('sort_by', 'created_at'),
            $request->get('sort_order', 'desc')
        );

        return TaskResource::collection(
            $query->paginate($request->integer('per_page', 15))
        );
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->createTask->handle($request->user(), $request->validated());

        return $this->success(data: new TaskResource($task), message: 'Task created successfully.', status: 201);
    }

    public function show(Task $task): TaskResource|JsonResponse
    {
        $this->authorize('view', $task);
        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $task = $this->updateTask->handle($task, $request->validated());

        return $this->success(data: new TaskResource($task), message: 'Task updated successfully.');
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);
        $this->deleteTask->handle($task);

        return $this->success(message: 'Task deleted successfully.');
    }
}
