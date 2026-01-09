<?php

namespace Tests\Unit;

use App\Models\VikRace;
use App\Models\VikRaid;
use App\Models\VikAccepter;
use App\Models\VikEquipe;
use App\Models\VikDossard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VikRaceModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test race can be created
     */
    public function test_race_can_be_created()
    {
        $race = VikRace::create([
            'COU_NUM' => 1,
            'COU_NOM' => 'Test Course',
            'INS_ID' => 1,
            'TYP_NUM' => 1,
            'COU_DATE_DEPART' => now(),
            'COU_DATE_FIN' => now()->addDays(1),
        ]);

        $this->assertNotNull($race->COU_NUM);
        $this->assertEquals('Test Course', $race->COU_NOM);
    }

    /**
     * Test race has many dossards
     */
    public function test_race_has_many_dossards()
    {
        $race = VikRace::create([
            'COU_NUM' => 1,
            'COU_NOM' => 'Test Course',
            'INS_ID' => 1,
            'TYP_NUM' => 1,
            'COU_DATE_DEPART' => now(),
            'COU_DATE_FIN' => now()->addDays(1),
        ]);

        VikDossard::create([
            'COU_NUM' => $race->COU_NUM,
            'DOSS_NUM' => 1,
        ]);

        $this->assertEquals(1, $race->dossards()->count());
    }

    /**
     * Test race has many teams
     */
    public function test_race_has_many_teams()
    {
        $race = VikRace::create([
            'COU_NUM' => 1,
            'COU_NOM' => 'Test Course',
            'INS_ID' => 1,
            'TYP_NUM' => 1,
            'COU_DATE_DEPART' => now(),
            'COU_DATE_FIN' => now()->addDays(1),
        ]);

        VikEquipe::create([
            'COU_NUM' => $race->COU_NUM,
            'EQU_NUM' => 1,
            'INS_ID' => 1,
            'EQU_NOM' => 'Test Team',
        ]);

        $this->assertEquals(1, $race->equipes()->count());
    }

    /**
     * Test race has many acceptances
     */
    public function test_race_has_many_acceptances()
    {
        $race = VikRace::create([
            'COU_NUM' => 1,
            'COU_NOM' => 'Test Course',
            'INS_ID' => 1,
            'TYP_NUM' => 1,
            'COU_DATE_DEPART' => now(),
            'COU_DATE_FIN' => now()->addDays(1),
        ]);

        VikAccepter::create([
            'COU_NUM' => $race->COU_NUM,
            'TRA_ID' => 1,
            'ACC_PRIX' => 50.00,
        ]);

        $this->assertEquals(1, $race->acceptances()->count());
    }

    /**
     * Test race table name is correct
     */
    public function test_race_table_name()
    {
        $race = new VikRace();
        $this->assertEquals('VIK_COURSE', $race->getTable());
    }

    /**
     * Test race primary key is correct
     */
    public function test_race_primary_key()
    {
        $race = new VikRace();
        $this->assertEquals('COU_NUM', $race->getKeyName());
    }

    /**
     * Test race timestamps disabled
     */
    public function test_race_timestamps_disabled()
    {
        $race = new VikRace();
        $this->assertFalse($race->timestamps);
    }
}
