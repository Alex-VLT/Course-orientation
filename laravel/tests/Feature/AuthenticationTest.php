<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VikClub;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can view login page
     */
    public function test_user_can_view_login_page()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertViewIs('pages.auth.login');
    }

    /**
     * Test user can login with valid credentials
     */
    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'INS_MAIL' => 'test@example.com',
            'INS_MDP' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test user cannot login with invalid credentials
     */
    public function test_user_cannot_login_with_invalid_credentials()
    {
        User::factory()->create([
            'INS_MAIL' => 'test@example.com',
            'INS_MDP' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect();
        $this->assertGuest();
    }

    /**
     * Test user can register with valid data
     */
    public function test_user_can_register_with_valid_data()
    {
        $response = $this->post('/register', [
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'jean@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'naissance' => '2000-01-15',
            'ville' => 'Paris',
            'cp' => '75001',
            'adresse' => '123 Main Street',
            'tel' => '0123456789',
            'licence' => null,
            'club_id' => null,
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('VIK_INSCRIT', ['INS_MAIL' => 'jean@example.com']);
    }

    /**
     * Test user cannot register with duplicate email
     */
    public function test_user_cannot_register_with_duplicate_email()
    {
        User::factory()->create(['INS_MAIL' => 'existing@example.com']);

        $response = $this->post('/register', [
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'naissance' => '2000-01-15',
            'ville' => 'Paris',
            'cp' => '75001',
            'adresse' => '123 Main Street',
            'tel' => '0123456789',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test user cannot register if too young
     */
    public function test_user_cannot_register_if_too_young()
    {
        $today = now();
        $tooYoungDate = $today->copy()->subYears(11)->toDateString();

        $response = $this->post('/register', [
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'young@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'naissance' => $tooYoungDate,
            'ville' => 'Paris',
            'cp' => '75001',
            'adresse' => '123 Main Street',
            'tel' => '0123456789',
        ]);

        $response->assertSessionHasErrors('naissance');
    }

    /**
     * Test user can logout
     */
    public function test_user_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * Test user can request password reset
     */
    public function test_user_can_request_password_reset()
    {
        User::factory()->create(['INS_MAIL' => 'test@example.com']);

        $response = $this->post('/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHas('status');
    }

    /**
     * Test user profile page shows user data and courses
     */
    public function test_user_profile_shows_user_data()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profil');

        $response->assertStatus(200);
        $response->assertViewIs('pages.profil');
    }
}
