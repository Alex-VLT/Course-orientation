<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\VikRaid;
use App\Models\VikClub;
use Carbon\Carbon;
use App\Http\Requests\StoreRaidRequest;
use App\Models\User;


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

    /**
     * Show create form for a raid (only for users managing at least one club).
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $managesClub = VikClub::where('INS_ID', $user->INS_ID)->exists();
        if (!$managesClub) {
            abort(403, 'Vous devez gérer au moins un club pour créer un raid.');
        }

        // Clubs managed by this user
        $clubs = VikClub::where('INS_ID', $user->INS_ID)->get();

        // Candidate responsibles: adherents (license or PPS if present)
        $responsiblesQuery = User::query()->whereNotNull('INS_NUM_LICENCE');
        if (\Illuminate\Support\Facades\Schema::hasColumn('VIK_INSCRIT', 'INS_NUM_PPS')) {
            $responsiblesQuery->orWhereNotNull('INS_NUM_PPS');
        }
        $responsibles = $responsiblesQuery->get();

        return view('pages.raids.create', compact('clubs', 'responsibles'));
    }

    /**
     * Store a newly created raid.
     */
    public function store(StoreRaidRequest $request)
    {
        $user = $request->user();
        $managesClub = VikClub::where('INS_ID', $user->INS_ID)->exists();
        if (!$managesClub) {
            abort(403, 'Vous devez gérer au moins un club pour créer un raid.');
        }

        $data = $request->validated();

        // Generate a RAID_NUM if needed (table uses non-incrementing PK)
        $max = VikRaid::max('RAID_NUM');
        $next = $max ? ((int)$max + 1) : 1;
        $data['RAID_NUM'] = $next;

        // INS_ID comes from the form (the designated raid responsible) — validated by the FormRequest
        $raid = VikRaid::create($data);

        return redirect()->route('raid.show', $raid->RAID_NUM)->with('success', 'Raid créé avec succès.');
    }
}
