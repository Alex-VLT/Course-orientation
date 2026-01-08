<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LogsRedirectTest extends TestCase
{
    public function test_logs_route_redirects_to_laravel_prefix(): void
    {
        $response = $this->get('/logs/test-log');

        $response->assertRedirect('/laravel/logs/test-log');
    }

    public function test_prefixed_logs_route_returns_log_content(): void
    {
        File::ensureDirectoryExists(storage_path('logs'));

        $path = storage_path('logs/test-log.log');
        File::put($path, "hello from test\n");

        try {
            $response = $this->get('/laravel/logs/test-log');

            $response->assertOk();
            $response->assertSeeText('hello from test');
        } finally {
            File::delete($path);
        }
    }

    public function test_prefixed_logs_route_rejects_invalid_names(): void
    {
        $response = $this->get('/laravel/logs/..');

        $response->assertNotFound();
    }
}
