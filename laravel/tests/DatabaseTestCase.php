<?php

namespace Tests;

use Database\Seeders\TestDataSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class DatabaseTestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Indicates whether the default seeding should be performed.
     *
     * @var bool
     */
    protected bool $seed = false;

    /**
     * Setup the test environment.
     *
     * Run migrations and seed with test data
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Only migrate if we need to test with database
        if ($this->shouldMigrate()) {
            $this->artisan('migrate:fresh');
            $this->seed(TestDataSeeder::class);
        }
    }

    /**
     * Determine if we should migrate for this test
     *
     * Override in subclasses that need database access
     */
    protected function shouldMigrate(): bool
    {
        return false;
    }
}
