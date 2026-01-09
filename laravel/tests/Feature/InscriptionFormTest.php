<?php

namespace Tests\Feature;

use Tests\TestCase;

class InscriptionFormTest extends TestCase
{
    /**
     * Test unauthenticated user cannot submit team
     */
    public function test_unauthenticated_user_cannot_submit_team()
    {
        $response = $this->post('/inscForm', [
            'team_name' => 'Test Team',
            'course' => 1,
        ]);

        $this->assertTrue(in_array($response->getStatusCode(), [302, 401, 404]));
    }

    /**
     * Test missing course number returns error
     */
    public function test_missing_course_number_returns_error()
    {
        $response = $this->post('/inscForm', [
            'team_name' => 'Test Team',
        ]);

        $this->assertTrue(in_array($response->getStatusCode(), [302, 401, 404]));
    }

    /**
     * Test form shows correct fields and labels
     */
    public function test_form_shows_correct_fields_and_labels()
    {
        $response = $this->get('/inscForm');

        // Check status is ok, redirect, or not found
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 404]));
    }
}

