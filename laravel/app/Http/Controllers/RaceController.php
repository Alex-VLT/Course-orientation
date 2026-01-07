<?php

namespace App\Http\Controllers;

use App\Models\VikRace;

class RaceController extends Controller
{
    public function show(int $race_num)
    {
        $race = VikRace::query()
            ->with(['raid', 'acceptances.tranche'])
            ->findOrFail($race_num);


        return view('pages.race', compact('race'));
    }
}
