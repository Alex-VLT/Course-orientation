<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VikRace;
use App\Models\VikRaid;
use App\Models\VikClub;

class DashboardController extends Controller
{
    /**
     * Display the dashboard for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $courses = VikRace::where('INS_ID', $user->INS_ID)
            ->orderBy('COU_DATE_DEPART', 'desc')
            ->get();

        $managesClub = VikClub::where('INS_ID', $user->INS_ID)->exists();

        $raids = collect();
        if ($managesClub) {
            $raids = VikRaid::where('INS_ID', $user->INS_ID)
                ->orderBy('RAID_DATE_DEBUT', 'desc')
                ->get();
        }
        return view('pages.dashboard', compact('courses', 'raids', 'managesClub'));
    }
}
