<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\VikClub;
use App\Models\VikRace;
use App\Models\VikRaid;
use App\Models\VikEquipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can be created with factory
     */
    public function test_user_can_be_created()
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->INS_ID);
        $this->assertDatabaseHas('VIK_INSCRIT', ['INS_ID' => $user->INS_ID]);
    }

    /**
     * Test getAuthPassword returns hashed password
     */
    public function test_get_auth_password()
    {
        $user = User::factory()->create(['INS_MDP' => bcrypt('test123')]);

        $password = $user->getAuthPassword();
        
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('test123', $password));
    }

    /**
     * Test getEmailForPasswordReset returns email
     */
    public function test_get_email_for_password_reset()
    {
        $user = User::factory()->create(['INS_MAIL' => 'test@example.com']);

        $email = $user->getEmailForPasswordReset();
        
        $this->assertEquals('test@example.com', $email);
    }

    /**
     * Test isMember returns true if user has license
     */
    public function test_is_member_with_license()
    {
        $user = User::factory()->create(['INS_NUM_LICENCE' => 'LIC123']);

        $this->assertTrue($user->isMember());
    }

    /**
     * Test isMember returns false if no license
     */
    public function test_is_member_without_license()
    {
        $user = User::factory()->create(['INS_NUM_LICENCE' => null]);

        $this->assertFalse($user->isMember());
    }

    /**
     * Test isAdherent returns true if user has license
     */
    public function test_is_adherent_with_license()
    {
        $user = User::factory()->create(['INS_NUM_LICENCE' => 'LIC123']);

        $this->assertTrue($user->isAdherent());
    }

    /**
     * Test managesClub returns true if user manages a club
     */
    public function test_manages_club_when_managing_one()
    {
        $user = User::factory()->create();
        VikClub::create([
            'CLU_NOM' => 'Test Club',
            'CLU_ADRESSE' => '123 Main St',
            'CLU_CODE_POSTAL' => '75001',
            'CLU_VILLE' => 'Paris',
            'INS_ID' => $user->INS_ID,
        ]);

        $this->assertTrue($user->managesClub());
    }

    /**
     * Test managesClub returns false if user doesn't manage any club
     */
    public function test_manages_club_when_not_managing_one()
    {
        $user = User::factory()->create();

        $this->assertFalse($user->managesClub());
    }

    /**
     * Test managesRaid returns true if user organizes a raid
     */
    public function test_manages_raid_when_organizing_one()
    {
        $user = User::factory()->create();
        $club = VikClub::create([
            'CLU_NOM' => 'Test Club',
            'CLU_ADRESSE' => '123 Main St',
            'CLU_CODE_POSTAL' => '75001',
            'CLU_VILLE' => 'Paris',
            'INS_ID' => $user->INS_ID,
        ]);

        VikRaid::create([
            'RAID_NUM' => 1,
            'RAID_NOM' => 'Test Raid',
            'CLU_NUM' => $club->CLU_NUM,
            'INS_ID' => $user->INS_ID,
            'RAID_DATE_DEBUT' => now(),
            'RAID_DATE_FIN' => now()->addDays(1),
            'RAID_DATE_DEBUT_INSCRI' => now(),
            'RAID_DATE_FIN_INSCRI' => now()->addDays(1),
            'RAID_CONTACT' => 'Contact',
        ]);

        $this->assertTrue($user->managesRaid());
    }

    /**
     * Test managesCourse returns true if user organizes a course
     */
    public function test_manages_course_when_organizing_one()
    {
        $user = User::factory()->create();

        VikRace::create([
            'COU_NUM' => 1,
            'COU_NOM' => 'Test Course',
            'INS_ID' => $user->INS_ID,
            'TYP_NUM' => 1,
            'COU_DATE_DEPART' => now(),
            'COU_DATE_FIN' => now()->addDays(1),
        ]);

        $this->assertTrue($user->managesCourse());
    }

    /**
     * Test isAdmin returns true if user is admin
     */
    public function test_is_admin_when_admin()
    {
        $user = User::factory()->create(['INS_IS_ADMIN' => 1]);

        $this->assertTrue($user->isAdmin());
    }

    /**
     * Test isAdmin returns false if not admin
     */
    public function test_is_admin_when_not_admin()
    {
        $user = User::factory()->create(['INS_IS_ADMIN' => 0]);

        $this->assertFalse($user->isAdmin());
    }
}
