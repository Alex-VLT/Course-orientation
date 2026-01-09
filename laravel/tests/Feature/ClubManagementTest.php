<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VikClub;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClubManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that only admin users can access club management page
     */
    public function test_only_admin_can_access_club_management()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/clubs');
        
        // Non-admin user should see the main page
        $response->assertViewIs('pages.mainPage');
    }

    /**
     * Test creating a new club with valid data
     */
    public function test_create_club_with_valid_data()
    {
        $manager = User::factory()->create();
        
        $clubData = [
            'CLU_NOM' => 'Test Club',
            'CLU_ADRESSE' => '123 Main Street',
            'CLU_CODE_POSTAL' => '75001',
            'CLU_VILLE' => 'Paris',
            'INS_ID' => $manager->INS_ID,
        ];

        $response = $this->actingAs($manager)->postJson('/clubs', $clubData);

        $response->assertStatus(201);
        $response->assertJsonStructure(['success', 'club']);
        $this->assertDatabaseHas('VIK_CLUB', ['CLU_NOM' => 'Test Club']);
    }

    /**
     * Test creating a club with missing required fields
     */
    public function test_create_club_with_missing_fields()
    {
        $manager = User::factory()->create();
        
        $clubData = [
            'CLU_NOM' => 'Test Club',
            // Missing other required fields
        ];

        $response = $this->actingAs($manager)->postJson('/clubs', $clubData);

        $response->assertStatus(422);
    }

    /**
     * Test updating an existing club
     */
    public function test_update_club()
    {
        $manager = User::factory()->create();
        $club = VikClub::create([
            'CLU_NOM' => 'Original Club',
            'CLU_ADRESSE' => '123 Main St',
            'CLU_CODE_POSTAL' => '75001',
            'CLU_VILLE' => 'Paris',
            'INS_ID' => $manager->INS_ID,
        ]);

        $updateData = [
            'CLU_NOM' => 'Updated Club',
            'CLU_ADRESSE' => '456 New Street',
            'CLU_CODE_POSTAL' => '75002',
            'CLU_VILLE' => 'Lyon',
            'INS_ID' => $manager->INS_ID,
        ];

        $response = $this->actingAs($manager)->patchJson("/clubs/{$club->CLU_NUM}", $updateData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('VIK_CLUB', ['CLU_NOM' => 'Updated Club']);
    }

    /**
     * Test deleting a user with no dependencies
     */
    public function test_delete_user_with_no_dependencies()
    {
        $user = User::factory()->create();
        
        $response = $this->deleteJson("/inscrit/{$user->INS_ID}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);
        $this->assertDatabaseMissing('VIK_INSCRIT', ['INS_ID' => $user->INS_ID]);
    }

    /**
     * Test cannot delete user who owns a club
     */
    public function test_cannot_delete_user_who_manages_club()
    {
        $manager = User::factory()->create();
        VikClub::create([
            'CLU_NOM' => 'Managed Club',
            'CLU_ADRESSE' => '123 Main St',
            'CLU_CODE_POSTAL' => '75001',
            'CLU_VILLE' => 'Paris',
            'INS_ID' => $manager->INS_ID,
        ]);

        $response = $this->deleteJson("/inscrit/{$manager->INS_ID}");

        $response->assertStatus(409);
        $response->assertJsonFragment(['success' => false]);
        $this->assertDatabaseHas('VIK_INSCRIT', ['INS_ID' => $manager->INS_ID]);
    }
}
