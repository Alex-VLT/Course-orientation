<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\User;
use App\Models\VikEquipe;
use App\Models\VikRace;
use App\Models\VikRaid;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // For DateTime
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RaceController extends Controller
{
    protected function currentUserCanManageRace(VikRace $race): bool
    {
        $userId = (int) Auth::id();

        if ((int) $race->INS_ID === $userId) {
            return true;
        }

        $raidResponsableId = (int) ($race->raid?->INS_ID ?? 0);

        return $raidResponsableId !== 0 && $raidResponsableId === $userId;
    }

    public function show(int $race_num)
    {
        $race = VikRace::query()
            ->with(['raid', 'acceptances.tranche'])
            ->findOrFail($race_num);

        $teamsCount = DB::table('VIK_EQUIPE')->where('COU_NUM', $race_num)->count();
        $isRegistered = false;
        $isTeamLeader = false;
        $userTeamNum = null;
        if (Auth::check()) {
            $current = Auth::user();

            $participation = DB::table('VIK_PARTICIPER')
                ->where('COU_NUM', $race_num)
                ->where('INS_ID', $current->INS_ID)
                ->first();

            $isRegistered = (bool) $participation;
            $userTeamNum = $participation->EQU_NUM ?? null;

            if (! empty($userTeamNum)) {
                $team = DB::table('VIK_EQUIPE')
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

    public function classement(int $cou_num)
    {
        $race = VikRace::query()
            ->with(['equipes'])
            ->findOrFail($cou_num);

        $equipes = $race->equipes
            ->sort(function ($a, $b) {
                $aPoints = (int) ($a->EQU_POINTS ?? 0);
                $bPoints = (int) ($b->EQU_POINTS ?? 0);

                $pointsCompare = $bPoints <=> $aPoints;
                if ($pointsCompare !== 0) {
                    return $pointsCompare;
                }

                $aTime = (! empty($a->EQU_TEMPS) && (int) $a->EQU_TEMPS > 0) ? (int) $a->EQU_TEMPS : PHP_INT_MAX;
                $bTime = (! empty($b->EQU_TEMPS) && (int) $b->EQU_TEMPS > 0) ? (int) $b->EQU_TEMPS : PHP_INT_MAX;

                $timeCompare = $aTime <=> $bTime;
                if ($timeCompare !== 0) {
                    return $timeCompare;
                }

                $aName = mb_strtolower((string) ($a->EQU_NOM ?? ''), 'UTF-8');
                $bName = mb_strtolower((string) ($b->EQU_NOM ?? ''), 'UTF-8');

                return $aName <=> $bName;
            })
            ->values();

        $resultsPublished = $equipes->contains(function ($equipe) {
            return ! empty($equipe->EQU_TEMPS) && (int) $equipe->EQU_TEMPS > 0;
        });

        return view('pages.courses.classement', compact('race', 'equipes', 'resultsPublished'));
    }

    /**
     * Unsubscribe the currently authenticated user from a course (their own participation only).
     */
    public function unsubscribeParticipant(int $cou_num)
    {
        $user = Auth::user();
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

    /**
     * Show the form for creating a new course for a given raid.
     * 
     * Only the raid responsible can create courses for their raid.
     * The form will display all licensed members (with INS_NUM_LICENCE) 
     * of the organizing club as potential course responsibles.
     *
     * @param int $raid_num The raid identifier
     * @param Request $request The HTTP request
     * @return \Illuminate\View\View
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If raid not found
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException 403 if user is not the raid responsible
     */
    public function create(int $raid_num, Request $request)
    {
        $raid = VikRaid::findOrFail($raid_num);

        // Only the raid responsible may create courses (raid INS_ID is the responsable)
        $user = $request->user();
        if ((int) $raid->INS_ID !== (int) $user->INS_ID) {
            abort(403, 'Seul le responsable du raid peut créer des courses.');
        }

        // Get all licenciés (members with license) of the club that organizes the raid
        $responsibles = DB::table('VIK_ADHERER')
            ->join('VIK_INSCRIT', 'VIK_INSCRIT.INS_ID', '=', 'VIK_ADHERER.INS_ID')
            ->where('VIK_ADHERER.CLU_NUM', $raid->CLU_NUM)
            ->whereNotNull('VIK_INSCRIT.INS_NUM_LICENCE')
            ->where('VIK_INSCRIT.INS_NUM_LICENCE', '!=', '')
            ->select('VIK_INSCRIT.INS_ID', 'VIK_INSCRIT.INS_PRENOM', 'VIK_INSCRIT.INS_NOM', 'VIK_INSCRIT.INS_NUM_LICENCE')
            ->orderBy('VIK_INSCRIT.INS_NOM')
            ->get();

        // Get course types
        $types = \App\Models\VikTypeCourse::all();

        return view('pages.courses.create', compact('raid', 'responsibles', 'types'));
    }

    /**
     * Store a newly created course in the database.
     * 
     * Validates the course data via StoreCourseRequest, generates a new COU_NUM,
     * and creates the course record. Only the raid responsible can create courses.
     *
     * @param int $raid_num The raid identifier
     * @param StoreCourseRequest $request The validated form request
     * @return \Illuminate\Http\RedirectResponse Redirects to the course detail page
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If raid not found
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException 403 if user is not the raid responsible
     */
    public function store(int $raid_num, StoreCourseRequest $request)
    {
        $raid = VikRaid::findOrFail($raid_num);
        $user = $request->user();

        if ((int) $raid->INS_ID !== (int) $user->INS_ID) {
            abort(403, 'Seul le responsable du raid peut créer des courses.');
        }

        // Get validated data and add raid reference
        $data = $request->validated();
        $data['RAID_NUM'] = $raid->RAID_NUM;

        // Generate next COU_NUM (start at 1000 if no courses exist)
        $max = VikRace::max('COU_NUM');
        $next = $max ? ((int) $max + 1) : 1000;
        $data['COU_NUM'] = $next;

        // Create the course
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

    /*
        Function to load races from the
        organizer page index.blade.php
    */
    public function organizerIndex(Request $request)
    {
        $userId = Auth::id();
        $currentYear = (int) now()->year;
        $yearInput = $request->input('year');
        $year = is_numeric($yearInput) ? (int) $yearInput : $currentYear;

        // 1. Get Years
        $years = VikRace::query()
            ->where('INS_ID', $userId)
            ->whereNotNull('COU_DATE_DEPART')
            ->get(['COU_DATE_DEPART'])
            ->map(function (VikRace $race) {
                $date = $race->COU_DATE_DEPART;

                return $date ? Carbon::parse($date)->year : null;
            })
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        // 2. Base Query
        $racesQuery = VikRace::where('INS_ID', $userId)
            ->with(['raid', 'equipes.participations.user'])
            ->orderBy('COU_DATE_DEPART', 'desc');

        $racesQuery->whereYear('COU_DATE_DEPART', $year);

        $races = $racesQuery->get();

        if ($races->isEmpty() && $year === $currentYear && $years->isEmpty()) {
            return redirect('/')->with('error', "Vous n'êtes responsable d'aucune course.");
        }

        // 3. Add Custom Attributes (Documents & Results Status)
        foreach ($races as $race) {

            // Check Documents (License or PPS) and team payments
            $isDocumentsComplete = true;

            // If no teams, documents are technically "complete" (nothing missing)
            if ($race->equipes->isNotEmpty()) {
                foreach ($race->equipes as $equipe) {
                    // 1) Each team must have a validated payment
                    if (empty($equipe->EQU_PAIEMENT_VALIDE) || ! $equipe->EQU_PAIEMENT_VALIDE) {
                        $isDocumentsComplete = false;
                        break; // stop at first unpaid team
                    }

                    // 2) Only check participations belonging to this race (avoid mixing teams from other races with same EQU_NUM)
                    $participations = $equipe->participations->filter(function ($p) use ($race) {
                        return isset($p->COU_NUM) && intval($p->COU_NUM) === intval($race->COU_NUM);
                    });

                    foreach ($participations as $part) {
                        $user = $part->user;
                        $hasDoc = ! empty($user->INS_NUM_LICENCE) || ! empty($part->PAR_NUM_PPS);

                        if (! $hasDoc) {
                            $isDocumentsComplete = false;
                            break 2;
                        }
                    }
                }
            }
            $race->documents_complete = $isDocumentsComplete;

            // Check Results (All teams have a rank)
            $isResultsComplete = true;
            if ($race->equipes->isEmpty()) {
                $isResultsComplete = false; // No teams = No results
            } else {
                foreach ($race->equipes as $equipe) {
                    if (is_null($equipe->EQU_ORDRE_ARRIVEE)) {
                        $isResultsComplete = false;
                        break;
                    }
                }
            }
            $race->results_complete = $isResultsComplete;
        }

        $now = Carbon::now();

        // 4. Categorize Races
        $upcomingRaces = $races->where('COU_DATE_DEPART', '>=', $now);
        $pastRacesAll = $races->where('COU_DATE_DEPART', '<', $now);

        // Filter: Races waiting for action (Results OR Documents missing)
        $racesWaitingForResults = $pastRacesAll->filter(function ($race) {
            // Logic: Past AND (Results missing OR Documents missing) AND has teams
            return $race->equipes->count() > 0 && (! $race->results_complete || ! $race->documents_complete);
        });

        // Filter: Completed Races (Everything is OK)
        $racesWithResults = $pastRacesAll->diff($racesWaitingForResults);

        return view('pages.courses.organizer_index', compact(
            'upcomingRaces',
            'racesWaitingForResults',
            'racesWithResults',
            'years',
            'year'
        ));
    }

    /**
     * Show the form for editing an existing course.
     * 
     * Only the course responsible can edit their course.
     *
     * @param int $cou_num The course identifier
     * @return \Illuminate\View\View
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If course not found
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException 403 if user is not the course responsible
     */
    public function edit(int $cou_num)
    {
        $race = VikRace::query()->with('raid')->findOrFail($cou_num);
        if (! $this->currentUserCanManageRace($race)) {
            abort(403);
        }

        return view('pages.courses.edit', compact('race'));
    }

    /**
     * Update an existing course in the database.
     * 
     * Validates the course data via UpdateCourseRequest and updates the course record.
     * Only the course responsible can update their course.
     *
     * @param int $cou_num The course identifier
     * @param UpdateCourseRequest $request The validated form request
     * @return \Illuminate\Http\RedirectResponse Redirects to the organizer course index
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If course not found
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException 403 if user is not the course responsible
     */
    public function update(int $cou_num, UpdateCourseRequest $request)
    {
        $race = VikRace::query()->with('raid')->findOrFail($cou_num);

        if (! $this->currentUserCanManageRace($race)) {
            abort(403);
        }

        $race->update($request->validated());

        return redirect()->route('race.organizer_index')->with('success', 'Course mise à jour.');
    }    /*
        Function used for managing teams in a race
        Displays teams, team members, and their status (whether paid or not).
    */
    public function manage(int $cou_num, Request $request)
    {
        $race = VikRace::findOrFail($cou_num);
        if ((int) $race->INS_ID !== (int) Auth::id()) {
            return redirect('/')->with('error', 'Accès refusé');
        }

        // Load teams with members (users) and PPS info from pivot table
        $race->load([
            'equipes.participations' => function ($query) use ($cou_num) {
                $query->where('COU_NUM', $cou_num)->with('user');
            },
            'equipes.createur',
        ]);

        // Sort teams by ranking (if available) then by name
        $sortedEquipes = $race->equipes->sortBy(function ($equipe) {
            // Sort logic: Ranked teams first (asc), then Unranked teams, then by name
            if ($equipe->EQU_ORDRE_ARRIVEE) {
                return $equipe->EQU_ORDRE_ARRIVEE;
            }

            return 999999;
        });

        // We replace the relation collection with the sorted one for the view
        $race->setRelation('equipes', $sortedEquipes);

        return view('pages.courses.manage', compact('race'));
    }

    /*
        To change a team's payment status,
        only the manager can change this status.
    */
    public function togglePayment(int $cou_num, int $equ_num)
    {
        $race = VikRace::findOrFail($cou_num);
        if ((int) $race->INS_ID !== (int) Auth::id()) {
            abort(403);
        }

        // Team recovery
        $equipe = VikEquipe::where('COU_NUM', $cou_num)->where('EQU_NUM', $equ_num)->firstOrFail();
        $newState = ! $equipe->EQU_PAIEMENT_VALIDE;

        // Change of status
        DB::table('vik_equipe')
            ->where('COU_NUM', $cou_num)
            ->where('EQU_NUM', $equ_num)
            ->update(['EQU_PAIEMENT_VALIDE' => $newState]);

        return back()->with('success', $newState ? 'Paiement validé.' : 'Paiement annulé.');
    }

    /*
        Function to remove a team from a race
    */
    public function deleteTeam(int $cou_num, int $equ_num)
    {
        $race = VikRace::findOrFail($cou_num);
        if ((int) $race->INS_ID !== (int) Auth::id()) {
            abort(403);
        }

        // Using transactions to remove all data related to a team from all tables
        DB::transaction(function () use ($cou_num, $equ_num) {
            DB::table('vik_participer')->where('COU_NUM', $cou_num)->where('EQU_NUM', $equ_num)->delete();
            DB::table('vik_equipe')->where('COU_NUM', $cou_num)->where('EQU_NUM', $equ_num)->delete();
        });

        return back()->with('success', 'Équipe supprimée.');
    }

    // Mettre à jour le PPS d'un membre
    public function updatePps(Request $request, int $cou_num, int $equ_num, int $ins_id)
    {
        $race = VikRace::findOrFail($cou_num);
        if ((int) $race->INS_ID !== (int) Auth::id()) {
            abort(403);
        }

        $request->validate(['pps' => 'required|string|max:64']);

        DB::table('vik_participer')
            ->where('COU_NUM', $cou_num)
            ->where('EQU_NUM', $equ_num)
            ->where('INS_ID', $ins_id)
            ->update(['PAR_NUM_PPS' => $request->pps]);

        return back()->with('success', 'Numéro PPS mis à jour.');
    }

    // Ajouter un membre à une équipe (Recherche par email)
    public function addTeamMember(Request $request, int $cou_num, int $equ_num)
    {
        $race = VikRace::findOrFail($cou_num);
        if ((int) $race->INS_ID !== (int) Auth::id()) {
            abort(403);
        }

        $request->validate(['ins_id' => 'required|integer|exists:vik_inscrit,INS_ID']);

        $userId = $request->input('ins_id');

        // 1. Vérif doublon
        $alreadyRegistered = DB::table('vik_participer')
            ->where('COU_NUM', $cou_num)
            ->where('INS_ID', $userId)
            ->exists();

        if ($alreadyRegistered) {
            return back()->with('error', 'Ce membre participe déjà à cette course.');
        }

        // 2. Vérif taille équipe
        $currentCount = DB::table('vik_participer')->where('COU_NUM', $cou_num)->where('EQU_NUM', $equ_num)->count();
        if ($currentCount >= $race->COU_PART_PAR_EQU_MAX) {
            return back()->with('error', 'L\'équipe est complète.');
        }

        // 3. Insertion
        DB::table('vik_participer')->insert([
            'INS_ID' => $userId,
            'COU_NUM' => $cou_num,
            'EQU_NUM' => $equ_num,
            'PAR_PARTICIPE' => 0,
            'PAR_NUM_PPS' => null,
        ]);

        return back()->with('success', 'Membre ajouté avec succès.');
    }

    // Supprimer un membre d'une équipe
    public function removeTeamMember(int $cou_num, int $equ_num, int $ins_id)
    {
        $race = VikRace::findOrFail($cou_num);
        if ((int) $race->INS_ID !== (int) Auth::id()) {
            abort(403);
        }

        // Prevent deleting the team creator? (Optional logic)
        // For now, we allow it.

        DB::table('vik_participer')
            ->where('COU_NUM', $cou_num)
            ->where('EQU_NUM', $equ_num)
            ->where('INS_ID', $ins_id)
            ->delete();

        return back()->with('success', 'Membre retiré de l\'équipe.');
    }

    public function exportResults(int $cou_num)
    {
        $race = VikRace::findOrFail($cou_num);
        if ((int) $race->INS_ID !== (int) Auth::id()) {
            abort(403);
        }

        $equipes = VikEquipe::where('COU_NUM', $cou_num)
            ->orderBy('EQU_ORDRE_ARRIVEE', 'asc') // Order by rank
            ->get();

        $csvFileName = 'resultats_'.Str::slug($race->COU_NOM).'_'.date('Y-m-d').'.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$csvFileName",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = ['Classement', 'Nom Equipe', 'Temps (min)', 'Points', 'Statut Paiement'];

        $callback = function () use ($equipes, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns, ';'); // Excel prefers semicolon in Europe

            foreach ($equipes as $equipe) {
                fputcsv($file, [
                    $equipe->EQU_ORDRE_ARRIVEE ?? 'Non classé',
                    $equipe->EQU_NOM,
                    $equipe->EQU_TEMPS ?? '-',
                    $equipe->EQU_POINTS ?? 0,
                    $equipe->EQU_PAIEMENT_VALIDE ? 'Payé' : 'Non Payé',
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function uploadResults(int $cou_num, Request $request)
    {
        $race = VikRace::findOrFail($cou_num);
        if ((int) $race->INS_ID !== (int) Auth::id()) {
            abort(403);
        }

        $request->validate([
            'results' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $file = $request->file('results');

        // Ouvrir le fichier
        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            // Lire la première ligne (En-tête) et détecter les colonnes (tolérant sur le format)
            $header = fgetcsv($handle, 1000, ';');

            // Par défaut, on suppose les colonnes: 0=CLT, 1=PUCE, 2=EQUIPE, 3=TEMPS, 4=PTS
            $teamCol = 2;
            $timeCol = 3;
            $pointsCol = 4;
            if (is_array($header)) {
                $lc = array_map(function ($h) {
                    return mb_strtolower(trim((string) $h), 'UTF-8');
                }, $header);
                foreach ($lc as $i => $h) {
                    if (mb_strpos($h, 'equipe') !== false || mb_strpos($h, 'nom') !== false || mb_strpos($h, 'team') !== false) {
                        $teamCol = $i;
                    }
                    if (mb_strpos($h, 'temps') !== false || mb_strpos($h, 'time') !== false) {
                        $timeCol = $i;
                    }
                    if (mb_strpos($h, 'pts') !== false || mb_strpos($h, 'point') !== false) {
                        $pointsCol = $i;
                    }
                }
            }

            $imported = 0;
            $errors = 0;
            $notFound = []; // Collect names not found for debugging

            while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                // At least we need a team column and rank
                if (! isset($data[0]) || ! isset($data[$teamCol])) {
                    continue;
                }

                $rank = (int) ($data[0] ?? 0);
                $rawName = $data[$teamCol] ?? '';
                // Normalize CSV team name (trim, collapse multiple whitespace)
                $teamName = trim(preg_replace('/\s+/u', ' ', $rawName));

                $timeStr = $data[$timeCol] ?? '';
                $points = isset($data[$pointsCol]) ? (int) $data[$pointsCol] : 0;

                // Conversion du temps (HH:MM:SS) en minutes décimales
                // Gestion du cas "-6:06:12" (si c'est un temps négatif ou erreur, on prend la valeur absolue)
                $timeStr = ltrim($timeStr, '-');
                $parts = explode(':', $timeStr);

                $minutes = null;
                if (count($parts) >= 2) {
                    $hours = (int) $parts[0];
                    $mins = (int) $parts[1];
                    $secs = isset($parts[2]) ? (int) $parts[2] : 0;

                    $minutes = ($hours * 60) + $mins + ($secs / 60);
                    $minutes = round($minutes, 2); // 2 décimales
                }

                // Normalize for comparison
                $teamNameLower = mb_strtolower($teamName, 'UTF-8');

                // 1) Try exact, case-insensitive match
                $equipe = VikEquipe::where('COU_NUM', $cou_num)
                    ->whereRaw('LOWER(TRIM(EQU_NOM)) = ?', [$teamNameLower])
                    ->first();

                // 2) Fallback: contains match (case-insensitive)
                if (! $equipe) {
                    $equipe = VikEquipe::where('COU_NUM', $cou_num)
                        ->whereRaw('LOWER(EQU_NOM) LIKE ?', ['%'.str_replace('%', '\\%', $teamNameLower).'%'])
                        ->first();
                }

                if ($equipe) {
                    // Mise à jour
                    DB::table('vik_equipe')
                        ->where('COU_NUM', $cou_num)
                        ->where('EQU_NUM', $equipe->EQU_NUM)
                        ->update([
                            'EQU_ORDRE_ARRIVEE' => $rank,
                            'EQU_TEMPS' => $minutes,
                            'EQU_POINTS' => $points,
                        ]);
                    $imported++;
                } else {
                    $errors++; // Équipe introuvable
                    $notFound[] = $teamName;
                }
            }
            fclose($handle);

            $msg = "Import terminé. $imported équipes mises à jour.";
            if ($errors > 0) {
                $shortList = array_slice($notFound, 0, 10);
                $msg .= " ($errors équipes non trouvées - vérifiez les noms: ".implode(', ', $shortList).(count($notFound) > 10 ? ', ...' : '').')';
            }

            return redirect()->route('race.manage', $race->COU_NUM)->with('success', $msg);
        }

        return back()->with('error', 'Impossible de lire le fichier.');
    }

    public function searchUser(Request $request)
    {
        if (! Auth::check()) {
            return response()->json([], 401);
        }

        $query = $request->input('q');
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::where('INS_NOM', 'LIKE', "%{$query}%")
            ->orWhere('INS_PRENOM', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get(['INS_ID', 'INS_NOM', 'INS_PRENOM', 'INS_MAIL', 'INS_NUM_LICENCE']);

        return response()->json($users);
    }
}
