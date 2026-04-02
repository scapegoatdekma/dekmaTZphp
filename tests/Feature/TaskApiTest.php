<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_task_with_normalized_payload(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => "   <b>Buy milk</b>   ",
            'description' => "  <script>alert('x')</script>\r\nRemember lactose free  ",
            'status' => 'DONE',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Buy milk')
            ->assertJsonPath('data.description', "alert('x')\nRemember lactose free")
            ->assertJsonPath('data.status', TaskStatus::Done->value);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Buy milk',
            'status' => TaskStatus::Done->value,
        ]);
    }

    public function test_it_validates_required_fields(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => '   ',
            'status' => 'hacked',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'status']);
    }

    public function test_it_lists_tasks_with_filters_and_pagination(): void
    {
        Task::factory()->count(2)->create(['status' => TaskStatus::Pending->value, 'title' => 'Daily plan']);
        Task::factory()->count(2)->create(['status' => TaskStatus::Done->value, 'title' => 'Archive report']);

        $response = $this->getJson('/api/tasks?status=done&search=archive&per_page=1');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.status', TaskStatus::Done->value);
    }

    public function test_it_shows_updates_and_deletes_a_task(): void
    {
        $task = Task::factory()->create([
            'title' => 'Initial title',
            'status' => TaskStatus::Pending->value,
        ]);

        $this->getJson("/api/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonPath('data.title', 'Initial title');

        $this->putJson("/api/tasks/{$task->id}", [
            'title' => '  <i>Updated</i> title  ',
            'status' => 'in_progress',
        ])->assertOk()
            ->assertJsonPath('data.title', 'Updated title')
            ->assertJsonPath('data.status', TaskStatus::InProgress->value);

        $this->deleteJson("/api/tasks/{$task->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
