<?php

namespace App\Http\Controllers;

use App\Models\VikRace;
use Illuminate\Support\Facades\DB;

class RaceController extends Controller
{
    public function show(int $race_num)
    {
        $race = VikRace::query()
            ->with(['raid', 'acceptances.tranche'])
            ->findOrFail($race_num);

        $teamsCount = DB::table('VIK_EQUIPE')->where('COU_NUM', $race_num)->count();

        return view('pages.race', compact('race', 'teamsCount'));
    }
}
