<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can view contact form
     */
    public function test_user_can_view_contact_form()
    {
        $response = $this->get('/contact');
        
        $response->assertStatus(200);
        $response->assertViewIs('pages.contact');
    }

    /**
     * Test user can submit contact form with valid data
     */
    public function test_user_can_submit_contact_form_with_valid_data()
    {
        $response = $this->post('/contact', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'This is a test message with sufficient length',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /**
     * Test contact form validation - name required
     */
    public function test_contact_form_requires_name()
    {
        $response = $this->post('/contact', [
            'name' => '',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'This is a test message',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Test contact form validation - email required and valid
     */
    public function test_contact_form_requires_valid_email()
    {
        $response = $this->post('/contact', [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'subject' => 'Test Subject',
            'message' => 'This is a test message',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test contact form validation - subject required
     */
    public function test_contact_form_requires_subject()
    {
        $response = $this->post('/contact', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => '',
            'message' => 'This is a test message',
        ]);

        $response->assertSessionHasErrors('subject');
    }

    /**
     * Test contact form validation - message minimum length
     */
    public function test_contact_form_requires_minimum_message_length()
    {
        $response = $this->post('/contact', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test',
            'message' => 'Short',
        ]);

        $response->assertSessionHasErrors('message');
    }
}
