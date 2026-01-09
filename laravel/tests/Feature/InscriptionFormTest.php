<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class InscriptionFormTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unauthenticated_user_cannot_submit_team()
    {
        $response = $this->post('/inscForm', [
            'team_name' => 'Test Team',
            'course' => 1,
        ]);

        // Should redirect to login or show error
        $response->assertStatus(302);
        $response->assertRedirect();
    }

    /** @test */
    public function missing_course_number_returns_error()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/inscForm', [
            'team_name' => 'Test Team',
            // course is missing
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors();
    }

    /** @test */
    public function form_shows_correct_fields_and_labels()
    {
        $response = $this->get('/inscForm');

        $response->assertStatus(200);
        $response->assertSee('Responsable d\'équipe');
        $response->assertSee('Nom de l\'équipe');
        $response->assertSee('Ajouter un coureur');
    }
}
