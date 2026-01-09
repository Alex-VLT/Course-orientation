<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VikClub;
use App\Models\VikRaid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test dashboard displays for club manager
     */
    public function test_dashboard_displays_for_club_manager()
    {
        $manager = User::factory()->create();
        $club = VikClub::create([
            'CLU_NOM' => 'Test Club',
            'CLU_ADRESSE' => '123 Main St',
            'CLU_CODE_POSTAL' => '75001',
            'CLU_VILLE' => 'Paris',
            'INS_ID' => $manager->INS_ID,
        ]);

        $response = $this->actingAs($manager)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('pages.dashboard');
        $response->assertViewHas('managesClub', true);
        $response->assertViewHas('club');
    }

    /**
     * Test dashboard displays empty for non-club managers
     */
    public function test_dashboard_empty_for_non_managers()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('managesClub', false);
    }

    /**
     * Test manager can see club members
     */
    public function test_manager_can_see_club_members()
    {
        $manager = User::factory()->create();
        $member = User::factory()->create();
        
        $club = VikClub::create([
            'CLU_NOM' => 'Test Club',
            'CLU_ADRESSE' => '123 Main St',
            'CLU_CODE_POSTAL' => '75001',
            'CLU_VILLE' => 'Paris',
            'INS_ID' => $manager->INS_ID,
        ]);

        // Add member to club
        $this->actingAs($manager)->post("/dashboard/members/{$member->INS_ID}/add");

        $response = $this->actingAs($manager)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('clubMembers');
    }

    /**
     * Test manager can remove a member from club
     */
    public function test_manager_can_remove_member_from_club()
    {
        $manager = User::factory()->create();
        $member = User::factory()->create();
        
        $club = VikClub::create([
            'CLU_NOM' => 'Test Club',
            'CLU_ADRESSE' => '123 Main St',
            'CLU_CODE_POSTAL' => '75001',
            'CLU_VILLE' => 'Paris',
            'INS_ID' => $manager->INS_ID,
        ]);

        // Add member
        \Illuminate\Support\Facades\DB::table('VIK_ADHERER')->insert([
            'INS_ID' => $member->INS_ID,
            'CLU_NUM' => $club->CLU_NUM,
        ]);

        $response = $this->actingAs($manager)->post("/dashboard/members/{$member->INS_ID}/remove");

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseMissing('VIK_ADHERER', [
            'INS_ID' => $member->INS_ID,
            'CLU_NUM' => $club->CLU_NUM,
        ]);
    }

    /**
     * Test manager cannot remove themselves
     */
    public function test_manager_cannot_remove_themselves()
    {
        $manager = User::factory()->create();
        
        VikClub::create([
            'CLU_NOM' => 'Test Club',
            'CLU_ADRESSE' => '123 Main St',
            'CLU_CODE_POSTAL' => '75001',
            'CLU_VILLE' => 'Paris',
            'INS_ID' => $manager->INS_ID,
        ]);

        $response = $this->actingAs($manager)->post("/dashboard/members/{$manager->INS_ID}/remove");

        $response->assertSessionHasErrors();
    }

    /**
     * Test dashboard shows raids for managed club
     */
    public function test_dashboard_shows_club_raids()
    {
        $manager = User::factory()->create();
        $club = VikClub::create([
            'CLU_NOM' => 'Test Club',
            'CLU_ADRESSE' => '123 Main St',
            'CLU_CODE_POSTAL' => '75001',
            'CLU_VILLE' => 'Paris',
            'INS_ID' => $manager->INS_ID,
        ]);

        VikRaid::create([
            'RAID_NUM' => 1,
            'RAID_NOM' => 'Test Raid',
            'CLU_NUM' => $club->CLU_NUM,
            'INS_ID' => $manager->INS_ID,
            'RAID_DATE_DEBUT' => now(),
            'RAID_DATE_FIN' => now()->addDays(1),
            'RAID_DATE_DEBUT_INSCRI' => now(),
            'RAID_DATE_FIN_INSCRI' => now()->addDays(1),
            'RAID_CONTACT' => 'Contact',
        ]);

        $response = $this->actingAs($manager)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('raids');
    }
}
