<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VikRaid;
use App\Models\VikClub;
use Carbon\Carbon;
use App\Http\Requests\StoreRaidRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Important pour voir les erreurs dans storage/logs/laravel.log

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
        
        // Vérification des droits (gestionnaire de club)
        $managesClub = VikClub::where('INS_ID', $user->INS_ID)->exists();
        if (!$managesClub) {
            abort(403, 'Vous devez gérer au moins un club pour créer un raid.');
        }

        // Récupération des clubs gérés par l'utilisateur
        $clubs = VikClub::where('INS_ID', $user->INS_ID)->get();
        $clubIds = $clubs->pluck('CLU_NUM')->toArray();
        
        // Récupération des membres inscrits dans ces clubs
        $members = DB::table('VIK_ADHERER')
            ->join('VIK_INSCRIT', 'VIK_INSCRIT.INS_ID', '=', 'VIK_ADHERER.INS_ID')
            ->whereIn('VIK_ADHERER.CLU_NUM', $clubIds)
            ->select('VIK_INSCRIT.INS_ID', 'VIK_INSCRIT.INS_PRENOM', 'VIK_INSCRIT.INS_NOM', 'VIK_INSCRIT.INS_NUM_LICENCE', 'VIK_ADHERER.CLU_NUM')
            ->get();

        // CORRECTIF : Ajout manuel de l'utilisateur connecté dans la liste s'il n'y est pas
        // (Cela permet d'apparaître dans le menu déroulant même sans être adhérent)
        foreach ($clubs as $c) {
            $alreadyInList = $members->contains(function ($m) use ($user, $c) {
                return $m->INS_ID == $user->INS_ID && $m->CLU_NUM == $c->CLU_NUM;
            });

            if (!$alreadyInList) {
                $members->push((object)[
                    'INS_ID' => $user->INS_ID,
                    'INS_PRENOM' => $user->INS_PRENOM, 
                    'INS_NOM' => $user->INS_NOM,
                    'INS_NUM_LICENCE' => $user->INS_NUM_LICENCE ?? '',
                    'CLU_NUM' => $c->CLU_NUM
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
        if (!$managesClub) {
            abort(403, 'Vous devez gérer au moins un club pour créer un raid.');
        }

        try {
            // 1. Validation des données (si ça échoue ici, Laravel redirige automatiquement vers le formulaire avec $errors)
            $data = $request->validated();

            // 2. Calcul du prochain ID (car auto-incrément désactivé ou manuel)
            $max = VikRaid::max('RAID_NUM');
            $next = $max ? ((int)$max + 1) : 1;
            $data['RAID_NUM'] = $next;

            // 3. Gestion de l'image
            if ($request->hasFile('RAID_ILLUSTRATION')) {
                $file = $request->file('RAID_ILLUSTRATION');
                $filename = 'illustration_' . $data['RAID_NUM'] . '_' . time() . '.' . $file->getClientOriginalExtension();

                $publicDir = public_path('images');
                if (!is_dir($publicDir)) {
                    mkdir($publicDir, 0755, true);
                }

                $file->move($publicDir, $filename);
                $data['RAID_ILLUSTRATION'] = $filename;
            }

            // 4. Création en base de données
            $raid = VikRaid::create($data);

            if ($request->expectsJson()) {
                return response()->json(['raid' => $raid], 201);
            }
            
            // Succès
            return redirect("/raid/{$raid->RAID_NUM}")->with('success', 'Raid créé avec succès.');

        } catch (\Exception $e) {
            // En cas d'erreur technique (ex: base de données)
            
            // On écrit l'erreur dans le fichier storage/logs/laravel.log
            Log::error("Erreur lors de la création du raid : " . $e->getMessage());

            // On redirige vers le formulaire pour afficher l'erreur
            return redirect()->back()
                ->withInput() // Garde les champs remplis
                ->with('error', 'Erreur système : ' . $e->getMessage());
        }
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

        $allRaids = VikRaid::where('INS_ID', $user->INS_ID)
            ->orderBy('RAID_DATE_DEBUT', 'desc')
            ->get();

        $years = $allRaids
            ->pluck('RAID_DATE_DEBUT')
            ->map(fn($date) => Carbon::parse($date)->year)
            ->unique()
            ->sortDesc()
            ->values();

        $selectedYear = $request->integer('year') ?: Carbon::now()->year;

        // Get raids for the selected year
        $raids = VikRaid::where('INS_ID', $user->INS_ID)
            ->whereYear('RAID_DATE_DEBUT', $selectedYear)
            ->with(['courses', 'club'])
            ->withCount('courses')
            ->orderBy('RAID_DATE_DEBUT', 'desc')
            ->get();

        $now = Carbon::now();

        $raids = $raids->map(function ($raid) use ($now) {
            $raid->isPast = Carbon::parse($raid->RAID_DATE_DEBUT)->lt($now);
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

        // Vérification stricte des droits
        if ((int)$raid->INS_ID !== (int)$user->INS_ID && ((int)$club->INS_ID !== (int)$user->INS_ID)) {
            abort(403, 'Seul le responsable du raid ou le gérant du club peut le modifier.');
        }

        // Si gérant du club, voit tous ses clubs. Sinon, voit uniquement le club du raid.
        if ((int)$club->INS_ID === (int)$user->INS_ID) {
            $clubs = VikClub::where('INS_ID', $user->INS_ID)->get();
        } else {
            $clubs = VikClub::where('CLU_NUM', $raid->CLU_NUM)->get();
        }

        $clubIds = $clubs->pluck('CLU_NUM')->toArray();
        
        $members = DB::table('VIK_ADHERER')
            ->join('VIK_INSCRIT', 'VIK_INSCRIT.INS_ID', '=', 'VIK_ADHERER.INS_ID')
            ->whereIn('VIK_ADHERER.CLU_NUM', $clubIds)
            ->select('VIK_INSCRIT.INS_ID', 'VIK_INSCRIT.INS_PRENOM', 'VIK_INSCRIT.INS_NOM', 'VIK_INSCRIT.INS_NUM_LICENCE', 'VIK_ADHERER.CLU_NUM')
            ->get();
            
        // CORRECTIF : Ajout manuel de l'utilisateur connecté dans la liste pour l'édition aussi
        foreach ($clubs as $c) {
            $alreadyInList = $members->contains(function ($m) use ($user, $c) {
                return $m->INS_ID == $user->INS_ID && $m->CLU_NUM == $c->CLU_NUM;
            });

            if (!$alreadyInList) {
                $members->push((object)[
                    'INS_ID' => $user->INS_ID,
                    'INS_PRENOM' => $user->INS_PRENOM, 
                    'INS_NOM' => $user->INS_NOM,
                    'INS_NUM_LICENCE' => $user->INS_NUM_LICENCE ?? '',
                    'CLU_NUM' => $c->CLU_NUM
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

        if ((int)$raid->INS_ID !== (int)$user->INS_ID && ((int)$club->INS_ID !== (int)$user->INS_ID)) {
            abort(403, 'Seul le responsable du raid ou le gérant du club peut le modifier.');
        }

        $data = $request->validated();

        if ($request->hasFile('RAID_ILLUSTRATION')) {
            $file = $request->file('RAID_ILLUSTRATION');
            $filename = 'illustration_' . $raid->RAID_NUM . '_' . time() . '.' . $file->getClientOriginalExtension();

            $publicDir = public_path('images');
            if (!is_dir($publicDir)) {
                mkdir($publicDir, 0755, true);
            }

            $file->move($publicDir, $filename);
            $data['RAID_ILLUSTRATION'] = $filename;
        }

        $raid->update($data);

        return redirect()->route('organisateur.dashboard')->with('success', 'Raid modifié avec succès.');
    }
}