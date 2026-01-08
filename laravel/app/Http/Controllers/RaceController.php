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
use Illuminate\Support\Str;

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

        $data['COU_UTILISE_PUCE'] = $request->has('COU_UTILISE_PUCE');

        $max = VikRace::max('COU_NUM');
        $next = $max ? ((int)$max + 1) : 1000;
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
    public function organizerIndex(Request $request)
    {
        $userId = Auth::id();
        $year = $request->input('year', date('Y'));

        // 1. Get Years
        $years = VikRace::where('INS_ID', $userId)
            ->selectRaw('YEAR(COU_DATE_DEPART) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        // 2. Base Query
        $racesQuery = VikRace::where('INS_ID', $userId)
            ->with(['raid', 'equipes.participations.user']) 
            ->orderBy('COU_DATE_DEPART', 'desc');

        if ($year != 'all') {
            $racesQuery->whereYear('COU_DATE_DEPART', $year);
        }

        $races = $racesQuery->get();

        if ($races->isEmpty() && $year == date('Y') && $years->isEmpty()) {
            return redirect('/')->with('error', "Vous n'êtes responsable d'aucune course.");
        }

        // 3. Add Custom Attributes (Documents & Results Status)
        foreach ($races as $race) {
            
            // Check Documents (License or PPS)
            $isDocumentsComplete = true;
            
            // If no teams, documents are technically "complete" (nothing missing)
            if ($race->equipes->isNotEmpty()) {
                foreach ($race->equipes as $equipe) {
                    foreach ($equipe->participations as $part) {
                        $user = $part->user;
                        $hasDoc = !empty($user->INS_NUM_LICENCE) || !empty($part->PAR_NUM_PPS);
                        
                        if (!$hasDoc) {
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
        $racesWaitingForResults = $pastRacesAll->filter(function($race) {
            // Logic: Past AND (Results missing OR Documents missing) AND has teams
            return $race->equipes->count() > 0 && (!$race->results_complete || !$race->documents_complete);
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
        
        // Responsible Check Race
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
            'COU_UTILISE_PUCE' => 'nullable',
        ]);

        // --- AUTOMATIC COMPUTING LOGIC ---
        $dateDepart = Carbon::parse($validated['COU_DATE_DEPART']);
        $dureeMinutes = (int) $validated['COU_DUREE'];
        $dateFin = $dateDepart->copy()->addMinutes($dureeMinutes);
        $validated['COU_DATE_FIN'] = $dateFin;
        // ---------------------------------

        $validated['COU_UTILISE_PUCE'] = $request->has('COU_UTILISE_PUCE');

        $race->update($validated);

        return redirect()->route('race.organizer_index')->with('success', 'Course mise à jour.');
    }

    /*
        Function used for managing teams in a race
        Displays teams, team members, and their status (whether paid or not).
    */
    public function manage(int $cou_num, Request $request)
    {
        $race = VikRace::findOrFail($cou_num);
        if ((int)$race->INS_ID !== (int)Auth::id()) return redirect('/')->with('error', 'Accès refusé');

        // Load teams with members (users) and PPS info from pivot table
        $race->load([
            'equipes.participations' => function($query) use ($cou_num) {
                $query->where('COU_NUM', $cou_num)->with('user');
            },
            'equipes.createur'
        ]);

        // Sort teams by ranking (if available) then by name
        $sortedEquipes = $race->equipes->sortBy(function($equipe) {
            // Sort logic: Ranked teams first (asc), then Unranked teams, then by name
            if ($equipe->EQU_ORDRE_ARRIVEE) return $equipe->EQU_ORDRE_ARRIVEE;
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

    // Mettre à jour le PPS d'un membre
    public function updatePps(Request $request, int $cou_num, int $equ_num, int $ins_id)
    {
        $race = VikRace::findOrFail($cou_num);
        if ((int)$race->INS_ID !== (int)Auth::id()) abort(403);
        
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
        if ((int)$race->INS_ID !== (int)Auth::id()) abort(403);
        
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
            'PAR_NUM_PPS' => null
        ]);
        
        return back()->with('success', 'Membre ajouté avec succès.');
    }

    // Supprimer un membre d'une équipe
    public function removeTeamMember(int $cou_num, int $equ_num, int $ins_id)
    {
        $race = VikRace::findOrFail($cou_num);
        if ((int)$race->INS_ID !== (int)Auth::id()) abort(403);
        
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
        if ((int)$race->INS_ID !== (int)Auth::id()) abort(403);

        $equipes = VikEquipe::where('COU_NUM', $cou_num)
            ->orderBy('EQU_ORDRE_ARRIVEE', 'asc') // Order by rank
            ->get();

        $csvFileName = 'resultats_' . Str::slug($race->COU_NOM) . '_' . date('Y-m-d') . '.csv';
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Classement', 'Nom Equipe', 'Temps (min)', 'Points', 'Statut Paiement'];

        $callback = function() use($equipes, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns, ';'); // Excel prefers semicolon in Europe

            foreach ($equipes as $equipe) {
                fputcsv($file, [
                    $equipe->EQU_ORDRE_ARRIVEE ?? 'Non classé',
                    $equipe->EQU_NOM,
                    $equipe->EQU_TEMPS ?? '-',
                    $equipe->EQU_POINTS ?? 0,
                    $equipe->EQU_PAIEMENT_VALIDE ? 'Payé' : 'Non Payé'
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function uploadResults(int $cou_num, Request $request)
    {
        $race = VikRace::findOrFail($cou_num);
        if ((int)$race->INS_ID !== (int)Auth::id()) abort(403);

        $request->validate([
            'results' => ['required', 'file', 'mimes:csv,txt']
        ]);

        $file = $request->file('results');
        
        // Ouvrir le fichier
        if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
            // Lire la première ligne (En-tête) pour l'ignorer
            fgetcsv($handle, 1000, ";");

            $imported = 0;
            $errors = 0;

            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                // Structure: [0]=>CLT, [1]=>PUCE, [2]=>EQUIPE, [3]=>TEMPS, [4]=>PTS
                
                // Vérification basique de la ligne
                if (count($data) < 5) continue;

                $rank = (int)$data[0];
                $teamName = trim(utf8_encode($data[2])); // utf8_encode si le CSV vient d'Excel (Windows-1252)
                $timeStr = $data[3];
                $points = (int)$data[4];

                // Conversion du temps (HH:MM:SS) en minutes décimales
                // Gestion du cas "-6:06:12" (si c'est un temps négatif ou erreur, on prend la valeur absolue)
                $timeStr = ltrim($timeStr, '-'); 
                $parts = explode(':', $timeStr);
                
                $minutes = null;
                if (count($parts) >= 2) {
                    $hours = (int)$parts[0];
                    $mins = (int)$parts[1];
                    $secs = isset($parts[2]) ? (int)$parts[2] : 0;
                    
                    $minutes = ($hours * 60) + $mins + ($secs / 60);
                    $minutes = round($minutes, 2); // 2 décimales
                }

                // Trouver l'équipe par son nom dans cette course
                $equipe = VikEquipe::where('COU_NUM', $cou_num)
                    ->where('EQU_NOM', $teamName) // Attention à la casse exacte
                    ->first();

                if ($equipe) {
                    // Mise à jour
                    DB::table('vik_equipe')
                        ->where('COU_NUM', $cou_num)
                        ->where('EQU_NUM', $equipe->EQU_NUM)
                        ->update([
                            'EQU_ORDRE_ARRIVEE' => $rank,
                            'EQU_TEMPS' => $minutes,
                            'EQU_POINTS' => $points
                        ]);
                    $imported++;
                } else {
                    $errors++; // Équipe introuvable
                }
            }
            fclose($handle);

            $msg = "Import terminé. $imported équipes mises à jour.";
            if ($errors > 0) $msg .= " ($errors équipes non trouvées - vérifiez les noms).";
            
            return redirect()->route('race.manage', $race->COU_NUM)->with('success', $msg);
        }

        return back()->with('error', 'Impossible de lire le fichier.');
    }

    public function searchUser(Request $request)
    {
        if (!Auth::check()) return response()->json([], 401);

        $query = $request->input('q');
        if (strlen($query) < 2) return response()->json([]);

        $users = User::where('INS_NOM', 'LIKE', "%{$query}%")
                    ->orWhere('INS_PRENOM', 'LIKE', "%{$query}%")
                    ->limit(10)
                    ->get(['INS_ID', 'INS_NOM', 'INS_PRENOM', 'INS_MAIL', 'INS_NUM_LICENCE']);

        return response()->json($users);
    }


}
