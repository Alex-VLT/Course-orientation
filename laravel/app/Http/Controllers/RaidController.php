<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\VikRaid;
use App\Models\VikClub;
use Carbon\Carbon;

class RaidController extends Controller
{

    public function show(int $raid_num)
    {
        $raid = VikRaid::query()
            ->with(['courses' => function ($q) {
                $q->orderBy('COU_DATE_DEPART', 'asc');
            }, 'courses.acceptances.tranche'])
            ->findOrFail($raid_num);

        if (!empty($raid->RAID_LIEN_SITE_WEB) && !preg_match('~^https?://~i', $raid->RAID_LIEN_SITE_WEB)) {
            $raid->RAID_LIEN_SITE_WEB = 'https://' . $raid->RAID_LIEN_SITE_WEB;
        }

        return view('pages.raid', compact('raid'));
    }

    public function index(Request $request)
    {
        $query = VikRaid::query();

        if ($request->filled('search')) {
            $query->where('RAID_NOM', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('club')) {
            $query->where('CLU_NUM', $request->club);
        }

        if ($request->filled('date_filter')) {
            $today = Carbon::now();
            if ($request->date_filter == 'future') {
                $query->where('RAID_DATE_DEBUT', '>=', $today);
            } elseif ($request->date_filter == 'past') {
                $query->where('RAID_DATE_DEBUT', '<', $today);
            }
        }

        $raids = $query->get();

        $clubs = VikClub::all();

        return view('pages.mainPage', compact('raids', 'clubs'));
    }
}
