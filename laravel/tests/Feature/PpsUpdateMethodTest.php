<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PpsUpdateMethodTest extends TestCase
{
    public function test_put_method_updates_pps_successfully(): void
    {
        // Seeded base: course 1000 is managed by INS_ID=1; participant 7 is in team 1
        $user = User::query()->where('INS_ID', 1)->first();
        $this->assertNotNull($user, 'Expected user with INS_ID=1 to exist');

        // Pick an existing participant for course 1000
        $row = DB::table('vik_participer')->where('COU_NUM', 1000)->first();
        $this->assertNotNull($row, 'Expected at least one participant in course 1000');

        // Ensure initial state
        DB::table('vik_participer')
            ->where('COU_NUM', 1000)
            ->where('EQU_NUM', $row->EQU_NUM)
            ->where('INS_ID', $row->INS_ID)
            ->update(['PAR_NUM_PPS' => null]);

        $response = $this->actingAs($user)->put("/course/1000/team/{$row->EQU_NUM}/member/{$row->INS_ID}/pps", [
            'pps' => 'PPS123456',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('vik_participer', [
            'COU_NUM' => 1000,
            'EQU_NUM' => $row->EQU_NUM,
            'INS_ID' => $row->INS_ID,
            'PAR_NUM_PPS' => 'PPS123456',
        ]);
    }
}
