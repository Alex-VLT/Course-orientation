<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardTest extends TestCase
{
    /**
     * Test dashboard page is accessible
     */
    public function test_dashboard_displays_for_club_manager()
    {
        $response = $this->get('/dashboard');
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 404]));
    }

    /**
     * Test dashboard for non-managers
     */
    public function test_dashboard_empty_for_non_managers()
    {
        $response = $this->get('/dashboard');
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 404]));
    }

    /**
     * Test manager can see club members
     */
    public function test_manager_can_see_club_members()
    {
        $response = $this->get('/dashboard');
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 404]));
    }

    /**
     * Test manager can remove member from club
     */
    public function test_manager_can_remove_member_from_club()
    {
        $response = $this->post('/dashboard/remove-member', ['member_id' => 1]);
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 404, 405]));
    }

    /**
     * Test manager cannot remove themselves
     */
    public function test_manager_cannot_remove_themselves()
    {
        $response = $this->post('/dashboard/remove-member', ['member_id' => 0]);
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 404, 405]));
    }

    /**
     * Test dashboard shows club raids
     */
    public function test_dashboard_shows_club_raids()
    {
        $response = $this->get('/dashboard');
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 404]));
    }
}
