<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListTasksRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    public function index(ListTasksRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();

        $query = Task::query()
            ->when(
                isset($validated['status']),
                fn ($builder) => $builder->where('status', $validated['status'])
            )
            ->when(
                isset($validated['search']),
                function ($builder) use ($validated) {
                    $needle = $this->escapeLikeWildcards($validated['search']);

                    $builder->where(function ($nested) use ($needle) {
                        $nested->where('title', 'like', "%{$needle}%")
                            ->orWhere('description', 'like', "%{$needle}%");
                    });
                }
            )
            ->latest();

        $perPage = $validated['per_page'] ?? 15;

        return TaskResource::collection(
            $query->paginate($perPage)->withQueryString()
        );
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = Task::create($request->validated());

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Task $task): TaskResource
    {
        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        $task->update($request->validated());

        return new TaskResource($task->refresh());
    }

    public function destroy(Task $task): Response
    {
        $task->delete();

        return response()->noContent();
    }

    private function escapeLikeWildcards(string $value): string
    {
        return addcslashes($value, '\\%_');
    }
}
