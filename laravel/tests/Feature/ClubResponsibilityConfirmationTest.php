<?php

namespace Tests\Feature;

use App\Mail\ClubResponsibilityConfirmation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ClubResponsibilityConfirmationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_creates_pending_club_and_sends_email(): void
    {
        Mail::fake();

        $adminId = DB::table('VIK_INSCRIT')->insertGetId([
            'INS_NOM' => 'Admin',
            'INS_PRENOM' => 'Test',
            'INS_MAIL' => 'admin@example.test',
            'INS_MDP' => Hash::make('secret'),
            'INS_TEL' => '0102030405',
            'INS_CODE_PO' => '75001',
            'INS_VILLE' => 'Paris',
            'INS_ADRESSE' => '1 rue Test',
            'INS_NAISSANCE' => '1990-01-01',
            'INS_IS_ADMIN' => 1,
            'INS_NUM_LICENCE' => 'LIC-ADMIN',
        ]);

        $responsibleId = DB::table('VIK_INSCRIT')->insertGetId([
            'INS_NOM' => 'Resp',
            'INS_PRENOM' => 'Club',
            'INS_MAIL' => 'resp@example.test',
            'INS_MDP' => Hash::make('secret'),
            'INS_TEL' => '0600000000',
            'INS_CODE_PO' => '69000',
            'INS_VILLE' => 'Lyon',
            'INS_ADRESSE' => '2 rue Resp',
            'INS_NAISSANCE' => '1992-02-02',
            'INS_IS_ADMIN' => 0,
            'INS_NUM_LICENCE' => 'LIC-RESP',
        ]);

        $admin = User::find($adminId);
        $this->actingAs($admin, 'web');

        $payload = [
            'CLU_NOM' => 'Club Test',
            'CLU_ADRESSE' => '10 rue Club',
            'CLU_CODE_POSTAL' => '31000',
            'CLU_VILLE' => 'Toulouse',
            'INS_ID' => $responsibleId,
        ];

        $response = $this->postJson('/clubs', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('VIK_CLUB_PENDING', [
            'INS_ID' => $responsibleId,
            'CLU_NOM' => 'Club Test',
        ]);

        Mail::assertSent(ClubResponsibilityConfirmation::class, function ($mail) use ($responsibleId) {
            return $mail->responsible->INS_ID === $responsibleId;
        });
    }

    public function test_confirmation_creates_club_and_clears_pending(): void
    {
        $responsibleId = DB::table('VIK_INSCRIT')->insertGetId([
            'INS_NOM' => 'Resp2',
            'INS_PRENOM' => 'Club',
            'INS_MAIL' => 'resp2@example.test',
            'INS_MDP' => Hash::make('secret'),
            'INS_TEL' => '0700000000',
            'INS_CODE_PO' => '44000',
            'INS_VILLE' => 'Nantes',
            'INS_ADRESSE' => '3 rue Resp',
            'INS_NAISSANCE' => '1993-03-03',
            'INS_IS_ADMIN' => 0,
            'INS_NUM_LICENCE' => 'LIC-RESP2',
        ]);

        $token = (string) \Illuminate\Support\Str::uuid();

        $pendingId = DB::table('VIK_CLUB_PENDING')->insertGetId([
            'token' => $token,
            'INS_ID' => $responsibleId,
            'CLU_NOM' => 'Club Validé',
            'CLU_ADRESSE' => '5 rue Valid',
            'CLU_CODE_POSTAL' => '13000',
            'CLU_VILLE' => 'Marseille',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get(route('clubs.confirm', ['token' => $token]));

        $response->assertRedirect(route('home'));
        $this->assertDatabaseHas('VIK_CLUB', [
            'INS_ID' => $responsibleId,
            'CLU_NOM' => 'Club Validé',
        ]);
        $this->assertDatabaseMissing('VIK_CLUB_PENDING', ['id' => $pendingId]);
    }
}
