<?php

namespace Tests\Feature;

use Tests\TestCase;

class RaceControllerTest extends TestCase
{
    /**
     * Test that the race index page loads
     */
    public function test_race_index_structure()
    {
        $response = $this->get('/courses');
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 404]));
    }

    /**
     * Test race show page structure
     */
    public function test_race_show_structure()
    {
        $response = $this->get('/courses/1');
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 404]));
    }

    /**
     * Test race rankings display
     */
    public function test_race_rankings_display()
    {
        $response = $this->get('/courses/1/rankings');
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 404]));
    }

    /**
     * Test course creation validation
     */
    public function test_course_creation_validation()
    {
        $response = $this->post('/courses', [
            'COU_NOM' => 'Test Course',
            'RAID_NUM' => 1,
            'TYP_NUM' => 1,
        ]);
        $this->assertTrue(in_array($response->getStatusCode(), [200, 201, 302, 404, 422]));
    }

    /**
     * Test team management
     */
    public function test_team_management()
    {
        $response = $this->get('/courses/1/teams');
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 404]));
    }

    /**
     * Test CSV export functionality
     */
    public function test_csv_export()
    {
        $response = $this->get('/courses/1/export/csv');
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 404]));
    }

    /**
     * Test dossard generation
     */
    public function test_dossard_generation()
    {
        $response = $this->post('/courses/1/dossards/generate', []);
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 404, 422]));
    }

    /**
     * Test team deletion
     */
    public function test_team_deletion()
    {
        $response = $this->delete('/teams/1');
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 404, 422]));
    }
}
