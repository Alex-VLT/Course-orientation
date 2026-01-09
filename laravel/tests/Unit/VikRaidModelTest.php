<?php

namespace Tests\Unit;

use App\Models\VikRaid;
use App\Models\VikClub;
use App\Models\VikRace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VikRaidModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test raid can be created
     */
    public function test_raid_can_be_created()
    {
        $raid = VikRaid::create([
            'RAID_NUM' => 1,
            'RAID_NOM' => 'Test Raid',
            'CLU_NUM' => 1,
            'INS_ID' => 1,
            'RAID_DATE_DEBUT' => now(),
            'RAID_DATE_FIN' => now()->addDays(1),
            'RAID_DATE_DEBUT_INSCRI' => now(),
            'RAID_DATE_FIN_INSCRI' => now()->addDays(1),
            'RAID_CONTACT' => 'Contact Info',
        ]);

        $this->assertNotNull($raid->RAID_NUM);
        $this->assertEquals('Test Raid', $raid->RAID_NOM);
    }

    /**
     * Test raid has many courses
     */
    public function test_raid_has_many_courses()
    {
        $raid = VikRaid::create([
            'RAID_NUM' => 1,
            'RAID_NOM' => 'Test Raid',
            'CLU_NUM' => 1,
            'INS_ID' => 1,
            'RAID_DATE_DEBUT' => now(),
            'RAID_DATE_FIN' => now()->addDays(1),
            'RAID_DATE_DEBUT_INSCRI' => now(),
            'RAID_DATE_FIN_INSCRI' => now()->addDays(1),
            'RAID_CONTACT' => 'Contact',
        ]);

        VikRace::create([
            'COU_NUM' => 1,
            'COU_NOM' => 'Test Course',
            'INS_ID' => 1,
            'TYP_NUM' => 1,
            'RAID_NUM' => $raid->RAID_NUM,
            'COU_DATE_DEPART' => now(),
            'COU_DATE_FIN' => now()->addDays(1),
        ]);

        $this->assertEquals(1, $raid->courses()->count());
    }

    /**
     * Test raid belongs to club
     */
    public function test_raid_belongs_to_club()
    {
        $club = VikClub::create([
            'CLU_NOM' => 'Test Club',
            'CLU_ADRESSE' => '123 Main St',
            'CLU_CODE_POSTAL' => '75001',
            'CLU_VILLE' => 'Paris',
            'INS_ID' => 1,
        ]);

        $raid = VikRaid::create([
            'RAID_NUM' => 1,
            'RAID_NOM' => 'Test Raid',
            'CLU_NUM' => $club->CLU_NUM,
            'INS_ID' => 1,
            'RAID_DATE_DEBUT' => now(),
            'RAID_DATE_FIN' => now()->addDays(1),
            'RAID_DATE_DEBUT_INSCRI' => now(),
            'RAID_DATE_FIN_INSCRI' => now()->addDays(1),
            'RAID_CONTACT' => 'Contact',
        ]);

        $this->assertEquals($club->CLU_NUM, $raid->club->CLU_NUM);
    }

    /**
     * Test raid belongs to organizer user
     */
    public function test_raid_belongs_to_organizer()
    {
        $user = User::factory()->create();

        $raid = VikRaid::create([
            'RAID_NUM' => 1,
            'RAID_NOM' => 'Test Raid',
            'CLU_NUM' => 1,
            'INS_ID' => $user->INS_ID,
            'RAID_DATE_DEBUT' => now(),
            'RAID_DATE_FIN' => now()->addDays(1),
            'RAID_DATE_DEBUT_INSCRI' => now(),
            'RAID_DATE_FIN_INSCRI' => now()->addDays(1),
            'RAID_CONTACT' => 'Contact',
        ]);

        $this->assertEquals($user->INS_ID, $raid->responsable->INS_ID);
    }

    /**
     * Test raid table name
     */
    public function test_raid_table_name()
    {
        $raid = new VikRaid();
        $this->assertEquals('VIK_RAID', $raid->getTable());
    }

    /**
     * Test raid primary key
     */
    public function test_raid_primary_key()
    {
        $raid = new VikRaid();
        $this->assertEquals('RAID_NUM', $raid->getKeyName());
    }
}
