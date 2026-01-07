<?php

namespace App\Http\Controllers;

use App\Models\VikRace;
use App\Models\VikRaid;
use App\Models\VikEquipe;
use App\Models\User;
use App\Http\Requests\StoreCourseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
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

    public function create(int $raid_num, Request $request)
    {
        $raid = VikRaid::findOrFail($raid_num);

        // Only the raid responsible may create courses (raid INS_ID is the responsable)
        $user = $request->user();
        if ((int)$raid->INS_ID !== (int)$user->INS_ID) {
            abort(403, 'Seul le responsable du raid peut créer des courses.');
        }

        $responsiblesQuery = User::query()->whereNotNull('INS_NUM_LICENCE');
        if (\Illuminate\Support\Facades\Schema::hasColumn('VIK_INSCRIT', 'INS_NUM_PPS')) {
            $responsiblesQuery->orWhereNotNull('INS_NUM_PPS');
        }
        $responsibles = $responsiblesQuery->get();

        return view('pages.courses.create', compact('raid', 'responsibles'));
    }

    public function store(int $raid_num, StoreCourseRequest $request)
    {
        $raid = VikRaid::findOrFail($raid_num);
        $user = $request->user();
        if ((int)$raid->INS_ID !== (int)$user->INS_ID) {
            abort(403, 'Seul le responsable du raid peut créer des courses.');
        }

        $data = $request->validated();
        $data['RAID_NUM'] = $raid->RAID_NUM;

        // Generate a COU_NUM primary key if necessary (non-incrementing PK)
        $max = VikRace::max('COU_NUM');
        $next = $max ? ((int)$max + 1) : 1;
        $data['COU_NUM'] = $next;

        $race = VikRace::create($data);

        return redirect()->route('race.show', $race->COU_NUM)->with('success', 'Course créée.');
    }

    public function generateDossards(int $cou_num, Request $request)
    {
        $race = VikRace::findOrFail($cou_num);
        $user = $request->user();
        if ((int)$race->INS_ID !== (int)$user->INS_ID) {
            abort(403);
        }

        $count = (int)$request->input('count', 100);
        $start = $race->dossards()->max('DOSS_NUM') ?? 0;
        $toCreate = [];
        for ($i = 1; $i <= $count; $i++) {
            $toCreate[] = ['COU_NUM' => $race->COU_NUM, 'DOSS_NUM' => $start + $i, 'created_at' => now(), 'updated_at' => now()];
        }

        \App\Models\VikDossard::insert($toCreate);

        return redirect()->route('race.manage', $race->COU_NUM)->with('success', "{$count} dossards générés.");
    }

    public function uploadResults(int $cou_num, Request $request)
    {
        $race = VikRace::findOrFail($cou_num);
        $user = $request->user();
        if ((int)$race->INS_ID !== (int)$user->INS_ID) {
            abort(403);
        }

        $request->validate(['results' => ['required', 'file', 'mimes:csv,txt']]);
        $file = $request->file('results');
        $path = $file->storeAs('results', 'course_'.$race->COU_NUM.'_'.time().'.csv');

        return redirect()->route('race.manage', $race->COU_NUM)->with('success', 'Fichier enregistré : ' . $path);
    }

    public function validateCourse(int $cou_num, Request $request)
    {
        $race = VikRace::findOrFail($cou_num);
        $user = $request->user();
        if ((int)$race->INS_ID !== (int)$user->INS_ID) {
            abort(403);
        }

        $race->COU_VALIDE = true;
        $race->save();

        return redirect()->route('race.manage', $race->COU_NUM)->with('success', 'Course validée.');
    }


    /* ### Race management ### */

    public function organizerIndex()
    {
        $userId = Auth::id();
        $races = VikRace::where('INS_ID', $userId)
            ->with('raid')
            ->orderBy('COU_DATE_DEPART', 'desc')
            ->get();

        if ($races->isEmpty()) {
            return redirect('/')->with('error', "Vous n'êtes responsable d'aucune course.");
        }

        $now = Carbon::now();
        $upcomingRaces = $races->where('COU_DATE_DEPART', '>=', $now);
        $pastRaces = $races->where('COU_DATE_DEPART', '<', $now);

        return view('pages.courses.organizer_index', compact('upcomingRaces', 'pastRaces'));
    }

    public function edit(int $cou_num)
    {
        $race = VikRace::findOrFail($cou_num);
        if ((int)$race->INS_ID !== (int)Auth::id()) abort(403);
        return view('pages.courses.edit', compact('race'));
    }

    /**
     * MODIFICATION ICI : Calcul automatique de la date de fin
     */
    public function update(int $cou_num, Request $request)
    {
        $race = VikRace::findOrFail($cou_num);
        if ((int)$race->INS_ID !== (int)Auth::id()) abort(403);

        // On valide les données
        // Note : On retire COU_DATE_FIN des règles "required" car on va le calculer nous-mêmes
        $validated = $request->validate([
            'COU_NOM' => 'required|string|max:64',
            'COU_DATE_DEPART' => 'required|date',
            // 'COU_DATE_FIN' => 'required|date', // On ne demande plus à l'utilisateur de valider la fin
            'COU_DUREE' => 'required|integer|min:1',
            'COU_DIFFICULTE' => 'required|string|max:64',
            'COU_PRIX_REPAS' => 'nullable|numeric|min:0',
            'COU_REDUC_LICENCIE' => 'nullable|numeric|min:0',
            'COU_NB_PART_MIN' => 'required|integer|min:1',
            'COU_NB_PART_MAX' => 'required|integer|gte:COU_NB_PART_MIN',
            'COU_NB_EQU_MIN' => 'required|integer|min:1',
            'COU_NB_EQU_MAX' => 'required|integer|gte:COU_NB_EQU_MIN',
            'COU_PART_PAR_EQU_MAX' => 'required|integer|min:1',
        ]);

        // --- LOGIQUE DE CALCUL AUTOMATIQUE ---
        
        // 1. On récupère la date de départ envoyée
        $dateDepart = Carbon::parse($validated['COU_DATE_DEPART']);
        
        // 2. On récupère la durée envoyée
        $dureeMinutes = (int) $validated['COU_DUREE'];

        // 3. On calcule la date de fin (Départ + Durée)
        $dateFin = $dateDepart->copy()->addMinutes($dureeMinutes);

        // 4. On injecte la date de fin calculée dans le tableau de données à mettre à jour
        $validated['COU_DATE_FIN'] = $dateFin;

        // -------------------------------------

        $race->update($validated);

        return redirect()->route('race.organizer_index')->with('success', 'Course mise à jour. La date de fin a été recalculée automatiquement.');
    }

    public function manage(int $cou_num, Request $request)
    {
        $race = VikRace::findOrFail($cou_num);
        if ((int)$race->INS_ID !== (int)Auth::id()) return redirect('/')->with('error', 'Accès refusé');

        $race->load([
            'equipes.participations' => function($query) use ($cou_num) {
                $query->where('COU_NUM', $cou_num)->with('user');
            },
            'equipes.createur'
        ]);

        return view('pages.courses.manage', compact('race'));
    }

    public function togglePayment(int $cou_num, int $equ_num)
    {
        $race = VikRace::findOrFail($cou_num);
        if ((int)$race->INS_ID !== (int)Auth::id()) abort(403);

        $equipe = VikEquipe::where('COU_NUM', $cou_num)->where('EQU_NUM', $equ_num)->firstOrFail();
        $newState = !$equipe->EQU_PAIEMENT_VALIDE;

        DB::table('vik_equipe')
            ->where('COU_NUM', $cou_num)
            ->where('EQU_NUM', $equ_num)
            ->update(['EQU_PAIEMENT_VALIDE' => $newState]);

        return back()->with('success', $newState ? "Paiement validé." : "Paiement annulé.");
    }

    public function deleteTeam(int $cou_num, int $equ_num)
    {
        $race = VikRace::findOrFail($cou_num);
        if ((int)$race->INS_ID !== (int)Auth::id()) abort(403);

        DB::transaction(function () use ($cou_num, $equ_num) {
            DB::table('vik_participer')->where('COU_NUM', $cou_num)->where('EQU_NUM', $equ_num)->delete();
            DB::table('vik_equipe')->where('COU_NUM', $cou_num)->where('EQU_NUM', $equ_num)->delete();
        });

        return back()->with('success', 'Équipe supprimée.');
    }
}
