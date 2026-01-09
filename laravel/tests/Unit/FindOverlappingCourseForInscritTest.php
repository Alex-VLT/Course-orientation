<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\VerifInscription;

class FindOverlappingCourseForInscritTest extends TestCase
{
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

    protected function dropTables()
    {
        if (env('DB_CONNECTION') !== 'sqlite' || env('DB_DATABASE') !== ':memory:') {
            return;
        }
        Schema::dropIfExists('vik_participer');
        Schema::dropIfExists('vik_course');
    }

    /** @test */
    public function returns_null_when_no_overlap()
    {
        $this->createVikCourseTable();
        $this->createVikParticiperTable();

        // Existing course ends before target start
        DB::table('vik_course')->insert([
            'COU_NUM' => 1,
            'COU_NOM' => 'EarlyCourse',
            'COU_DATE_DEPART' => '2025-05-01',
            'COU_DATE_FIN' => '2025-05-09',
        ]);
        DB::table('vik_course')->insert([
            'COU_NUM' => 2,
            'COU_NOM' => 'TargetCourse',
            'COU_DATE_DEPART' => '2025-05-10',
            'COU_DATE_FIN' => '2025-05-12',
        ]);

        DB::table('vik_participer')->insert([
            ['INS_ID' => 42, 'COU_NUM' => 1, 'EQU_NUM' => 1],
        ]);

        $conflict = VerifInscription::findOverlappingCourseForInscrit(42, '2025-05-10', '2025-05-12', 2);
        $this->assertNull($conflict);

        $this->dropTables();
    }

    /** @test */
    public function detects_overlap_when_intervals_intersect()
    {
        $this->createVikCourseTable();
        $this->createVikParticiperTable();

        DB::table('vik_course')->insert([
            'COU_NUM' => 10,
            'COU_NOM' => 'OverlapCourse',
            'COU_DATE_DEPART' => '2025-05-11',
            'COU_DATE_FIN' => '2025-05-13',
        ]);
        DB::table('vik_course')->insert([
            'COU_NUM' => 20,
            'COU_NOM' => 'TargetCourse',
            'COU_DATE_DEPART' => '2025-05-10',
            'COU_DATE_FIN' => '2025-05-12',
        ]);

        DB::table('vik_participer')->insert([
            ['INS_ID' => 7, 'COU_NUM' => 10, 'EQU_NUM' => 1],
        ]);

        $conflict = VerifInscription::findOverlappingCourseForInscrit(7, '2025-05-10', '2025-05-12', 20);
        $this->assertNotNull($conflict);
        $this->assertEquals(10, $conflict->COU_NUM);

        $this->dropTables();
    }

    /** @test */
    public function touching_endpoints_count_as_overlap()
    {
        $this->createVikCourseTable();
        $this->createVikParticiperTable();

        // Existing course ends exactly when target starts
        DB::table('vik_course')->insert([
            'COU_NUM' => 30,
            'COU_NOM' => 'EdgeCourse',
            'COU_DATE_DEPART' => '2025-05-08',
            'COU_DATE_FIN' => '2025-05-10',
        ]);
        DB::table('vik_course')->insert([
            'COU_NUM' => 31,
            'COU_NOM' => 'TargetCourse',
            'COU_DATE_DEPART' => '2025-05-10',
            'COU_DATE_FIN' => '2025-05-12',
        ]);

        DB::table('vik_participer')->insert([
            ['INS_ID' => 99, 'COU_NUM' => 30, 'EQU_NUM' => 2],
        ]);

        $conflict = VerifInscription::findOverlappingCourseForInscrit(99, '2025-05-10', '2025-05-12', 31);
        $this->assertNotNull($conflict);
        $this->assertEquals(30, $conflict->COU_NUM);

        $this->dropTables();
    }

    /** @test */
    public function course_with_missing_dates_is_considered_conflicting()
    {
        $this->createVikCourseTable();
        $this->createVikParticiperTable();

        // Existing course has missing start date
        DB::table('vik_course')->insert([
            'COU_NUM' => 40,
            'COU_NOM' => 'NoDatesCourse',
            'COU_DATE_DEPART' => null,
            'COU_DATE_FIN' => null,
        ]);
        DB::table('vik_course')->insert([
            'COU_NUM' => 41,
            'COU_NOM' => 'TargetCourse',
            'COU_DATE_DEPART' => '2025-06-01',
            'COU_DATE_FIN' => '2025-06-02',
        ]);

        DB::table('vik_participer')->insert([
            ['INS_ID' => 77, 'COU_NUM' => 40, 'EQU_NUM' => 1],
        ]);

        $conflict = VerifInscription::findOverlappingCourseForInscrit(77, '2025-06-01', '2025-06-02', 41);
        $this->assertNotNull($conflict);
        $this->assertEquals(40, $conflict->COU_NUM);

        $this->dropTables();
    }

    /** @test */
    public function exclude_course_num_ignores_excluded_course()
    {
        $this->createVikCourseTable();
        $this->createVikParticiperTable();

        DB::table('vik_course')->insert([
            'COU_NUM' => 50,
            'COU_NOM' => 'SelfCourse',
            'COU_DATE_DEPART' => '2025-07-01',
            'COU_DATE_FIN' => '2025-07-03',
        ]);

        DB::table('vik_participer')->insert([
            ['INS_ID' => 55, 'COU_NUM' => 50, 'EQU_NUM' => 1],
        ]);

        // Excluding the same course must result in null (no other conflicts)
        $conflict = VerifInscription::findOverlappingCourseForInscrit(55, '2025-07-01', '2025-07-03', 50);
        $this->assertNull($conflict);

        $this->dropTables();
    }
}
