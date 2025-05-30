<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\TodoistService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    protected $todoistService;

    public function __construct(TodoistService $todoistService)
    {
        $this->todoistService = $todoistService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = Auth::user()->tasks()->orderBy('due_date')->get();
        return response()->json([
            'status' => 'success',
            'data' => $tasks
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        try {
            $todoistTask = $this->todoistService->createTask(
                $request->title,
                $request->due_date
            );

            $task = Auth::user()->tasks()->create([
                'title' => $request->title,
                'description' => $request->description,
                'due_date' => $request->due_date,
                'todoist_id' => $todoistTask['id'] ?? null,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $task
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create task: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create task'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        $this->authorize('view', $task);
        return response()->json([
            'status' => 'success',
            'data' => $task
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        try {
            if ($task->todoist_id) {
                $this->todoistService->updateTask(
                    $task->todoist_id,
                    $request->title,
                    $request->due_date
                );
            }

            $task->update($request->all());
            return response()->json([
                'status' => 'success',
                'data' => $task
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update task: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update task'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        try {
            if (!Auth::check()) {
                Log::error("Task deletion failed: User not authenticated");
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $user = Auth::user();
            Log::info("Attempting to delete task {$task->id} by user {$user->id}");

            if ($user->id !== $task->user_id) {
                Log::error("Task deletion failed: Unauthorized - User {$user->id} attempted to delete task {$task->id} owned by user {$task->user_id}");
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            if (!$user->can('delete', $task)) {
                Log::error("Task deletion failed: Policy check failed for user {$user->id} on task {$task->id}");
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            if ($task->todoist_id) {
                try {
                    $this->todoistService->deleteTask($task->todoist_id);
                } catch (\Exception $e) {
                    Log::warning("Failed to delete task in Todoist, continuing with local deletion: " . $e->getMessage());
                }
            }

            $task->delete();
            Log::info("Task {$task->id} successfully deleted by user {$user->id}");
            return response()->json([
                'status' => 'success',
                'message' => 'Task deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error("Task deletion failed: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete task'
            ], 500);
        }
    }

    public function complete(Task $task)
    {
        try {
            if (!Auth::check()) {
                Log::error("Task completion failed: User not authenticated");
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $user = Auth::user();
            Log::info("Attempting to complete task {$task->id} by user {$user->id}");

            if ($user->id !== $task->user_id) {
                Log::error("Task completion failed: Unauthorized - User {$user->id} attempted to complete task {$task->id} owned by user {$task->user_id}");
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Toggle the completed status
            $newStatus = !$task->completed;

            if ($task->todoist_id) {
                try {
                    if ($newStatus) {
                        $this->todoistService->completeTask($task->todoist_id);
                    } else {
                        // For now, we'll just update the local status as Todoist doesn't have a reopen endpoint
                        Log::info("Todoist task status can't be uncompleted, only updating local status");
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to update task in Todoist, continuing with local update: " . $e->getMessage());
                }
            }

            $task->update(['completed' => $newStatus]);
            Log::info("Task {$task->id} " . ($newStatus ? 'completed' : 'uncompleted') . " by user {$user->id}");
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $task->id,
                    'completed' => $newStatus,
                    'message' => 'Task completion status updated successfully'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Task completion failed: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update task completion status'
            ], 500);
        }
    }
}
