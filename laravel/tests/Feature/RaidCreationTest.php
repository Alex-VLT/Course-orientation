<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\VikClub;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class RaidCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function club_manager_can_create_raid_with_illustration_and_contact_email()
    {
        $manager = User::factory()->create(['INS_NUM_LICENCE' => 'L123']);
        $club = VikClub::create([
            'CLU_NUM' => 1,
            'CLU_NOM' => 'Club Test',
            'INS_ID' => $manager->INS_ID,
        ]);

        $member = User::factory()->create(['INS_NUM_LICENCE' => 'L999']);
        DB::table('VIK_ADHERER')->insert(['INS_ID' => $member->INS_ID, 'CLU_NUM' => $club->CLU_NUM]);

        $this->actingAs($manager, 'web');

        Storage::fake('public');
        $file = UploadedFile::fake()->image('illustration.jpg');

        $response = $this->post('/dashboard/raids', [
            'RAID_NOM' => 'Raid Test',
            'CLU_NUM' => $club->CLU_NUM,
            'INS_ID' => $member->INS_ID,
            'RAID_CONTACT_MAIL' => 'contact@test.local',
            'RAID_ILLUSTRATION' => $file,
            'RAID_DATE_DEBUT' => now()->addDays(10)->format('Y-m-d'),
            'RAID_DATE_FIN' => now()->addDays(12)->format('Y-m-d'),
            'RAID_DATE_DEBUT_INSCRI' => now()->format('Y-m-d'),
            'RAID_DATE_FIN_INSCRI' => now()->addDays(9)->format('Y-m-d'),
            'RAID_LATITUDE' => 45.12345,
            'RAID_LONGITUDE' => 3.54321,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('VIK_RAID', ['RAID_NOM' => 'Raid Test', 'RAID_CONTACT_MAIL' => 'contact@test.local']);

        $raid = DB::table('VIK_RAID')->where('RAID_NOM', 'Raid Test')->first();
        $this->assertNotNull($raid->RAID_ILLUSTRATION);
        $filename = $raid->RAID_ILLUSTRATION;
        $this->assertTrue(Storage::disk('public')->exists('images/' . $filename));
        $this->assertEqualsWithDelta(45.12345, (float)$raid->RAID_LATITUDE, 0.0001);
        $this->assertEqualsWithDelta(3.54321, (float)$raid->RAID_LONGITUDE, 0.0001);
    }

    /** @test */
    public function cannot_assign_responsible_not_member_of_club()
    {
        $manager = User::factory()->create(['INS_NUM_LICENCE' => 'L123']);
        $club = VikClub::create(['CLU_NUM' => 1, 'CLU_NOM' => 'Club Test', 'INS_ID' => $manager->INS_ID]);

        $other = User::factory()->create(['INS_NUM_LICENCE' => 'L111']);

        $this->actingAs($manager, 'web');

        $response = $this->post('/dashboard/raids', [
            'RAID_NOM' => 'Raid Test',
            'CLU_NUM' => $club->CLU_NUM,
            'INS_ID' => $other->INS_ID,
        ]);

        $response->assertSessionHasErrors('INS_ID');
    }

    /** @test */
    public function create_page_shows_map_picker_and_fields()
    {
        $manager = User::factory()->create(['INS_NUM_LICENCE' => 'L123']);
        $club = VikClub::create(['CLU_NUM' => 1, 'CLU_NOM' => 'Club Test', 'INS_ID' => $manager->INS_ID]);

        $this->actingAs($manager, 'web');

        $response = $this->get('/dashboard/raids/create');
        $response->assertStatus(200);
        $response->assertSee('Emplacement du raid');
        $response->assertSee('id="map"', false);
        $response->assertSee('Utiliser ma position');
    }

    /** @test */
    public function can_create_raid_without_optional_fields()
    {
        $manager = User::factory()->create(['INS_NUM_LICENCE' => 'L123']);
        $club = VikClub::create(['CLU_NUM' => 1, 'CLU_NOM' => 'Club Test', 'INS_ID' => $manager->INS_ID]);
        $member = User::factory()->create(['INS_NUM_LICENCE' => 'L999']);
        DB::table('VIK_ADHERER')->insert(['INS_ID' => $member->INS_ID, 'CLU_NUM' => $club->CLU_NUM]);

        $this->actingAs($manager, 'web');

        $response = $this->post('/dashboard/raids', [
            'RAID_NOM' => 'Raid Minimal',
            'CLU_NUM' => $club->CLU_NUM,
            'INS_ID' => $member->INS_ID,
            'RAID_DATE_DEBUT' => now()->addDays(10)->format('Y-m-d'),
            'RAID_DATE_FIN' => now()->addDays(11)->format('Y-m-d'),
            'RAID_DATE_DEBUT_INSCRI' => now()->format('Y-m-d'),
            'RAID_DATE_FIN_INSCRI' => now()->addDays(9)->format('Y-m-d'),
            'RAID_LATITUDE' => 46.1,
            'RAID_LONGITUDE' => 2.2,
            'RAID_CONTACT' => '0601020304',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('VIK_RAID', ['RAID_NOM' => 'Raid Minimal']);
        $raid = DB::table('VIK_RAID')->where('RAID_NOM', 'Raid Minimal')->first();
        $this->assertNull($raid->RAID_CONTACT_MAIL);
        $this->assertNull($raid->RAID_LIEN_SITE_WEB);
        $this->assertNull($raid->RAID_ILLUSTRATION);
        $this->assertEqualsWithDelta(46.1, (float)$raid->RAID_LATITUDE, 0.0001);
        $this->assertEqualsWithDelta(2.2, (float)$raid->RAID_LONGITUDE, 0.0001);
    }

    /** @test */
    public function api_request_can_create_raid_and_returns_json()
    {
        $manager = User::factory()->create(['INS_NUM_LICENCE' => 'L123']);
        $club = VikClub::create(['CLU_NUM' => 1, 'CLU_NOM' => 'Club Test', 'INS_ID' => $manager->INS_ID]);
        $member = User::factory()->create(['INS_NUM_LICENCE' => 'L999']);
        DB::table('VIK_ADHERER')->insert(['INS_ID' => $member->INS_ID, 'CLU_NUM' => $club->CLU_NUM]);

        $this->actingAs($manager, 'web');

        $response = $this->postJson('/dashboard/raids', [
            'RAID_NOM' => 'Raid JSON',
            'CLU_NUM' => $club->CLU_NUM,
            'INS_ID' => $member->INS_ID,
            'RAID_CONTACT_MAIL' => 'contact@test.local',
        ]);

        $response->assertStatus(201)->assertJsonStructure(['raid' => ['RAID_NUM', 'RAID_NOM', 'CLU_NUM']]);
    }

    /** @test */
    public function email_contact_is_mapped_to_contact_column_if_needed()
    {
        $manager = User::factory()->create(['INS_NUM_LICENCE' => 'L123']);
        $club = VikClub::create(['CLU_NUM' => 3, 'CLU_NOM' => 'Club X', 'INS_ID' => $manager->INS_ID]);
        $member = User::factory()->create(['INS_NUM_LICENCE' => 'L999']);
        DB::table('VIK_ADHERER')->insert(['INS_ID' => $member->INS_ID, 'CLU_NUM' => $club->CLU_NUM]);

        $this->actingAs($manager, 'web');

        $response = $this->post('/dashboard/raids', [
            'RAID_NOM' => 'EmailFallback',
            'CLU_NUM' => $club->CLU_NUM,
            'INS_ID' => $member->INS_ID,
            'RAID_CONTACT_MAIL' => 'fallback@test.local',
            'RAID_DATE_DEBUT' => now()->addDays(5)->format('Y-m-d'),
            'RAID_DATE_FIN' => now()->addDays(6)->format('Y-m-d'),
            'RAID_DATE_DEBUT_INSCRI' => now()->format('Y-m-d'),
            'RAID_DATE_FIN_INSCRI' => now()->addDays(4)->format('Y-m-d'),
            'RAID_LATITUDE' => 48.5,
            'RAID_LONGITUDE' => 2.3,
        ]);

        $response->assertRedirect();
        $raid = DB::table('VIK_RAID')->where('RAID_NOM', 'EmailFallback')->first();
        $this->assertNotNull($raid);
        // DB may not have RAID_CONTACT_MAIL column; ensure RAID_CONTACT contains the email
        $this->assertStringContainsString('@', $raid->RAID_CONTACT);
    }
}
