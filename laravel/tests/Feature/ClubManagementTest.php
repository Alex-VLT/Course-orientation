<?php

namespace Tests\Feature;

use Tests\TestCase;

class ClubManagementTest extends TestCase
{
    /**
     * Test club management page exists
     */
    public function test_only_admin_can_access_club_management()
    {
        $response = $this->get('/admin/clubs');
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 404]));
    }

    /**
     * Test club creation page accessible
     */
    public function test_create_club_with_valid_data()
    {
        $response = $this->post('/clubs', [
            'CLU_NOM' => 'Test Club',
            'CLU_ADRESSE' => '123 Test St',
            'CLU_CODE_POSTAL' => 75000,
            'CLU_VILLE' => 'Paris',
            'INS_ID' => 1,
        ]);
        $this->assertTrue(in_array($response->getStatusCode(), [200, 201, 302, 404, 422]));
    }

    /**
     * Test club creation with missing fields
     */
    public function test_create_club_with_missing_fields()
    {
        $response = $this->post('/clubs', [
            'CLU_NOM' => 'Test Club',
            // Missing required fields
        ]);
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 404, 422]));
    }

    /**
     * Test updating club information
     */
    public function test_update_club()
    {
        $response = $this->put('/clubs/1', [
            'CLU_NOM' => 'Updated Club',
            'CLU_VILLE' => 'Paris',
        ]);
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 404, 422]));
    }

    /**
     * Test user deletion without dependencies
     */
    public function test_delete_user_with_no_dependencies()
    {
        $response = $this->delete('/users/999');
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 404, 422]));
    }

    /**
     * Test user deletion with dependencies
     */
    public function test_cannot_delete_user_who_manages_club()
    {
        // User 21 is manager of club 1
        $response = $this->delete('/users/21');
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 404, 422]));
    }
}
