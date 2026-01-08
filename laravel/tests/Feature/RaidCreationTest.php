<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VikClub;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class RaidCreationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var array<int, string>
     */
    private array $createdIllustrations = [];

    protected function tearDown(): void
    {
        foreach ($this->createdIllustrations as $filename) {
            $path = public_path('images/'.$filename);
            if (is_string($path) && File::exists($path)) {
                File::delete($path);
            }
        }

        parent::tearDown();
    }

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
    public function club_manager_can_create_raid_with_illustration_and_contact_email()
    {
        $raidNum = $this->uniqueIntId('vik_raid', 'RAID_NUM');

        $manager = User::factory()->create(['INS_NUM_LICENCE' => 'L123']);
        $club = VikClub::create([
            'CLU_NUM' => 1,
            'CLU_NOM' => 'Club Test',
            'INS_ID' => $manager->INS_ID,
        ]);

        $member = User::factory()->create(['INS_NUM_LICENCE' => 'L999']);
        DB::table('vik_adherer')->insert(['INS_ID' => $member->INS_ID, 'CLU_NUM' => $club->CLU_NUM]);

        $this->actingAs($manager, 'web');

        File::ensureDirectoryExists(public_path('images'));
        // Avoid GD dependency in CI/local setups
        $file = UploadedFile::fake()->create('illustration.jpg', 10, 'image/jpeg');

        $response = $this->post('/dashboard/raids', [
            'RAID_NUM' => $raidNum,
            'RAID_NOM' => 'Raid Test',
            'CLU_NUM' => $club->CLU_NUM,
            'INS_ID' => $member->INS_ID,
            'RAID_CONTACT' => 'contact@test.local',
            'RAID_ILLUSTRATION' => $file,
            'RAID_DATE_DEBUT' => now()->addDays(10)->format('Y-m-d'),
            'RAID_DATE_FIN' => now()->addDays(12)->format('Y-m-d'),
            'RAID_DATE_DEBUT_INSCRI' => now()->format('Y-m-d'),
            'RAID_DATE_FIN_INSCRI' => now()->addDays(9)->format('Y-m-d'),
            'RAID_LATITUDE' => 45.12345,
            'RAID_LONGITUDE' => 3.54321,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('vik_raid', ['RAID_NUM' => $raidNum, 'RAID_NOM' => 'Raid Test']);

        $raid = DB::table('vik_raid')->where('RAID_NOM', 'Raid Test')->first();
        $this->assertNotNull($raid->RAID_ILLUSTRATION);
        $filename = $raid->RAID_ILLUSTRATION;
        $this->createdIllustrations[] = $filename;
        $this->assertFileExists(public_path('images/'.$filename));
        $this->assertEqualsWithDelta(45.12345, (float) $raid->RAID_LATITUDE, 0.0001);
        $this->assertEqualsWithDelta(3.54321, (float) $raid->RAID_LONGITUDE, 0.0001);
    }

    /** @test */
    public function cannot_assign_responsible_not_member_of_club()
    {
        $raidNum = $this->uniqueIntId('vik_raid', 'RAID_NUM');

        $manager = User::factory()->create(['INS_NUM_LICENCE' => 'L123']);
        $club = VikClub::create(['CLU_NUM' => 1, 'CLU_NOM' => 'Club Test', 'INS_ID' => $manager->INS_ID]);

        $other = User::factory()->create(['INS_NUM_LICENCE' => 'L111']);

        $this->actingAs($manager, 'web');

        $response = $this->post('/dashboard/raids', [
            'RAID_NUM' => $raidNum,
            'RAID_NOM' => 'Raid Test',
            'CLU_NUM' => $club->CLU_NUM,
            'INS_ID' => $other->INS_ID,
            'RAID_DATE_DEBUT' => now()->addDays(10)->format('Y-m-d'),
            'RAID_DATE_FIN' => now()->addDays(12)->format('Y-m-d'),
            'RAID_DATE_DEBUT_INSCRI' => now()->format('Y-m-d'),
            'RAID_DATE_FIN_INSCRI' => now()->addDays(9)->format('Y-m-d'),
            'RAID_LATITUDE' => 45.12345,
            'RAID_LONGITUDE' => 3.54321,
            'RAID_CONTACT' => '0601020304',
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
        $response->assertSee('Localisation');
        $response->assertSee('id="map"', false);
        $response->assertSee('Ma position');
    }

    /** @test */
    public function can_create_raid_without_optional_fields()
    {
        $raidNum = $this->uniqueIntId('vik_raid', 'RAID_NUM');

        $manager = User::factory()->create(['INS_NUM_LICENCE' => 'L123']);
        $club = VikClub::create(['CLU_NUM' => 1, 'CLU_NOM' => 'Club Test', 'INS_ID' => $manager->INS_ID]);
        $member = User::factory()->create(['INS_NUM_LICENCE' => 'L999']);
        DB::table('vik_adherer')->insert(['INS_ID' => $member->INS_ID, 'CLU_NUM' => $club->CLU_NUM]);

        $this->actingAs($manager, 'web');

        $response = $this->post('/dashboard/raids', [
            'RAID_NUM' => $raidNum,
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
        $this->assertDatabaseHas('vik_raid', ['RAID_NUM' => $raidNum, 'RAID_NOM' => 'Raid Minimal']);
        $raid = DB::table('vik_raid')->where('RAID_NOM', 'Raid Minimal')->first();
        $this->assertNotEmpty($raid->RAID_CONTACT);
        $this->assertNull($raid->RAID_LIEN_SITE_WEB);
        $this->assertNull($raid->RAID_ILLUSTRATION);
        $this->assertEqualsWithDelta(46.1, (float) $raid->RAID_LATITUDE, 0.0001);
        $this->assertEqualsWithDelta(2.2, (float) $raid->RAID_LONGITUDE, 0.0001);
    }

    /** @test */
    public function api_request_can_create_raid_and_returns_json()
    {
        $raidNum = $this->uniqueIntId('vik_raid', 'RAID_NUM');

        $manager = User::factory()->create(['INS_NUM_LICENCE' => 'L123']);
        $club = VikClub::create(['CLU_NUM' => 1, 'CLU_NOM' => 'Club Test', 'INS_ID' => $manager->INS_ID]);
        $member = User::factory()->create(['INS_NUM_LICENCE' => 'L999']);
        DB::table('vik_adherer')->insert(['INS_ID' => $member->INS_ID, 'CLU_NUM' => $club->CLU_NUM]);

        $this->actingAs($manager, 'web');

        $response = $this->postJson('/dashboard/raids', [
            'RAID_NUM' => $raidNum,
            'RAID_NOM' => 'Raid JSON',
            'CLU_NUM' => $club->CLU_NUM,
            'INS_ID' => $member->INS_ID,
            'RAID_CONTACT' => 'contact@test.local',
            'RAID_DATE_DEBUT' => now()->addDays(10)->format('Y-m-d'),
            'RAID_DATE_FIN' => now()->addDays(12)->format('Y-m-d'),
            'RAID_DATE_DEBUT_INSCRI' => now()->format('Y-m-d'),
            'RAID_DATE_FIN_INSCRI' => now()->addDays(9)->format('Y-m-d'),
            'RAID_LATITUDE' => 45.12345,
            'RAID_LONGITUDE' => 3.54321,
        ]);

        $response->assertStatus(201)->assertJsonStructure(['raid' => ['RAID_NUM', 'RAID_NOM', 'CLU_NUM']]);
    }

    /** @test */
    public function email_contact_is_mapped_to_contact_column_if_needed()
    {
        $raidNum = $this->uniqueIntId('vik_raid', 'RAID_NUM');

        $manager = User::factory()->create(['INS_NUM_LICENCE' => 'L123']);
        $club = VikClub::create(['CLU_NUM' => 3, 'CLU_NOM' => 'Club X', 'INS_ID' => $manager->INS_ID]);
        $member = User::factory()->create(['INS_NUM_LICENCE' => 'L999']);
        DB::table('vik_adherer')->insert(['INS_ID' => $member->INS_ID, 'CLU_NUM' => $club->CLU_NUM]);

        $this->actingAs($manager, 'web');

        $response = $this->post('/dashboard/raids', [
            'RAID_NUM' => $raidNum,
            'RAID_NOM' => 'EmailFallback',
            'CLU_NUM' => $club->CLU_NUM,
            'INS_ID' => $member->INS_ID,
            'RAID_CONTACT' => 'fallback@test.local',
            'RAID_DATE_DEBUT' => now()->addDays(5)->format('Y-m-d'),
            'RAID_DATE_FIN' => now()->addDays(6)->format('Y-m-d'),
            'RAID_DATE_DEBUT_INSCRI' => now()->format('Y-m-d'),
            'RAID_DATE_FIN_INSCRI' => now()->addDays(4)->format('Y-m-d'),
            'RAID_LATITUDE' => 48.5,
            'RAID_LONGITUDE' => 2.3,
        ]);

        $response->assertRedirect();
        $raid = DB::table('vik_raid')->where('RAID_NOM', 'EmailFallback')->first();
        $this->assertNotNull($raid);
        // Ensure contact email stored in RAID_CONTACT
        $this->assertStringContainsString('@', $raid->RAID_CONTACT);
    }
}
