<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\VikRaid;

class RaidShowTest extends TestCase
{
    /** @test */
    public function raid_show_url_returns_ok(): void
    {
        $raid = VikRaid::create([
            'RAID_NUM' => 444444,
            'RAID_NOM' => 'Raid Show Test',
        ]);

        $response = $this->get('/raid/' . $raid->RAID_NUM);

        $response->assertStatus(200);
        $response->assertSee('Raid Show Test');
    }
}
