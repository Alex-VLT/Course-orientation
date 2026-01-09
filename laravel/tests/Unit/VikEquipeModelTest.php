<?php

namespace Tests\Unit;

use App\Models\VikEquipe;
use App\Models\Participate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VikEquipeModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test team can be created
     */
    public function test_team_can_be_created()
    {
        $team = VikEquipe::create([
            'COU_NUM' => 1,
            'EQU_NUM' => 1,
            'INS_ID' => 1,
            'EQU_NOM' => 'Test Team',
        ]);

        $this->assertNotNull($team->EQU_NUM);
        $this->assertEquals('Test Team', $team->EQU_NOM);
    }

    /**
     * Test team has many participations
     */
    public function test_team_has_many_participations()
    {
        $user = User::factory()->create();
        
        $team = VikEquipe::create([
            'COU_NUM' => 1,
            'EQU_NUM' => 1,
            'INS_ID' => $user->INS_ID,
            'EQU_NOM' => 'Test Team',
        ]);

        Participate::create([
            'INS_ID' => $user->INS_ID,
            'COU_NUM' => $team->COU_NUM,
            'EQU_NUM' => $team->EQU_NUM,
            'PAR_PARTICIPE' => 1,
        ]);

        $this->assertEquals(1, $team->participations()->count());
    }

    /**
     * Test team belongs to creator user
     */
    public function test_team_belongs_to_creator()
    {
        $user = User::factory()->create();
        
        $team = VikEquipe::create([
            'COU_NUM' => 1,
            'EQU_NUM' => 1,
            'INS_ID' => $user->INS_ID,
            'EQU_NOM' => 'Test Team',
        ]);

        $this->assertEquals($user->INS_ID, $team->createur->INS_ID);
    }

    /**
     * Test team table name
     */
    public function test_team_table_name()
    {
        $team = new VikEquipe();
        $this->assertEquals('vik_equipe', $team->getTable());
    }

    /**
     * Test team castings
     */
    public function test_team_castings()
    {
        $team = VikEquipe::create([
            'COU_NUM' => 1,
            'EQU_NUM' => 1,
            'INS_ID' => 1,
            'EQU_NOM' => 'Test Team',
            'EQU_ORDRE_ARRIVEE' => 1,
            'EQU_TEMPS' => 120,
            'EQU_POINTS' => 100,
        ]);

        $this->assertIsInt($team->EQU_ORDRE_ARRIVEE);
        $this->assertIsInt($team->EQU_TEMPS);
        $this->assertIsInt($team->EQU_POINTS);
    }

    /**
     * Test team has no timestamps
     */
    public function test_team_no_timestamps()
    {
        $team = new VikEquipe();
        $this->assertFalse($team->timestamps);
    }
}
