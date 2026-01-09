<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
use App\Http\Controllers\VerifInscriptionController;
use App\Models\VerifInscription;

class VerifInscriptionControllerTest extends TestCase
{
    protected function createVikInscritTable()
    {
        if (env('DB_CONNECTION') !== 'sqlite' || env('DB_DATABASE') !== ':memory:') {
            $this->markTestSkipped('Skipping DB schema changes: tests must run against sqlite :memory: to be safe.');
        }
        Schema::create('vik_inscrit', function ($table) {
            $table->integer('INS_ID')->primary();
            $table->string('INS_PRENOM')->nullable();
            $table->string('INS_NOM')->nullable();
            $table->string('INS_NAISSANCE')->nullable();
            $table->string('INS_NUM_LICENCE')->nullable();
            $table->string('INS_NUM_PPS')->nullable();
            $table->string('INS_MAIL')->nullable();
        });
    }

    protected function createVikCourseTable()
    {
        if (env('DB_CONNECTION') !== 'sqlite' || env('DB_DATABASE') !== ':memory:') {
            $this->markTestSkipped('Skipping DB schema changes: tests must run against sqlite :memory: to be safe.');
        }
        Schema::create('vik_course', function ($table) {
            $table->integer('COU_NUM')->primary();
            $table->string('COU_NOM')->nullable();
            $table->string('COU_DATE_DEPART')->nullable();
            $table->string('COU_DATE_FIN')->nullable();
            $table->integer('COU_AGE_A')->nullable();
            $table->integer('COU_AGE_B')->nullable();
            $table->integer('COU_AGE_C')->nullable();
            $table->integer('COU_PART_PAR_EQU_MAX')->nullable();
            $table->integer('COU_NB_EQU_MAX')->nullable();
            $table->integer('COU_NB_PART_MAX')->nullable();
        });
    }

    protected function createVikParticiperTable()
    {
        if (env('DB_CONNECTION') !== 'sqlite' || env('DB_DATABASE') !== ':memory:') {
            $this->markTestSkipped('Skipping DB schema changes: tests must run against sqlite :memory: to be safe.');
        }
        Schema::create('vik_participer', function ($table) {
            $table->increments('id');
            $table->integer('INS_ID')->nullable();
            $table->integer('COU_NUM')->nullable();
            $table->integer('EQU_NUM')->nullable();
        });
    }

    protected function tearDownTables()
    {
        if (env('DB_CONNECTION') !== 'sqlite' || env('DB_DATABASE') !== ':memory:') {
            return;
        }
        Schema::dropIfExists('vik_participer');
        Schema::dropIfExists('vik_inscrit');
        Schema::dropIfExists('vik_course');
    }

    /** @test */
    public function validate_nb_participants_detects_limits()
    {
        $controller = new VerifInscriptionController();

        $course = (object)[
            'COU_PART_PAR_EQU_MAX' => 3,
            'COU_NB_EQU_MAX' => 2,
            'COU_NB_PART_MAX' => 6,
        ];

        // two existing teams (distinct equ_num 1 and 2) with total 4 participants
        $participations = collect([
            (object)['ins_id' => 10, 'equ_num' => 1],
            (object)['ins_id' => 11, 'equ_num' => 1],
            (object)['ins_id' => 12, 'equ_num' => 2],
            (object)['ins_id' => 13, 'equ_num' => 2],
        ]);

        // new team with 4 members exceeding COU_PART_PAR_EQU_MAX (3)
        $teamMembers = collect([
            (object)['INS_ID' => 20],
            (object)['INS_ID' => 21],
            (object)['INS_ID' => 22],
            (object)['INS_ID' => 23],
        ]);

        $res = $controller->validateNbParticipants($course, $participations, $teamMembers);
        $this->assertFalse($res['ok']);
        $this->assertStringContainsString('Trop de membres', implode(' | ', $res['messages']));

        // reduce team size to allowed value and check overall participants limit
        $teamMembers = collect([(object)['INS_ID'=>20], (object)['INS_ID'=>21], (object)['INS_ID'=>22]]);
        $res2 = $controller->validateNbParticipants($course, $participations, $teamMembers);
        $this->assertTrue($res2['ok']);

        // add more participations to exceed COU_NB_PART_MAX (6)
    $participations->push((object)['ins_id' => 14, 'equ_num' => 3]);
    $participations->push((object)['ins_id' => 15, 'equ_num' => 3]);
    $res3 = $controller->validateNbParticipants($course, $participations, $teamMembers);
    $this->assertFalse($res3['ok']);
    $msg = implode(' | ', $res3['messages']);
    // Depending on which limit triggers first the validator may report either
    // too many teams or too many total participants. Accept either message.
    $this->assertTrue(str_contains($msg, 'Nombre total de participants dépassé') || str_contains($msg, "Nombre d'équipe" ) || str_contains($msg, "Nombre d'équipes" ) || str_contains($msg, 'Nombre d\'équipes dépassé'));
    }

