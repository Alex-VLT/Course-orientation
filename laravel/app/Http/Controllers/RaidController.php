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

        $clubs = VikClub::where('INS_ID', $user->INS_ID)->get();
        $clubIds = $clubs->pluck('CLU_NUM')->toArray();
        $members = \Illuminate\Support\Facades\DB::table('VIK_ADHERER')
            ->join('VIK_INSCRIT', 'VIK_INSCRIT.INS_ID', '=', 'VIK_ADHERER.INS_ID')
            ->whereIn('VIK_ADHERER.CLU_NUM', $clubIds)
            ->select('VIK_INSCRIT.INS_ID', 'VIK_INSCRIT.INS_PRENOM', 'VIK_INSCRIT.INS_NOM', 'VIK_INSCRIT.INS_NUM_LICENCE', 'VIK_ADHERER.CLU_NUM')
            ->get();

        return view('pages.raids.create', compact('clubs', 'members'));
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

        $max = VikRaid::max('RAID_NUM');
        $next = $max ? ((int)$max + 1) : 1;
        $data['RAID_NUM'] = $next;

        if ($request->hasFile('RAID_ILLUSTRATION')) {
            $file = $request->file('RAID_ILLUSTRATION');
            $filename = 'raid_' . $data['RAID_NUM'] . '_' . time() . '.' . $file->getClientOriginalExtension();
            \Illuminate\Support\Facades\Storage::disk('public')->putFileAs('images', $file, $filename);
            $data['RAID_ILLUSTRATION'] = $filename;
        }

        $raid = VikRaid::create($data);

        if ($request->expectsJson()) {
            return response()->json(['raid' => $raid], 201);
        }
        return redirect("/raid/{$raid->RAID_NUM}")->with('success', 'Raid créé avec succès.');
    }

    public function show($raid)
    {
        $raid = VikRaid::where('RAID_NUM', $raid)->firstOrFail();

        if (request()->expectsJson()) {
            return response()->json(['raid' => $raid]);
        }

        return view('pages.raid', compact('raid'));
    }
}
