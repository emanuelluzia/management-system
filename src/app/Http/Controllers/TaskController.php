<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Category;
use App\Services\TaskService;
use App\Services\TaskQueryService;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function __construct(
        protected TaskService $taskService,
        protected TaskQueryService $taskQueryService
    ) {}

    
    protected function extractFilters(Request $request): array
    {
        return [
            'status'      => $request->input('status'),
            'priority'    => $request->input('priority'),
            'category_id' => $request->input('category_id'),
            'search'      => $request->input('search'),
            'due_from'    => $request->input('due_from'),
            'due_to'      => $request->input('due_to'),
            'with'        => $request->input('with'), 

        ];
    }

    protected function extractSorting(Request $request): array
    {
        return [
            'sort_by'  => $request->input('sort_by', 'created_at'),
            'sort_dir' => $request->input('sort_dir', 'desc'),
        ];
    }

    public function index(Request $request): Response
    {
        $filters    = $this->extractFilters($request);
        $sorting    = $this->extractSorting($request);

        $tasks      = $this->taskQueryService->getAllTasks($filters, $sorting);
        $statistics = $this->taskQueryService->getTaskStatistics();

        return Inertia::render('Tasks/Index', [
            'tasks'       => TaskResource::collection($tasks),
            'filters'     => $filters,
            'sorting'     => $sorting,
            'statistics'  => $statistics,
        ]);
    }

    public function create(): Response
    {
        $categories = Category::query()
            ->select('id', 'name', 'parent_id')
            ->orderBy('name')
            ->get();

        return Inertia::render('Tasks/TaskForm', [
            'categories' => $categories, 
        ]);
    }

    public function store(TaskRequest $request)
    {
        $this->taskService->createTask($request->validated());

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Task created successfully!');
    }

    public function show(Task $task): Response
    {
        $task->load(['category:id,name']);

        return Inertia::render('Tasks/Show', [
            'task' => new TaskResource($task),
        ]);
    }

    public function edit(Task $task): Response
    {
        $task->load(['category:id,name']);

        $categories = Category::query()
            ->select('id', 'name', 'parent_id')
            ->orderBy('name')
            ->get();
        
        return Inertia::render('Tasks/TaskForm', [
            'task'       => new TaskResource($task),
            'categories' => $categories,
        ]);
    }

    public function update(TaskRequest $request, Task $task)
    {
        $this->taskService->updateTask($task, $request->validated());

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Task updated.');
    }

    public function destroy(Task $task)
    {
        $this->taskService->deleteTask($task);

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Task removed.');
    }

    public function restore(int $id)
    {
        $this->taskService->restoreTask($id);

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Task restored.');
    }
}
