<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    public function store(Request $request)
    {
        // Валидация входных данных
        $validatedData = $request->validate([
            'id_user' => 'required|integer',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'finish_at' => 'nullable|date',
        ]);

        $task = Task::create([
            'id_user' => $validatedData['id_user'],
            'title' => $validatedData['title'],
            'description' => $validatedData['description'] ?? null,
            'status' => $validatedData['status'] ?? 'Created',
            'finish_at' => $validatedData['finish_at'] ?? null,
        ]);

        return response()->json([
            'message' => 'Задача успешно создана!',
            'task' => $task,
        ], 201);
    }



    public function multiStore(Request $request)
{
    // Валидация входных данных
    $validatedData = $request->validate([
        'tasks.*.id_user' => 'required|integer',
        'tasks.*.title' => 'required|string|max:255',
        'tasks.*.description' => 'nullable|string',
        'tasks.*.status' => 'nullable|string',
        'tasks.*.finish_at' => 'nullable|date',
    ]);

    $tasks = [];
    foreach ($validatedData['tasks'] as $taskData) {
        $task = Task::create([
            'id_user' => $taskData['id_user'],
            'title' => $taskData['title'],
            'description' => $taskData['description'] ?? null,
            'status' => $taskData['status'] ?? 'Created',
            'finish_at' => $taskData['finish_at'] ?? null,
        ]);
        $tasks[] = $task;
    }

    return response()->json([
        'message' => 'Задачи успешно созданы!',
        'tasks' => $tasks,
    ], 201);
}
}
