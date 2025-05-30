<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class TodoistService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.todoist.com/rest/v2';

    public function __construct()
    {
        $this->apiKey = config('services.todoist.api_key');
    }

    public function getTasks()
    {
        $response = Http::withToken($this->apiKey)
            ->get("{$this->baseUrl}/tasks");

        return $response->json();
    }

    public function createTask($content, $dueDate = null)
    {
        $data = [
            'content' => $content,
        ];

        if ($dueDate) {
            $data['due_date'] = $dueDate;
        }

        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/tasks", $data);

        return $response->json();
    }

    public function updateTask($taskId, $content, $dueDate = null)
    {
        $data = [
            'content' => $content,
        ];

        if ($dueDate) {
            $data['due_date'] = $dueDate;
        }

        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/tasks/{$taskId}", $data);

        return $response->json();
    }

    public function completeTask($taskId)
    {
        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/tasks/{$taskId}/close");

        return $response->successful();
    }

    public function deleteTask($taskId)
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->delete("{$this->baseUrl}/tasks/{$taskId}");

            if (!$response->successful()) {
                throw new \Exception("Failed to delete task in Todoist. Status: {$response->status()}, Error: " . $response->body());
            }

            return true;
        } catch (\Exception $e) {
            \Log::error("Todoist task deletion failed: " . $e->getMessage());
            throw $e;
        }
    }
} 