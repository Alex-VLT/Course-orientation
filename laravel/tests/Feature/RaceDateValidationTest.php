<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VikRaid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\CreatesVikSchema;
use Tests\TestCase;

class RaceDateValidationTest extends TestCase
{
    use CreatesVikSchema, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createVikSchema();

        // Create a course type
        DB::table('vik_type_course')->insert([
            'TYP_NUM' => 1,
            'TYP_LIBELLE' => 'Trail',
        ]);

        // Create a club
        DB::table('vik_club')->insert([
            'CLU_NUM' => 1,
            'CLU_NOM' => 'Test Club',
        ]);
    }

    public function test_course_can_start_and_end_on_raid_start_date(): void
    {
        $user = User::factory()->create([
            'INS_IS_ADMIN' => 1,
            'INS_NUM_LICENCE' => 'LIC12345',
        ]);

        // Make user an adherent of the club
        DB::table('vik_adherer')->insert([
            'INS_ID' => $user->INS_ID,
            'CLU_NUM' => 1,
        ]);

        $raid = VikRaid::create([
            'RAID_NUM' => 1,
            'RAID_NOM' => 'Test Raid',
            'RAID_DATE_DEBUT' => '2026-01-24',
            'RAID_DATE_FIN' => '2026-01-25',
            'INS_ID' => $user->INS_ID,
            'CLU_NUM' => 1,
        ]);

        $response = $this->actingAs($user)->post(route('race.store', $raid->RAID_NUM), [
            'COU_NOM' => 'Course du 24',
            'TYP_NUM' => 1,
            'COU_DATE_DEPART' => '2026-01-24',
            'COU_DATE_FIN' => '2026-01-24',
            'COU_DUREE' => 120,
            'COU_DIFFICULTE' => 'Facile',
            'COU_NB_PART_MIN' => 10,
            'COU_NB_PART_MAX' => 100,
            'COU_NB_EQU_MIN' => 2,
            'COU_NB_EQU_MAX' => 20,
            'COU_PART_PAR_EQU_MAX' => 5,
            'COU_AGE_A' => 18,
            'COU_AGE_B' => 30,
            'COU_AGE_C' => 50,
            'INS_ID' => $user->INS_ID,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('vik_course', [
            'COU_NOM' => 'Course du 24',
        ]);
    }

    public function test_course_can_start_and_end_on_raid_end_date(): void
    {
        $user = User::factory()->create([
            'INS_IS_ADMIN' => 1,
            'INS_NUM_LICENCE' => 'LIC12346',
        ]);

        // Make user an adherent of the club
        DB::table('vik_adherer')->insert([
            'INS_ID' => $user->INS_ID,
            'CLU_NUM' => 1,
        ]);

        $raid = VikRaid::create([
            'RAID_NUM' => 2,
            'RAID_NOM' => 'Test Raid',
            'RAID_DATE_DEBUT' => '2026-01-24',
            'RAID_DATE_FIN' => '2026-01-25',
            'INS_ID' => $user->INS_ID,
            'CLU_NUM' => 1,
        ]);

        $response = $this->actingAs($user)->post(route('race.store', $raid->RAID_NUM), [
            'COU_NOM' => 'Course du 25',
            'TYP_NUM' => 1,
            'COU_DATE_DEPART' => '2026-01-25',
            'COU_DATE_FIN' => '2026-01-25',
            'COU_DUREE' => 120,
            'COU_DIFFICULTE' => 'Facile',
            'COU_NB_PART_MIN' => 10,
            'COU_NB_PART_MAX' => 100,
            'COU_NB_EQU_MIN' => 2,
            'COU_NB_EQU_MAX' => 20,
            'COU_PART_PAR_EQU_MAX' => 5,
            'COU_AGE_A' => 18,
            'COU_AGE_B' => 30,
            'COU_AGE_C' => 50,
            'INS_ID' => $user->INS_ID,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('vik_course', [
            'COU_NOM' => 'Course du 25',
        ]);
    }

    public function test_course_cannot_start_before_raid_start(): void
    {
        $user = User::factory()->create([
            'INS_IS_ADMIN' => 1,
            'INS_NUM_LICENCE' => 'LIC12347',
        ]);

        // Make user an adherent of the club
        DB::table('vik_adherer')->insert([
            'INS_ID' => $user->INS_ID,
            'CLU_NUM' => 1,
        ]);

        $raid = VikRaid::create([
            'RAID_NUM' => 3,
            'RAID_NOM' => 'Test Raid',
            'RAID_DATE_DEBUT' => '2026-01-24',
            'RAID_DATE_FIN' => '2026-01-25',
            'INS_ID' => $user->INS_ID,
            'CLU_NUM' => 1,
        ]);

        $response = $this->actingAs($user)->post(route('race.store', $raid->RAID_NUM), [
            'COU_NOM' => 'Course Invalide',
            'TYP_NUM' => 1,
            'COU_DATE_DEPART' => '2026-01-23',
            'COU_DATE_FIN' => '2026-01-24',
            'COU_DUREE' => 120,
            'COU_DIFFICULTE' => 'Facile',
            'COU_NB_PART_MIN' => 10,
            'COU_NB_PART_MAX' => 100,
            'COU_NB_EQU_MIN' => 2,
            'COU_NB_EQU_MAX' => 20,
            'COU_PART_PAR_EQU_MAX' => 5,
            'COU_AGE_A' => 18,
            'COU_AGE_B' => 30,
            'COU_AGE_C' => 50,
            'INS_ID' => $user->INS_ID,
        ]);

        $response->assertSessionHasErrors('COU_DATE_DEPART');
    }

    public function test_course_cannot_end_after_raid_end(): void
    {
        $user = User::factory()->create([
            'INS_IS_ADMIN' => 1,
            'INS_NUM_LICENCE' => 'LIC12348',
        ]);

        // Make user an adherent of the club
        DB::table('vik_adherer')->insert([
            'INS_ID' => $user->INS_ID,
            'CLU_NUM' => 1,
        ]);

        $raid = VikRaid::create([
            'RAID_NUM' => 4,
            'RAID_NOM' => 'Test Raid',
            'RAID_DATE_DEBUT' => '2026-01-24',
            'RAID_DATE_FIN' => '2026-01-25',
            'INS_ID' => $user->INS_ID,
            'CLU_NUM' => 1,
        ]);

        $response = $this->actingAs($user)->post(route('race.store', $raid->RAID_NUM), [
            'COU_NOM' => 'Course Invalide',
            'TYP_NUM' => 1,
            'COU_DATE_DEPART' => '2026-01-25',
            'COU_DATE_FIN' => '2026-01-26',
            'COU_DUREE' => 120,
            'COU_DIFFICULTE' => 'Facile',
            'COU_NB_PART_MIN' => 10,
            'COU_NB_PART_MAX' => 100,
            'COU_NB_EQU_MIN' => 2,
            'COU_NB_EQU_MAX' => 20,
            'COU_PART_PAR_EQU_MAX' => 5,
            'COU_AGE_A' => 18,
            'COU_AGE_B' => 30,
            'COU_AGE_C' => 50,
            'INS_ID' => $user->INS_ID,
        ]);

        $response->assertSessionHasErrors('COU_DATE_FIN');
    }
}
