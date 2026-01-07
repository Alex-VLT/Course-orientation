<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\VikRaid;
use App\Models\VikRace;

class RaceTeamsCountTest extends TestCase
{
    /** @test */
    public function it_displays_number_of_registered_teams_on_course_page(): void
    {
        $raid = VikRaid::create([
            'RAID_NUM' => 999999,
            'RAID_NOM' => 'Raid Test',
        ]);

        $race = VikRace::create([
            'COU_NUM' => 888888,
            'RAID_NUM' => $raid->RAID_NUM,
            'COU_NOM' => 'Course Test',
            'COU_DATE_DEPART' => now(),
            'COU_DATE_FIN' => now()->addHour(),
        ]);

        DB::table('VIK_EQUIPE')->insert([
            ['EQU_NUM' => 1001, 'COU_NUM' => $race->COU_NUM],
            ['EQU_NUM' => 1002, 'COU_NUM' => $race->COU_NUM],
            ['EQU_NUM' => 1003, 'COU_NUM' => $race->COU_NUM],
        ]);

        $response = $this->get('/course/' . $race->COU_NUM);

        $response->assertStatus(200);
        $response->assertSee("Équipes inscrites : 3");
    }
}
