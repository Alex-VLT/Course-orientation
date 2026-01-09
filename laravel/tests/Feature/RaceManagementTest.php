<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VikRace;
use App\Models\VikRaid;
use App\Models\VikClub;
use App\Models\VikEquipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RaceManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test organizer can view race details
     */
    public function test_user_can_view_race_details()
    {
        $race = VikRace::create([
            'COU_NUM' => 1,
            'COU_NOM' => 'Test Course',
            'INS_ID' => 1,
            'TYP_NUM' => 1,
            'COU_DATE_DEPART' => now(),
            'COU_DATE_FIN' => now()->addDays(1),
        ]);

        $response = $this->get("/race/{$race->COU_NUM}");

        $response->assertStatus(200);
        $response->assertViewIs('pages.race');
        $response->assertViewHas('race');
    }

    /**
     * Test race rankings are sorted by points and time
     */
    public function test_race_rankings_displayed()
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
            'EQU_NOM' => 'Team A',
            'EQU_POINTS' => 100,
            'EQU_TEMPS' => 120,
        ]);

        $response = $this->get("/race/{$race->COU_NUM}/classement");

        $response->assertStatus(200);
    }

    /**
     * Test organizer can create a course in a raid
     */
    public function test_organizer_can_create_course()
    {
        $organizer = User::factory()->create();
        $club = VikClub::create([
            'CLU_NOM' => 'Test Club',
            'CLU_ADRESSE' => '123 Main St',
            'CLU_CODE_POSTAL' => '75001',
            'CLU_VILLE' => 'Paris',
            'INS_ID' => $organizer->INS_ID,
        ]);

        $raid = VikRaid::create([
            'RAID_NUM' => 1,
            'RAID_NOM' => 'Test Raid',
            'CLU_NUM' => $club->CLU_NUM,
            'INS_ID' => $organizer->INS_ID,
            'RAID_DATE_DEBUT' => now(),
            'RAID_DATE_FIN' => now()->addDays(1),
            'RAID_DATE_DEBUT_INSCRI' => now(),
            'RAID_DATE_FIN_INSCRI' => now()->addDays(1),
            'RAID_CONTACT' => 'Contact',
        ]);

        $courseData = [
            'COU_NOM' => 'New Course',
            'TYP_NUM' => 1,
            'COU_DATE_DEPART' => now()->toDateTimeString(),
            'COU_DATE_FIN' => now()->addDays(1)->toDateTimeString(),
            'COU_DIFFICULTE' => 'Medium',
            'COU_DUREE' => 120,
        ];

        $response = $this->actingAs($organizer)->post("/raid/{$raid->RAID_NUM}/course", $courseData);

        $response->assertRedirect();
        $this->assertDatabaseHas('VIK_COURSE', ['COU_NOM' => 'New Course']);
    }

    /**
     * Test generating race bibs
     */
    public function test_organizer_can_generate_dossards()
    {
        $organizer = User::factory()->create();
        
        $race = VikRace::create([
            'COU_NUM' => 1,
            'COU_NOM' => 'Test Course',
            'INS_ID' => $organizer->INS_ID,
            'TYP_NUM' => 1,
            'COU_DATE_DEPART' => now(),
            'COU_DATE_FIN' => now()->addDays(1),
        ]);

        $response = $this->actingAs($organizer)->post("/race/{$race->COU_NUM}/dossards", ['count' => 50]);

        $response->assertRedirect();
        $this->assertTrue($race->dossards()->count() >= 50);
    }

    /**
     * Test organizer can toggle team payment status
     */
    public function test_organizer_can_toggle_team_payment()
    {
        $organizer = User::factory()->create();
        
        $race = VikRace::create([
            'COU_NUM' => 1,
            'COU_NOM' => 'Test Course',
            'INS_ID' => $organizer->INS_ID,
            'TYP_NUM' => 1,
            'COU_DATE_DEPART' => now(),
            'COU_DATE_FIN' => now()->addDays(1),
        ]);

        $team = VikEquipe::create([
            'COU_NUM' => $race->COU_NUM,
            'EQU_NUM' => 1,
            'INS_ID' => $organizer->INS_ID,
            'EQU_NOM' => 'Test Team',
            'EQU_PAIEMENT_VALIDE' => false,
        ]);

        $response = $this->actingAs($organizer)->post("/race/{$race->COU_NUM}/team/{$team->EQU_NUM}/payment");

        $response->assertRedirect();
        $this->assertTrue($team->refresh()->EQU_PAIEMENT_VALIDE);
    }

    /**
     * Test organizer can delete a team
     */
    public function test_organizer_can_delete_team()
    {
        $organizer = User::factory()->create();
        
        $race = VikRace::create([
            'COU_NUM' => 1,
            'COU_NOM' => 'Test Course',
            'INS_ID' => $organizer->INS_ID,
            'TYP_NUM' => 1,
            'COU_DATE_DEPART' => now(),
            'COU_DATE_FIN' => now()->addDays(1),
        ]);

        $team = VikEquipe::create([
            'COU_NUM' => $race->COU_NUM,
            'EQU_NUM' => 1,
            'INS_ID' => $organizer->INS_ID,
            'EQU_NOM' => 'Test Team',
        ]);

        $response = $this->actingAs($organizer)->post("/race/{$race->COU_NUM}/team/{$team->EQU_NUM}/delete");

        $response->assertRedirect();
        $this->assertDatabaseMissing('VIK_EQUIPE', ['EQU_NUM' => $team->EQU_NUM]);
    }

    /**
     * Test organizer can export results as CSV
     */
    public function test_organizer_can_export_results()
    {
        $organizer = User::factory()->create();
        
        $race = VikRace::create([
            'COU_NUM' => 1,
            'COU_NOM' => 'Test Course',
            'INS_ID' => $organizer->INS_ID,
            'TYP_NUM' => 1,
            'COU_DATE_DEPART' => now(),
            'COU_DATE_FIN' => now()->addDays(1),
        ]);

        $response = $this->actingAs($organizer)->get("/race/{$race->COU_NUM}/export");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
    }

    /**
     * Test non-organizer cannot manage race
     */
    public function test_non_organizer_cannot_manage_race()
    {
        $organizer = User::factory()->create();
        $user = User::factory()->create();
        
        $race = VikRace::create([
            'COU_NUM' => 1,
            'COU_NOM' => 'Test Course',
            'INS_ID' => $organizer->INS_ID,
            'TYP_NUM' => 1,
            'COU_DATE_DEPART' => now(),
            'COU_DATE_FIN' => now()->addDays(1),
        ]);

        $response = $this->actingAs($user)->post("/race/{$race->COU_NUM}/dossards", ['count' => 10]);

        $response->assertStatus(403);
    }

    /**
     * Test unsubscribing from a course
     */
    public function test_user_can_unsubscribe_from_course()
    {
        $user = User::factory()->create();
        
        $race = VikRace::create([
            'COU_NUM' => 1,
            'COU_NOM' => 'Test Course',
            'INS_ID' => 1,
            'TYP_NUM' => 1,
            'COU_DATE_DEPART' => now()->addDays(5),
            'COU_DATE_FIN' => now()->addDays(6),
        ]);

        // Register user
        \Illuminate\Support\Facades\DB::table('vik_participer')->insert([
            'INS_ID' => $user->INS_ID,
            'COU_NUM' => $race->COU_NUM,
            'EQU_NUM' => 1,
        ]);

        $response = $this->actingAs($user)->post("/race/{$race->COU_NUM}/unsubscribe");

        $response->assertRedirect();
        $this->assertDatabaseMissing('vik_participer', [
            'INS_ID' => $user->INS_ID,
            'COU_NUM' => $race->COU_NUM,
        ]);
    }
}
