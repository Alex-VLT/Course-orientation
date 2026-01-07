<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Team;
use App\Models\User;
use App\Models\VerifInscription;

class inscFormController extends Controller
{
    public function showForm(Request $request)
    {
        // If a course is provided in the query string, fetch its team size limit to
        // allow the frontend to disable the "Ajouter un coureur" button when reached.
        $courseNum = $request->query('course');
        $teamMax = null;
        if (! empty($courseNum) && is_numeric($courseNum)) {
            $course = VerifInscription::fetchCourse((int) $courseNum);
            if ($course) {
                $teamMax = $course->COU_PART_PAR_EQU_MAX ?? null;
            }
        }

        return view('pages.inscForm', [
            'team_max' => $teamMax,
            'course_num' => $courseNum,
        ]);
    }

    public function submitForm(Request $request)
    {
        // Accept either 'coureurs' (older) or 'people' (view) formats. Keep validation permissive and handle mapping.
        // log raw input early to help debugging what the browser actually sent
        $rawPayload = ['input' => $request->all(), 'query' => $request->query()];
        logger()->debug('submitForm raw request', $rawPayload);

        // If caller requested an immediate echo for debugging (POST field or ?debug_echo=1),
        // return the raw payload as JSON so it's visible in the browser instead of only in logs.
        if ($request->boolean('debug_echo') || $request->has('debug_echo')) {
            return response()->json($rawPayload);
        }

    $validated = $request->validate([
            'team_name' => 'required|string|max:255',
            // either people[...] or coureurs[...] arrays
            'people' => 'sometimes|array|min:1',
            'people.*.firstname' => 'sometimes|required_with:people|string|max:255',
            'people.*.name' => 'sometimes|required_with:people|string|max:255',
            'people.*.email' => 'sometimes|nullable|email',
        ]);

        $authUser = auth()->user();
        if (!$authUser) {
            return back()->withErrors(['msg' => 'Vous devez être connecté pour créer une équipe.']);
        }

        // récupérer l'email de l'utilisateur authentifié puis retrouver INS_ID depuis vik_inscrit
        $chefEmail = $authUser->INS_MAIL ?? ($authUser->email ?? null);
        if (empty($chefEmail)) {
            return back()->withErrors(['msg' => 'Impossible de déterminer l\'email de l\'utilisateur connecté.']);
        }

        $chefRecord = User::where('INS_MAIL', $chefEmail)->first();
        if (!$chefRecord) {
            return back()->withErrors(['msg' => 'Aucun inscrit trouvé pour l\'email du chef connecté.']);
        }

        $chefId = $chefRecord->INS_ID;

    // Le chef participe-t-il ? (on lit la checkbox tôt pour les pré-validations)
    $chefParticipates = $request->has('participation') || $request->boolean('participation');

            // récupérer le numéro de course : d'abord depuis le POST (hidden input), sinon depuis la query string
            $courseNum = $request->input('course') ?? $request->query('course');
            if (empty($courseNum) || !is_numeric($courseNum)) {
                return back()->withErrors(['msg' => 'Numéro de course manquant ou invalide.']);
            }
            $courseNum = (int) $courseNum;

            // calculer EQU_NUM : incrementer le numéro d'équipe pour cette course (pré-calcul sans insert)
            $maxEq = DB::table('vik_equipe')->where('COU_NUM', $courseNum)->max('EQU_NUM');
            $newEquNum = $maxEq ? ((int)$maxEq + 1) : 1;

            // --- Pré-validations en mémoire en utilisant VerifInscription (évite insert+rollback)
            $courseObj = VerifInscription::fetchCourse($courseNum);
            if (! $courseObj) {
                return back()->withErrors(['msg' => 'Course introuvable.']);
            }

            // Normaliser members list (comme plus bas)
            $members = [];
            if ($request->has('people')) {
                foreach ($request->input('people') as $p) {
                    $members[] = [
                        'prenom' => $p['firstname'] ?? null,
                        'nom' => $p['name'] ?? null,
                        'email' => $p['email'] ?? null,
                        'licence' => $p['licence'] ?? null,
                    ];
                }
            } elseif ($request->has('coureurs')) {
                foreach ($request->input('coureurs') as $c) {
                    $members[] = [
                        'prenom' => $c['prenom'] ?? null,
                        'nom' => $c['nom'] ?? null,
                        'email' => $c['email'] ?? null,
                        'licence' => $c['licence'] ?? null,
                    ];
                }
            }

            // resolve existing users for all members (no creation)
            $resolvedMembers = collect();
            foreach ($members as $member) {
                $user = null;
                if (!empty($member['email'])) {
                    $user = User::where('INS_MAIL', $member['email'])->first();
                }
                if (! $user && !empty($member['prenom']) && !empty($member['nom'])) {
                    $user = User::where('INS_PRENOM', $member['prenom'])->where('INS_NOM', $member['nom'])->first();
                }
                if (! $user) {
                    return back()->withErrors(['msg' => 'Inscrit introuvable pour ' . ($member['prenom'] ?? '') . ' ' . ($member['nom'] ?? '') . ". Veuillez l'enregistrer d'abord."]);
                }
                $resolvedMembers->push((object)['INS_ID' => $user->INS_ID]);
            }

            // Vérifier s'il y a des duplicata parmi les membres fournis (même INS_ID plusieurs fois)
            $idCounts = $resolvedMembers->pluck('INS_ID')->countBy()->filter(function($c){ return $c > 1; });
            if ($idCounts->isNotEmpty()) {
                $dupIds = $idCounts->keys()->values()->all();
                logger()->warning('Duplicata détecté dans les membres fournis', ['dup_ids' => $dupIds]);
                return back()->withErrors(['msg' => 'Duplication détectée : un même coureur est présent plusieurs fois dans la liste.']);
            }

            // Si le chef participe et qu'il est aussi présent dans la liste des membres, c'est une duplication
            if ($chefParticipates && $resolvedMembers->pluck('INS_ID')->contains($chefId)) {
                return back()->withErrors(['msg' => 'Le chef est déjà ajouté comme coureur : un même utilisateur ne peut pas figurer plusieurs fois.']);
            }

            // Construire la collection des participations telle qu'elle serait après insertion
            $existingParticipations = VerifInscription::fetchParticipationsForCourse($courseNum);
            $simParticipations = $existingParticipations->map(function($p){
                // normaliser clés
                return (object)[
                    'ins_id' => $p->INS_ID ?? $p->ins_id ?? null,
                    'equ_num' => $p->EQU_NUM ?? $p->equ_num ?? null,
                ];
            });
            foreach ($resolvedMembers as $m) {
                $simParticipations->push((object)['ins_id' => $m->INS_ID, 'equ_num' => $newEquNum]);
            }

            // Construire collection teamMembers (objets avec INS_ID) pour validateAge
            // on part des membres résolus ; si le chef participe on l'ajoute ensuite
            $teamMembersForValidation = $resolvedMembers;

            // si le chef participe, l'ajouter aux participations simulées et aux membres d'équipe pour validation
            if ($chefParticipates) {
                $simParticipations->push((object)['ins_id' => $chefId, 'equ_num' => $newEquNum]);
                $teamMembersForValidation->push((object)['INS_ID' => $chefId]);
            }

            // Appeler les validateurs en mémoire
            $verifier = new VerifInscriptionController();
            $resultNb = $verifier->validateNbParticipants($courseObj, $simParticipations, $teamMembersForValidation);
            $resultAge = $verifier->validateAge($courseObj, $teamMembersForValidation);

            $messages = array_merge($resultNb['messages'] ?? [], $resultAge['messages'] ?? []);
            // il faut au moins un coureur inscrit : soit des membres ajoutés, soit le chef qui participe
            if ($resolvedMembers->isEmpty() && ! $chefParticipates) {
                $messages[] = 'Vous devez ajouter au moins un coureur ou cocher "Je participe" pour inclure le chef.';
            }
            if (!empty($messages)) {
                // Si l'erreur vient de l'âge, construire un détail JSON similaire à ce que fournit
                // la route /validate-equipe pour faciliter le debug côté front.
                $validationPayload = [
                    'ok' => false,
                    'messages' => $messages,
                    'details' => [],
                ];

                // Si validateAge a retourné des messages, enrichir par coureur
                if (!empty($resultAge['messages'])) {
                    $ageDetails = [];
                    $startDate = $courseObj->COU_DATE_DEPART ?? null;
                    foreach ($teamMembersForValidation as $tm) {
                        $ins = VerifInscription::fetchInscritById($tm->INS_ID);
                        if (! $ins) {
                            $ageDetails[] = ['INS_ID' => $tm->INS_ID, 'ok' => false, 'reason' => 'Inscrit introuvable'];
                            continue;
                        }
                        $age = VerifInscription::getAgeAtDate($ins->INS_NAISSANCE ?? '', $startDate ?? '');
                        $ageDetails[] = [
                            'INS_ID' => $ins->INS_ID,
                            'nom' => $ins->INS_NOM ?? null,
                            'prenom' => $ins->INS_PRENOM ?? null,
                            'naissance' => $ins->INS_NAISSANCE ?? null,
                            'age_at_start' => $age,
                        ];
                    }
                    $validationPayload['details']['age'] = $ageDetails;
                }

                // retourner les messages d'erreur sans insérer, et flasher le payload détaillé pour debug côté UI
                return back()->withErrors(['msg' => implode(' | ', $messages)])->with('validation_json', $validationPayload);
            }

            // Toutes les pré-validations ont réussi — on peut commencer la transaction et insérer
            DB::beginTransaction();
            try {

            // Re-check team count inside the transaction to avoid race condition where
            // multiple submissions compute the same newEquNum concurrently.
            if (! is_null($courseObj->COU_NB_EQU_MAX)) {
                $currentTeamsCount = DB::table('vik_equipe')
                    ->where('COU_NUM', $courseNum)
                    ->distinct()
                    ->count('EQU_NUM');
                if ($currentTeamsCount >= $courseObj->COU_NB_EQU_MAX) {
                    throw new \RuntimeException('Nombre maximum d\'équipes atteint pour cette course.');
                }
            }

            $teamData = [
                'COU_NUM' => $courseNum,
                'EQU_NUM' => $newEquNum,
                'INS_ID' => $chefId,
                'EQU_NOM' => $request->input('team_name'),
                'EQU_ORDRE_ARRIVEE' => null,
                'EQU_TEMPS' => null,
                'EQU_POINTS' => null,
            ];

            logger()->debug('Insertion vik_equipe payload', ['data' => $teamData]);

            // Insérer l'équipe (EQU_NUM calculé manuellement)
            DB::table('vik_equipe')->insert($teamData);

            // Insérer chaque participant dans vik_participer — on utilise les INS_ID résolus en pré-validation
            foreach ($resolvedMembers as $memberObj) {
                DB::table('vik_participer')->insert([
                    'INS_ID' => $memberObj->INS_ID,
                    'COU_NUM' => $courseNum,
                    'EQU_NUM' => $newEquNum,
                ]);
            }

            // Chef participation: checkbox in the view is named 'participation'
            $chefParticipates = $request->has('participation') || $request->boolean('participation');
            if ($chefParticipates) {
                $exists = DB::table('vik_participer')
                    ->where('INS_ID', $chefId)
                    ->where('COU_NUM', $courseNum)
                    ->where('EQU_NUM', $newEquNum)
                    ->exists();

                if (!$exists) {
                    DB::table('vik_participer')->insert([
                        'INS_ID' => $chefId,
                        'COU_NUM' => $courseNum,
                        'EQU_NUM' => $newEquNum,
                    ]);
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Erreur inscription équipe : ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors(['msg' => 'Une erreur est survenue lors de l\'inscription.']);
        }

        // Redirect to the course page and flash a success notification so the user sees confirmation
        return redirect()->route('race.show', ['cou_num' => $courseNum])->with('success', 'Inscription d\'équipe réussie !');
    }

    /**
     * AJAX endpoint: search inscrits by name or email for autocomplete suggestions.
     * Returns JSON array of matches: {INS_ID, INS_PRENOM, INS_NOM, INS_MAIL, INS_NAISSANCE}
     */
    public function searchInscrits(Request $request)
    {
        $q = trim($request->query('q', ''));
        if ($q === '') {
            return response()->json([]);
        }

        // simple search: prenom or nom or concatenation
        $matches = User::where('INS_PRENOM', 'like', "%{$q}%")
            ->orWhere('INS_NOM', 'like', "%{$q}%")
            ->orWhere(DB::raw("CONCAT(INS_PRENOM, ' ', INS_NOM)"), 'like', "%{$q}%")
            ->orWhere('INS_MAIL', 'like', "%{$q}%")
            ->select('INS_ID', 'INS_PRENOM', 'INS_NOM', 'INS_MAIL', 'INS_NAISSANCE')
            ->limit(10)
            ->get();

        return response()->json($matches);
    }
}