<?php

namespace App\Http\Controllers;

use App\Models\VikRace;
use App\Models\VikRaid;
use App\Models\VikEquipe;
use App\Models\User;
use App\Http\Requests\StoreCourseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // For DateTime
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

        // Generate a COU_NUM primary key if necessary
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

    /*
        Function to load races from the 
        organizer page index.blade.php
    */
    public function organizerIndex()
    {
        $userId = Auth::id();

        /* We search for races for which the user is responsible 
        using the VikRace model with "vik_course" in the database */
        $races = VikRace::where('INS_ID', $userId)
            ->with('raid')
            ->orderBy('COU_DATE_DEPART', 'desc')
            ->get();

        // If the race doesn't exist, then we won't go any further.
        if ($races->isEmpty()) {
            return redirect('/')->with('error', "Vous n'êtes responsable d'aucune course.");
        }

        // Compare the dates to place in the correct category
        $now = Carbon::now();
        $upcomingRaces = $races->where('COU_DATE_DEPART', '>=', $now);
        $pastRaces = $races->where('COU_DATE_DEPART', '<', $now);

        return view('pages.courses.organizer_index', compact('upcomingRaces', 'pastRaces'));
    }

    /*
        Redirecting to the edit.blade.php page, 
        check for the existence of a race
    */
    public function edit(int $cou_num)
    {
        $race = VikRace::findOrFail($cou_num);
        if ((int)$race->INS_ID !== (int)Auth::id()) abort(403);
        return view('pages.courses.edit', compact('race'));
    }

    /*
        Function to change race data
    */
    public function update(int $cou_num, Request $request)
    {
        $race = VikRace::findOrFail($cou_num);
        if ((int)$race->INS_ID !== (int)Auth::id()) abort(403);

        $validated = $request->validate([
            'COU_NOM' => 'required|string|max:64',
            'COU_DATE_DEPART' => 'required|date',
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

        // --- AUTOMATIC COMPUTING LOGIC ---
        
        // We retrieve the departure date that was sent
        $dateDepart = Carbon::parse($validated['COU_DATE_DEPART']);
        
        // We retrieve the duration sent
        $dureeMinutes = (int) $validated['COU_DUREE'];

        // We calculate the end date (Start + Duration)
        $dateFin = $dateDepart->copy()->addMinutes($dureeMinutes);

        // The calculated end date is injected into the data table to be updated.
        $validated['COU_DATE_FIN'] = $dateFin;

        // -------------------------------------

        $race->update($validated);

        return redirect()->route('race.organizer_index')->with('success', 'Course mise à jour. La date de fin a été recalculée automatiquement.');
    }

    /*
        Function used for managing teams in a race
        Displays teams, team members, and their status (whether paid or not).
    */
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

    /*
        To change a team's payment status, 
        only the manager can change this status.
    */
    public function togglePayment(int $cou_num, int $equ_num)
    {
        $race = VikRace::findOrFail($cou_num);
        if ((int)$race->INS_ID !== (int)Auth::id()) abort(403);

        // Team recovery
        $equipe = VikEquipe::where('COU_NUM', $cou_num)->where('EQU_NUM', $equ_num)->firstOrFail();
        $newState = !$equipe->EQU_PAIEMENT_VALIDE;

        // Change of status
        DB::table('vik_equipe')
            ->where('COU_NUM', $cou_num)
            ->where('EQU_NUM', $equ_num)
            ->update(['EQU_PAIEMENT_VALIDE' => $newState]);

        return back()->with('success', $newState ? "Paiement validé." : "Paiement annulé.");
    }

    /*
        Function to remove a team from a race
    */
    public function deleteTeam(int $cou_num, int $equ_num)
    {
        $race = VikRace::findOrFail($cou_num);
        if ((int)$race->INS_ID !== (int)Auth::id()) abort(403);

        // Using transactions to remove all data related to a team from all tables
        DB::transaction(function () use ($cou_num, $equ_num) {
            DB::table('vik_participer')->where('COU_NUM', $cou_num)->where('EQU_NUM', $equ_num)->delete();
            DB::table('vik_equipe')->where('COU_NUM', $cou_num)->where('EQU_NUM', $equ_num)->delete();
        });

        return back()->with('success', 'Équipe supprimée.');
    }
}