    /** @test */
    public function validate_age_checks_bounds_and_missing_birth()
    {
        $this->createVikInscritTable();

        // Create some inscrits
        DB::table('vik_inscrit')->insert([
            ['INS_ID' => 1, 'INS_PRENOM' => 'Alice', 'INS_NOM' => 'A', 'INS_NAISSANCE' => '2010-01-01'],
            ['INS_ID' => 2, 'INS_PRENOM' => 'Bob', 'INS_NOM' => 'B', 'INS_NAISSANCE' => '2005-06-01'],
            ['INS_ID' => 3, 'INS_PRENOM' => 'Charlie', 'INS_NOM' => 'C', 'INS_NAISSANCE' => null],
        ]);

        $course = (object)[
            'COU_NUM' => 99,
            'COU_DATE_DEPART' => '2025-01-01',
            'COU_AGE_A' => 12,
            'COU_AGE_B' => 16,
            'COU_AGE_C' => 18,
        ];

        $teamMembers = collect([
            (object)['INS_ID' => 1],
            (object)['INS_ID' => 2],
            (object)['INS_ID' => 3],
        ]);

        $controller = new VerifInscriptionController();
        $res = $controller->validateAge($course, $teamMembers);

        // Alice born 2010 -> 15 at 2025 -> >= A but < B
        // Bob born 2005 -> 19 at 2025 -> >= C
        // Charlie missing birth -> should trigger missing birth message
        $this->assertFalse($res['ok']);
        $this->assertNotEmpty($res['messages']);
        $this->assertStringContainsString('date de naissance manquante', implode(' | ', $res['messages']));

    // cleanup
    $this->tearDownTables();
    }

    /** @test */
    public function validate_equipe_uses_db_and_respects_limits()
    {
        $this->createVikCourseTable();
        $this->createVikParticiperTable();
        $this->createVikInscritTable();

        // Insert course with max 1 team and max 2 participants total
        DB::table('vik_course')->insert([
            'COU_NUM' => 200,
            'COU_NOM' => 'TestCourse',
            'COU_DATE_DEPART' => '2025-01-01',
            'COU_DATE_FIN' => '2025-01-02',
            'COU_AGE_A' => 10,
            'COU_AGE_B' => 14,
            'COU_AGE_C' => 18,
            'COU_PART_PAR_EQU_MAX' => 3,
            'COU_NB_EQU_MAX' => 1,
            'COU_NB_PART_MAX' => 2,
        ]);

        // Insert participants: team 1 already has 2 participants
        DB::table('vik_participer')->insert([
            ['INS_ID' => 1, 'COU_NUM' => 200, 'EQU_NUM' => 1],
            ['INS_ID' => 2, 'COU_NUM' => 200, 'EQU_NUM' => 1],
        ]);

        // Team 2 members (the one we're validating) has 2 members, exceeding COU_NB_PART_MAX if both teams count
        DB::table('vik_participer')->insert([
            ['INS_ID' => 3, 'COU_NUM' => 200, 'EQU_NUM' => 2],
            ['INS_ID' => 4, 'COU_NUM' => 200, 'EQU_NUM' => 2],
        ]);

        // Create entries in vik_inscrit for age lookups
        DB::table('vik_inscrit')->insert([
            ['INS_ID' => 3, 'INS_PRENOM' => 'T1', 'INS_NOM' => 'P', 'INS_NAISSANCE' => '2000-01-01'],
            ['INS_ID' => 4, 'INS_PRENOM' => 'T2', 'INS_NOM' => 'P', 'INS_NAISSANCE' => '2000-01-01'],
        ]);

        $controller = new VerifInscriptionController();

        // Validate team 2 (numeroEquipe = 2)
        $res = $controller->validateEquipe(2, 200, false);
        $this->assertFalse($res['ok']);
        $this->assertIsArray($res['messages']);

        // Now test deletion path: deleteIfInvalid = true should remove team participations
        $res2 = $controller->validateEquipe(2, 200, true);
        $this->assertFalse($res2['ok']);
        $this->assertStringContainsString('Équipe supprimée', implode(' | ', $res2['messages']));

        // Ensure participants for team 2 are deleted
        $remaining = DB::table('vik_participer')->where('equ_num', 2)->count();
        $this->assertSame(0, $remaining);

        // cleanup
        $this->tearDownTables();
    }

