<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateTaskRequest;
use App\Models\Task;
use App\Jobs\DeleteTaskJob;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = Task::all();
        return response()->json($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUpdateTaskRequest $request)
    {
        $validated = $request->validated();
        $task = Task::create($validated);
        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Tarefa não encontrada'], 404);
        }

        return response()->json($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreUpdateTaskRequest $request, string $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Tarefa não encontrada'], 404);
        }

        $validated = $request->validated();
        $task->update($validated);

        return response()->json($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Tarefa não encontrada'], 404);
        }

        $task->delete();
        return response()->json(null, 204);
    }

    /**
     * Toggle the finalizado status of the specified task.
     */
    public function toggle(string $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Tarefa não encontrada'], 404);
        }

        $task->finalizado = !$task->finalizado;
        $task->save();

        if ($task->finalizado) {
            // Dispara o job para rodar em 10 minutos
            DeleteTaskJob::dispatch($task->id)->delay(now()->addMinutes(10));
        }

        return response()->json($task);
    }
}
