<?php

namespace Tests\Feature;

use App\Models\MonitoredFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_watch_accepts_path_in_a_json_request(): void
    {
        $response = $this->postJson('/api/watch', [
            'path' => base_path('watched/.env'),
        ]);

        $response->assertCreated()
            ->assertJsonPath('file_type', 'env')
            ->assertJsonPath('is_active', true);

        $this->assertDatabaseHas('monitored_files', [
            'path' => str_replace('\\', '/', base_path('watched/.env')),
        ]);
    }

    public function test_watch_requires_path(): void
    {
        $this->postJson('/api/watch', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['path']);
    }
}