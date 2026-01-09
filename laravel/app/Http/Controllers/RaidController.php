<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRaidRequest;
use App\Models\User;
use App\Models\VikClub;
use App\Models\VikRaid;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RaidController extends Controller
{
    public function index(Request $request)
    {
        $query = VikRaid::query();

        if ($request->filled('search')) {
            $query->where('RAID_NOM', 'like', '%'.$request->search.'%');
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

        // Vérification des droits (gestionnaire de club)
        $managesClub = VikClub::where('INS_ID', $user->INS_ID)->exists();
        if (! $managesClub) {
            abort(403, 'Vous devez gérer au moins un club pour créer un raid.');
        }

        // Récupération des clubs gérés par l'utilisateur
        $clubs = VikClub::where('INS_ID', $user->INS_ID)->get();
        $clubIds = $clubs->pluck('CLU_NUM')->toArray();
        $members = \Illuminate\Support\Facades\DB::table('vik_adherer')
            ->join('vik_inscrit', 'vik_inscrit.INS_ID', '=', 'vik_adherer.INS_ID')
            ->whereIn('vik_adherer.CLU_NUM', $clubIds)
            ->select('vik_inscrit.INS_ID', 'vik_inscrit.INS_PRENOM', 'vik_inscrit.INS_NOM', 'vik_inscrit.INS_NUM_LICENCE', 'vik_adherer.CLU_NUM')
            ->get();

        // CORRECTIF : Ajout manuel de l'utilisateur connecté dans la liste s'il n'y est pas
        // (Cela permet d'apparaître dans le menu déroulant même sans être adhérent)
        foreach ($clubs as $c) {
            $alreadyInList = $members->contains(function ($m) use ($user, $c) {
                return $m->INS_ID == $user->INS_ID && $m->CLU_NUM == $c->CLU_NUM;
            });

            if (! $alreadyInList) {
                $members->push((object) [
                    'INS_ID' => $user->INS_ID,
                    'INS_PRENOM' => $user->INS_PRENOM,
                    'INS_NOM' => $user->INS_NOM,
                    'INS_NUM_LICENCE' => $user->INS_NUM_LICENCE ?? '',
                    'CLU_NUM' => $c->CLU_NUM,
                ]);
            }
        }

        return view('pages.raids.create', compact('clubs', 'members'));
    }

    /**
     * Store a newly created raid.
     */
    public function store(StoreRaidRequest $request)
    {
        $user = $request->user();

        // Vérification des droits
        $managesClub = VikClub::where('INS_ID', $user->INS_ID)->exists();
        if (! $managesClub) {
            abort(403, 'Vous devez gérer au moins un club pour créer un raid.');
        }

        try {
            $data = $request->validated();

            $requestedId = (int) ($request->input('RAID_NUM') ?? 0);
            if ($requestedId > 0) {
                $data['RAID_NUM'] = $requestedId;
            } else {
                $max = VikRaid::max('RAID_NUM');
                $next = $max ? ((int) $max + 1) : 1;
                $data['RAID_NUM'] = $next;
            }

            if ($request->hasFile('RAID_ILLUSTRATION')) {
                $file = $request->file('RAID_ILLUSTRATION');
                $filename = 'illustration_'.$data['RAID_NUM'].'_'.time().'.'.$file->getClientOriginalExtension();

                $publicDir = public_path('images');
                if (! is_dir($publicDir)) {
                    mkdir($publicDir, 0755, true);
                }

                $file->move($publicDir, $filename);
                $data['RAID_ILLUSTRATION'] = $filename;
            }

            $raid = VikRaid::create($data);

            if ($request->expectsJson()) {
                return response()->json(['raid' => $raid], 201);
            }

            return redirect("/raid/{$raid->RAID_NUM}")->with('success', 'Raid créé avec succès.');

        } catch (\Exception $e) {

            Log::error('Erreur lors de la création du raid : '.$e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur système : '.$e->getMessage());
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

    /**
     * Display the raid management page for raids the user is responsible for.
     */
    public function managerIndex(Request $request)
    {
        $user = $request->user();

        $managedClubIds = VikClub::where('INS_ID', $user->INS_ID)->pluck('CLU_NUM');

        $allRaids = VikRaid::query()
            ->where('INS_ID', $user->INS_ID)
            ->when($managedClubIds->isNotEmpty(), function ($q) use ($managedClubIds) {
                $q->orWhereIn('CLU_NUM', $managedClubIds);
            })
            ->orderBy('RAID_DATE_DEBUT', 'desc')
            ->get();

        $years = $allRaids
            ->pluck('RAID_DATE_DEBUT')
            ->map(fn ($date) => Carbon::parse($date)->year)
            ->unique()
            ->sortDesc()
            ->values();

        $selectedYear = $request->integer('year') ?: Carbon::now()->year;

        // Get raids for the selected year
        $raids = VikRaid::query()
            ->where(function ($q) use ($user, $managedClubIds) {
                $q->where('INS_ID', $user->INS_ID);
                if ($managedClubIds->isNotEmpty()) {
                    $q->orWhereIn('CLU_NUM', $managedClubIds);
                }
            })
            ->whereYear('RAID_DATE_DEBUT', $selectedYear)
            ->with(['courses', 'club'])
            ->withCount('courses')
            ->orderBy('RAID_DATE_DEBUT', 'desc')
            ->get();

        $now = Carbon::now();

        $raids = $raids->map(function ($raid) use ($now, $user) {
            $raid->isPast = Carbon::parse($raid->RAID_DATE_DEBUT)->lt($now);
            $raid->user_is_raid_responsible = ((int) $raid->INS_ID === (int) $user->INS_ID);

            return $raid;
        });

        return view('pages.raid-manager', compact('raids', 'years', 'selectedYear'));
    }

    /**
     * Show the form for editing a raid.
     */
    public function edit(int $raid_num, Request $request)
    {
        $raid = VikRaid::findOrFail($raid_num);
        $user = $request->user();

        $club = VikClub::where('CLU_NUM', $raid->CLU_NUM)->first();

        if ((int) $raid->INS_ID !== (int) $user->INS_ID && ((int) $club->INS_ID !== (int) $user->INS_ID)) {
            abort(403, 'Seul le responsable du raid ou le gérant du club peut le modifier.');
        }

        if ((int) $club->INS_ID === (int) $user->INS_ID) {
            $clubs = VikClub::where('INS_ID', $user->INS_ID)->get();
        } else {
            $clubs = VikClub::where('CLU_NUM', $raid->CLU_NUM)->get();
        }

        $clubIds = $clubs->pluck('CLU_NUM')->toArray();
        $members = \Illuminate\Support\Facades\DB::table('vik_adherer')
            ->join('vik_inscrit', 'vik_inscrit.INS_ID', '=', 'vik_adherer.INS_ID')
            ->whereIn('vik_adherer.CLU_NUM', $clubIds)
            ->select('vik_inscrit.INS_ID', 'vik_inscrit.INS_PRENOM', 'vik_inscrit.INS_NOM', 'vik_inscrit.INS_NUM_LICENCE', 'vik_adherer.CLU_NUM')
            ->get();

        // CORRECTIF : Ajout manuel de l'utilisateur connecté dans la liste pour l'édition aussi
        foreach ($clubs as $c) {
            $alreadyInList = $members->contains(function ($m) use ($user, $c) {
                return $m->INS_ID == $user->INS_ID && $m->CLU_NUM == $c->CLU_NUM;
            });

            if (! $alreadyInList) {
                $members->push((object) [
                    'INS_ID' => $user->INS_ID,
                    'INS_PRENOM' => $user->INS_PRENOM,
                    'INS_NOM' => $user->INS_NOM,
                    'INS_NUM_LICENCE' => $user->INS_NUM_LICENCE ?? '',
                    'CLU_NUM' => $c->CLU_NUM,
                ]);
            }
        }

        return view('pages.raids.edit', compact('raid', 'clubs', 'members'));
    }

    /**
     * Update the specified raid.
     */
    public function update(int $raid_num, StoreRaidRequest $request)
    {
        $raid = VikRaid::findOrFail($raid_num);
        $user = $request->user();

        $club = VikClub::where('CLU_NUM', $raid->CLU_NUM)->first();

        if ((int) $raid->INS_ID !== (int) $user->INS_ID && ((int) $club->INS_ID !== (int) $user->INS_ID)) {
            abort(403, 'Seul le responsable du raid ou le gérant du club peut le modifier.');
        }

        $data = $request->validated();

        if ($request->hasFile('RAID_ILLUSTRATION')) {
            $file = $request->file('RAID_ILLUSTRATION');
            $filename = 'illustration_'.$raid->RAID_NUM.'_'.time().'.'.$file->getClientOriginalExtension();

            $publicDir = public_path('images');
            if (! is_dir($publicDir)) {
                mkdir($publicDir, 0755, true);
            }

            $file->move($publicDir, $filename);
            $data['RAID_ILLUSTRATION'] = $filename;
        }

        $raid->update($data);

        return redirect()->route('dashboard')->with('success', 'Raid modifié avec succès.');
    }
}
