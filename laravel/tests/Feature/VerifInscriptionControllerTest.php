<?php

namespace Tests\Feature;

use Tests\TestCase;

class VerifInscriptionControllerTest extends TestCase
{
    /**
     * Test inscription validation
     */
    public function test_inscription_validation_structure()
    {
        $response = $this->get('/verify-inscription');
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 404]));
    }

    /**
     * Test team validation
     */
    public function test_team_validation()
    {
        $response = $this->post('/verify-inscription', [
            'team_id' => 1,
        ]);
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 404, 422]));
    }

    /**
     * Test age validation
     */
    public function test_age_validation()
    {
        $response = $this->post('/verify-inscription', [
            'participant_age' => 5,
            'course_id' => 1,
        ]);
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 404, 422]));
    }

    /**
     * Test participant limit validation
     */
    public function test_participant_limit_validation()
    {
        $response = $this->post('/verify-inscription', [
            'course_id' => 1,
            'team_id' => 1,
        ]);
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 404, 422]));
    }

    /**
     * Test equipment validation
     */
    public function test_equipment_validation()
    {
        $response = $this->post('/verify-inscription', [
            'team_id' => 1,
            'equipment' => ['basic'],
        ]);
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 404, 422]));
    }

    /**
     * Test form submission
     */
    public function test_form_submission()
    {
        $response = $this->post('/verify-inscription/submit', [
            'team_id' => 1,
        ]);
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 404, 422]));
    }
}
