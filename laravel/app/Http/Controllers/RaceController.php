<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Models\User;
use App\Models\VikEquipe;
use App\Models\VikRace;
use App\Models\VikRaid;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RaceController extends Controller
{
    public function show(int $race_num)
    {
        $race = VikRace::query()
            ->with(['raid', 'acceptances.tranche'])
            ->findOrFail($race_num);

        $teamsCount = DB::table('vik_equipe')->where('COU_NUM', $race_num)->count();
        $isRegistered = false;
        $isTeamLeader = false;
        $userTeamNum = null;
        if (Auth::check()) {
            $current = Auth::user();

            $participation = DB::table('vik_participer')
                ->where('COU_NUM', $race_num)
                ->where('INS_ID', $current->INS_ID)
                ->first();

            $isRegistered = (bool) $participation;
            $userTeamNum = $participation->EQU_NUM ?? null;

            if (! empty($userTeamNum)) {
                $team = DB::table('vik_equipe')
                    ->where('COU_NUM', $race_num)
                    ->where('EQU_NUM', $userTeamNum)
                    ->first();
                if ($team && ((int) $team->INS_ID === (int) $current->INS_ID)) {
                    $isTeamLeader = true;
                }
            }
        }

        return view('pages.race', compact('race', 'teamsCount', 'isRegistered', 'isTeamLeader', 'userTeamNum'));
    }

    /**
     * Unsubscribe the currently authenticated user from a course (their own participation only).
     */
    public function unsubscribeParticipant(int $cou_num)
    {
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        // If the user participates in a team and they are responsible for that team, they must use the team-unsubscribe
        $participation = DB::table('vik_participer')->where('COU_NUM', $cou_num)->where('INS_ID', $user->INS_ID)->first();
        if ($participation && ! empty($participation->EQU_NUM)) {
            $team = DB::table('vik_equipe')->where('COU_NUM', $cou_num)->where('EQU_NUM', $participation->EQU_NUM)->first();
            if ($team && ((int) $team->INS_ID === (int) $user->INS_ID)) {
                return redirect()->route('race.show', $cou_num)->with('error', "Vous êtes responsable d'une équipe. Utilisez le bouton 'Désinscrire l'équipe'.");
            }
        }

        $course = DB::table('vik_course')->where('COU_NUM', $cou_num)->first();
        if (! $course) {
            abort(404, 'Course introuvable.');
        }
        if (\Carbon\Carbon::parse($course->COU_DATE_FIN)->isPast()) {
            return redirect()->route('race.show', $cou_num)->with('error', 'Course terminée : désinscription impossible.');
        }

        DB::table('vik_participer')
            ->where('COU_NUM', $cou_num)
            ->where('INS_ID', $user->INS_ID)
            ->delete();

        return redirect()->route('race.show', $cou_num)->with('success', 'Vous êtes désinscrit de la course.');
    }

    public function create(int $raid_num, Request $request)
    {
        $raid = VikRaid::findOrFail($raid_num);

        // Only the raid responsible may create courses (raid INS_ID is the responsable)
        $user = $request->user();
        if ((int) $raid->INS_ID !== (int) $user->INS_ID) {
            abort(403, 'Seul le responsable du raid peut créer des courses.');
        }

        // Get all adherents of the club that organizes the raid
        $responsibles = DB::table('vik_adherer')
            ->join('vik_inscrit', 'vik_inscrit.INS_ID', '=', 'vik_adherer.INS_ID')
            ->where('vik_adherer.CLU_NUM', $raid->CLU_NUM)
            ->select('vik_inscrit.INS_ID', 'vik_inscrit.INS_PRENOM', 'vik_inscrit.INS_NOM', 'vik_inscrit.INS_NUM_LICENCE')
            ->orderBy('vik_inscrit.INS_NOM')
            ->get();

        // Get course types
        $types = \App\Models\VikTypeCourse::all();

        return view('pages.courses.create', compact('raid', 'responsibles', 'types'));
    }

    public function store(int $raid_num, StoreCourseRequest $request)
    {
        $raid = VikRaid::findOrFail($raid_num);
        $user = $request->user();
        if ((int) $raid->INS_ID !== (int) $user->INS_ID) {
            abort(403, 'Seul le responsable du raid peut créer des courses.');
        }

        $data = $request->validated();
        $data['RAID_NUM'] = $raid->RAID_NUM;

        // Calculer la durée de la course en minutes (en tenant compte des horaires)
        $dateDebut = Carbon::parse($data['COU_DATE_DEPART']);
        $dateFin = Carbon::parse($data['COU_DATE_FIN']);
        $data['COU_DUREE'] = $dateDebut->diffInMinutes($dateFin);

        $max = VikRace::max('COU_NUM');
        $next = $max ? ((int) $max + 1) : 1;
        $data['COU_NUM'] = $next;

        $race = VikRace::create($data);

        return redirect()->route('race.show', $race->COU_NUM)->with('success', 'Course créée.');
    }

    public function generateDossards(int $cou_num, Request $request)
    {
        $race = VikRace::findOrFail($cou_num);
        $user = $request->user();
        if ((int) $race->INS_ID !== (int) $user->INS_ID) {
            abort(403);
        }

        $count = (int) $request->input('count', 100);
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
        if ((int) $race->INS_ID !== (int) $user->INS_ID) {
            abort(403);
        }

        $request->validate(['results' => ['required', 'file', 'mimes:csv,txt']]);
        $file = $request->file('results');
        $path = $file->storeAs('results', 'course_'.$race->COU_NUM.'_'.time().'.csv');

        return redirect()->route('race.manage', $race->COU_NUM)->with('success', 'Fichier enregistré : '.$path);
    }

    public function validateCourse(int $cou_num, Request $request)
    {
        $race = VikRace::findOrFail($cou_num);
        $user = $request->user();
        if ((int) $race->INS_ID !== (int) $user->INS_ID) {
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
        if ((int) $race->INS_ID !== (int) Auth::id()) {
            abort(403);
        }

        return view('pages.courses.edit', compact('race'));
    }

    /**
     * MODIFICATION ICI : Calcul automatique de la date de fin
     */
    public function update(int $cou_num, Request $request)
    {
        $race = VikRace::findOrFail($cou_num);
        if ((int) $race->INS_ID !== (int) Auth::id()) {
            abort(403);
        }

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
        if ((int) $race->INS_ID !== (int) Auth::id()) {
            return redirect('/')->with('error', 'Accès refusé');
        }

        $race->load([
            'equipes.participations' => function ($query) use ($cou_num) {
                $query->where('COU_NUM', $cou_num)->with('user');
            },
            'equipes.createur',
        ]);

        return view('pages.courses.manage', compact('race'));
    }

    public function togglePayment(int $cou_num, int $equ_num)
    {
        $race = VikRace::findOrFail($cou_num);
        if ((int) $race->INS_ID !== (int) Auth::id()) {
            abort(403);
        }

        $equipe = VikEquipe::where('COU_NUM', $cou_num)->where('EQU_NUM', $equ_num)->firstOrFail();
        $newState = ! $equipe->EQU_PAIEMENT_VALIDE;

        DB::table('vik_equipe')
            ->where('COU_NUM', $cou_num)
            ->where('EQU_NUM', $equ_num)
            ->update(['EQU_PAIEMENT_VALIDE' => $newState]);

        return back()->with('success', $newState ? 'Paiement validé.' : 'Paiement annulé.');
    }

    public function deleteTeam(int $cou_num, int $equ_num)
    {
        $race = VikRace::findOrFail($cou_num);
        if ((int) $race->INS_ID !== (int) Auth::id()) {
            abort(403);
        }

        DB::transaction(function () use ($cou_num, $equ_num) {
            DB::table('vik_participer')->where('COU_NUM', $cou_num)->where('EQU_NUM', $equ_num)->delete();
            DB::table('vik_equipe')->where('COU_NUM', $cou_num)->where('EQU_NUM', $equ_num)->delete();
        });

        return back()->with('success', 'Équipe supprimée.');
    }
}
