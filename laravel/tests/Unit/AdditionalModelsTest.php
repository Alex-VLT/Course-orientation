<?php

namespace Tests\Unit;

use App\Models\VikClub;
use App\Models\VikAccepter;
use App\Models\VikTypeCourse;
use App\Models\VikTrancheAge;
use App\Models\VikDossard;
use App\Models\Participate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VikClubModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test club can be created
     */
    public function test_club_can_be_created()
    {
        $club = VikClub::create([
            'CLU_NOM' => 'Test Club',
            'CLU_ADRESSE' => '123 Main St',
            'CLU_CODE_POSTAL' => '75001',
            'CLU_VILLE' => 'Paris',
            'INS_ID' => 1,
        ]);

        $this->assertNotNull($club->CLU_NUM);
        $this->assertEquals('Test Club', $club->CLU_NOM);
    }

    /**
     * Test club table name
     */
    public function test_club_table_name()
    {
        $club = new VikClub();
        $this->assertEquals('VIK_CLUB', $club->getTable());
    }
}

class VikAccepterModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test accepter can be created
     */
    public function test_accepter_can_be_created()
    {
        $accepter = VikAccepter::create([
            'COU_NUM' => 1,
            'TRA_ID' => 1,
            'ACC_PRIX' => 50.00,
        ]);

        $this->assertNotNull($accepter->COU_NUM);
        $this->assertEquals(50.00, (float) $accepter->ACC_PRIX);
    }

    /**
     * Test accepter table name
     */
    public function test_accepter_table_name()
    {
        $accepter = new VikAccepter();
        $this->assertEquals('VIK_ACCEPTER', $accepter->getTable());
    }
}

class VikTypeCourseModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test type course can be created
     */
    public function test_type_course_can_be_created()
    {
        $type = VikTypeCourse::create([
            'TYP_NUM' => 1,
            'TYP_LABEL' => 'Trail Running',
        ]);

        $this->assertNotNull($type->TYP_NUM);
        $this->assertEquals('Trail Running', $type->TYP_LABEL);
    }

    /**
     * Test type course table name
     */
    public function test_type_course_table_name()
    {
        $type = new VikTypeCourse();
        $this->assertEquals('VIK_TYPE_COURSE', $type->getTable());
    }
}

class VikTrancheAgeModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test age bracket can be created
     */
    public function test_age_bracket_can_be_created()
    {
        $tranche = VikTrancheAge::create([
            'TRA_ID' => 1,
            'TRA_AGE_MIN' => 18,
            'TRA_AGE_MAX' => 35,
        ]);

        $this->assertNotNull($tranche->TRA_ID);
        $this->assertEquals(18, $tranche->TRA_AGE_MIN);
        $this->assertEquals(35, $tranche->TRA_AGE_MAX);
    }

    /**
     * Test age bracket table name
     */
    public function test_age_bracket_table_name()
    {
        $tranche = new VikTrancheAge();
        $this->assertEquals('vik_tranche_age', $tranche->getTable());
    }
}

class VikDossardModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test bib can be created
     */
    public function test_bib_can_be_created()
    {
        $dossard = VikDossard::create([
            'COU_NUM' => 1,
            'DOSS_NUM' => 1,
            'EQUIPE_NUM' => null,
            'DOSS_DISTRIBUE' => false,
        ]);

        $this->assertNotNull($dossard->DOSS_ID);
        $this->assertEquals(1, $dossard->DOSS_NUM);
    }

    /**
     * Test bib table name
     */
    public function test_bib_table_name()
    {
        $dossard = new VikDossard();
        $this->assertEquals('VIK_DOSSARD', $dossard->getTable());
    }
}

class ParticipateModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test participation can be created
     */
    public function test_participation_can_be_created()
    {
        $user = User::factory()->create();
        
        $participate = Participate::create([
            'INS_ID' => $user->INS_ID,
            'COU_NUM' => 1,
            'EQU_NUM' => 1,
            'PAR_PARTICIPE' => 1,
        ]);

        $this->assertNotNull($participate);
        $this->assertEquals(1, $participate->EQU_NUM);
    }

    /**
     * Test participation belongs to user
     */
    public function test_participation_belongs_to_user()
    {
        $user = User::factory()->create();
        
        $participate = Participate::create([
            'INS_ID' => $user->INS_ID,
            'COU_NUM' => 1,
            'EQU_NUM' => 1,
            'PAR_PARTICIPE' => 1,
        ]);

        $this->assertEquals($user->INS_ID, $participate->user->INS_ID);
    }

    /**
     * Test participation table name
     */
    public function test_participation_table_name()
    {
        $participate = new Participate();
        $this->assertEquals('vik_participer', $participate->getTable());
    }
}
