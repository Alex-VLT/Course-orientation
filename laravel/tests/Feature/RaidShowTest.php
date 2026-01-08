<?php

namespace Tests\Feature;

use App\Models\VikRaid;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RaidShowTest extends TestCase
{
    use DatabaseTransactions;

    private function uniqueRaidNum(): int
    {
        for ($i = 0; $i < 50; $i++) {
            $id = random_int(100000, 999999);
            if (! DB::table('vik_raid')->where('RAID_NUM', $id)->exists()) {
                return $id;
            }
        }

        return random_int(100000, 999999);
    }

    /** @test */
    public function raid_show_url_returns_ok(): void
    {
        $raidNum = $this->uniqueRaidNum();

        $clubNum = random_int(100000, 999999);
        DB::table('vik_club')->insert([
            'CLU_NUM' => $clubNum,
            'CLU_NOM' => 'Club Test',
            'INS_ID' => null,
        ]);

        $raid = VikRaid::create([
            'RAID_NUM' => $raidNum,
            'CLU_NUM' => $clubNum,
            'INS_ID' => null,
            'RAID_NOM' => 'Raid Show Test',
        ]);

        $response = $this->get('/raid/'.$raid->RAID_NUM);

        $response->assertStatus(200);
        $response->assertSee('Raid Show Test');
    }
}