    /** @test */
    public function validate_age_accepts_exact_boundary_ages()
    {
        $this->createVikInscritTable();

        // Create inscrits at exact boundaries: age A, B, C
        DB::table('vik_inscrit')->insert([
            ['INS_ID' => 100, 'INS_PRENOM' => 'AtA', 'INS_NOM' => 'Limit', 'INS_NAISSANCE' => '2013-01-01'], // age 12 on 2025-01-01
            ['INS_ID' => 101, 'INS_PRENOM' => 'AtB', 'INS_NOM' => 'Limit', 'INS_NAISSANCE' => '2009-01-01'], // age 16 on 2025-01-01
            ['INS_ID' => 102, 'INS_PRENOM' => 'AtC', 'INS_NOM' => 'Limit', 'INS_NAISSANCE' => '2007-01-01'], // age 18 on 2025-01-01
        ]);

        $course = (object)[
            'COU_NUM' => 300,
            'COU_DATE_DEPART' => '2025-01-01',
            'COU_AGE_A' => 12,
            'COU_AGE_B' => 16,
            'COU_AGE_C' => 18,
        ];

        $teamMembers = collect([
            (object)['INS_ID' => 100],
            (object)['INS_ID' => 101],
            (object)['INS_ID' => 102],
        ]);

        $controller = new VerifInscriptionController();
        $res = $controller->validateAge($course, $teamMembers);

        // With one member at C (18), the rule should be satisfied
        $this->assertTrue($res['ok'], 'Age validation should pass with members at exact boundaries');

        $this->tearDownTables();
    }

    /** @test */
    public function validate_age_rejects_when_all_below_minimum()
    {
        $this->createVikInscritTable();

        DB::table('vik_inscrit')->insert([
            ['INS_ID' => 200, 'INS_PRENOM' => 'Young1', 'INS_NOM' => 'Kid', 'INS_NAISSANCE' => '2015-01-01'], // age 9 on 2025-01-01
            ['INS_ID' => 201, 'INS_PRENOM' => 'Young2', 'INS_NOM' => 'Kid', 'INS_NAISSANCE' => '2014-01-01'], // age 10 on 2025-01-01
        ]);

        $course = (object)[
            'COU_NUM' => 301,
            'COU_DATE_DEPART' => '2025-01-01',
            'COU_AGE_A' => 12,
            'COU_AGE_B' => 16,
            'COU_AGE_C' => 18,
        ];

        $teamMembers = collect([
            (object)['INS_ID' => 200],
            (object)['INS_ID' => 201],
        ]);

        $controller = new VerifInscriptionController();
        $res = $controller->validateAge($course, $teamMembers);

        // Both members below minimum -> should fail
        $this->assertFalse($res['ok']);
        $this->assertNotEmpty($res['messages']);
        $this->assertStringContainsString('avoir au moins', implode(' | ', $res['messages']));

        $this->tearDownTables();
    }
}
