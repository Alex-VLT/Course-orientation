<?php

namespace Tests\Feature;

use App\Models\VikRace;
use App\Models\VikRaid;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RaceTeamsCountTest extends TestCase
{
    use DatabaseTransactions;

    private function uniqueIntId(string $table, string $column, int $min = 100000, int $max = 999999): int
    {
        for ($i = 0; $i < 50; $i++) {
            $id = random_int($min, $max);
            if (! DB::table($table)->where($column, $id)->exists()) {
                return $id;
            }
        }

        return random_int($min, $max);
    }

    /** @test */
    public function it_displays_number_of_registered_teams_on_course_page(): void
    {
        $raidNum = $this->uniqueIntId('vik_raid', 'RAID_NUM');
        $courseNum = $this->uniqueIntId('vik_course', 'COU_NUM');

        $clubNum = $this->uniqueIntId('vik_club', 'CLU_NUM');
        DB::table('vik_club')->insert([
            'CLU_NUM' => $clubNum,
            'CLU_NOM' => 'Club Test',
            'INS_ID' => null,
        ]);

        $raid = VikRaid::create([
            'RAID_NUM' => $raidNum,
            'CLU_NUM' => $clubNum,
            'INS_ID' => null,
            'RAID_NOM' => 'Raid Test',
        ]);

        $race = VikRace::create([
            'COU_NUM' => $courseNum,
            'RAID_NUM' => $raid->RAID_NUM,
            'INS_ID' => null,
            'COU_NOM' => 'Course Test',
            'COU_DATE_DEPART' => now(),
            'COU_DATE_FIN' => now()->addHour(),
            'COU_NB_EQU_MAX' => 10,
        ]);

        DB::table('vik_equipe')->insert([
            ['EQU_NUM' => 1001, 'COU_NUM' => $race->COU_NUM],
            ['EQU_NUM' => 1002, 'COU_NUM' => $race->COU_NUM],
            ['EQU_NUM' => 1003, 'COU_NUM' => $race->COU_NUM],
        ]);

        $response = $this->get('/course/'.$race->COU_NUM);

        $response->assertStatus(200);
        $response->assertSee('Équipes inscrites : 3');
    }
}
