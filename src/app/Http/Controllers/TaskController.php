<?php

// app/Http/Controllers/TaskController.php
namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    private TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index()
    {
        $tasks = $this->taskService->all();
        // dd('tesre');
        return Inertia::render('Tasks/Index', ['tasks' => $tasks]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $this->taskService->store($data);
        return redirect()->route('tasks.index');
    }

    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'completed' => 'boolean',
        ]);
        $this->taskService->update($task, $data);
        return redirect()->route('tasks.index');
    }

    public function destroy(Task $task)
    {
        $this->taskService->delete($task);
        return redirect()->route('tasks.index');
    }

    public function restore($id)
    {
        $this->taskService->restore($id);
        return redirect()->route('tasks.index');
    }
}
