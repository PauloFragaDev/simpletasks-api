<?php
namespace App\Http\Controllers\Api;

use App\Actions\Task\CreateTaskAction;
use App\Actions\Task\DeleteTaskAction;
use App\Actions\Task\UpdateTaskAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

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
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $tasks = QueryBuilder::for($request->user()->tasks())
            ->allowedFilters(
                AllowedFilter::exact('status'),
                AllowedFilter::exact('priority'),
            )
            ->allowedSorts('created_at', 'updated_at', 'title', 'due_date', 'priority', 'status')
            ->defaultSort('-created_at')
            ->paginate($request->integer('per_page', 15));

        return TaskResource::collection($tasks);
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
