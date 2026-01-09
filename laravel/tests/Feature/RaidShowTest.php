<?php

namespace Tests\Feature;

use Tests\TestCase;

class RaidShowTest extends TestCase
{
    public function test_raid_show_url_returns_ok(): void
    {
        // Utilise l'ID existant de la base fournie (g1_db.sql)
        $response = $this->get('/raid/100');
        $response->assertOk();
    }
}
