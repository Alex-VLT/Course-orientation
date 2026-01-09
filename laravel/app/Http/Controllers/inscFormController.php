<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\VerifInscription;
use Illuminate\Support\Facades\Mail;
use App\Mail\TeamRegisteredChef;
use App\Mail\TeamRegisteredRunner;

class inscFormController extends Controller
{
    /**
     * Show the inscription form.
     *
     * This method optionally accepts a `course` query parameter. When present it:
     * - fetches the course to determine team size limits
     * - performs lightweight registration window checks (course and raid-level)
     *
    * @param \Illuminate\Http\Request $request
    * @return \Illuminate\Http\Response|\Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showForm(Request $request)
    {
        $courseNum = $request->query('course');
        $teamMax = null;
        if (! empty($courseNum) && is_numeric($courseNum)) {
            $course = VerifInscription::fetchCourse((int) $courseNum);
            if ($course) {
                $teamMax = $course->COU_PART_PAR_EQU_MAX ?? null;
            }
        }

        if (! empty($courseNum) && is_numeric($courseNum)) {
            $courseObj = VerifInscription::fetchCourse((int) $courseNum);
            if ($courseObj) {
                $now = new \DateTime();
                if (! empty($courseObj->COU_DATE_FIN)) {
                    try {
                        $end = new \DateTime($courseObj->COU_DATE_FIN);
                        if ($end < $now) {
                            return redirect()->route('race.show', ['cou_num' => (int)$courseNum])->with('error', 'Les inscriptions sont terminées pour cette course.');
                        }
                    } catch (\Exception $_e) {
                        // parsing errors ignored; form will render and submission will re-check
                    }
                }

                try {
                    $raid = DB::table('vik_raid')->where('RAID_NUM', $courseObj->RAID_NUM)->first();
                    if ($raid) {
                        if (! empty($raid->RAID_DATE_DEBUT_INSCRI)) {
                            $startIns = new \DateTime($raid->RAID_DATE_DEBUT_INSCRI);
                            if ($now < $startIns) {
                                return redirect()->route('race.show', ['cou_num' => (int)$courseNum])->with('error', 'Les inscriptions pour cette course ne sont pas encore ouvertes.');
                            }
                        }
                        if (! empty($raid->RAID_DATE_FIN_INSCRI)) {
                            $endIns = new \DateTime($raid->RAID_DATE_FIN_INSCRI);
                            if ($now > $endIns) {
                                return redirect()->route('race.show', ['cou_num' => (int)$courseNum])->with('error', 'Les inscriptions pour cette course sont clôturées.');
                            }
                        }
                    }
                } catch (\Exception $_e) {
                    // DB/parse issues are tolerated at display time
                }
            }
        }

        return view('pages.inscForm', [
            'team_max' => $teamMax,
            'course_num' => $courseNum,
        ]);
    }

    /**
     * Handle the inscription form submission.
     *
     * Behaviour summary:
     * - validates request input (team name, optional people array)
     * - resolves provided members into existing INS_IDs (no creation)
     * - runs in-memory validations (age, duplicates, team sizes) using VerifInscriptionController
     * - inserts team and participations inside a DB transaction
     * - sends confirmation emails (non-blocking)
     *
     * Error modes:
     * - returns back()->withErrors() on validation or pre-check failures
     * - rolls back DB transaction on exception
     *
     * Tests to cover:
     * - happy path (insertion + emails queued/sent)
     * - duplicate member detection
     * - age validation branch
     * - race/raid registration window blocking
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function submitForm(Request $request)
    {
        $rawPayload = ['input' => $request->all(), 'query' => $request->query()];
        logger()->debug('submitForm raw request', $rawPayload);

    // === Request logging & optional debug echo ===
    // We log the raw request early to aid troubleshooting. The debug_echo flag returns
    // the raw payload as JSON for fast inspection during development and should not be
    // enabled in production.

        if ($request->boolean('debug_echo') || $request->has('debug_echo')) {
            return response()->json($rawPayload);
        }

        // === Validate input ===
        // Ensures required fields are present and basic shapes are correct. Note: more
        // domain-specific checks (age, duplicate members, overlaps) are performed later
        // using VerifInscriptionController helpers.
        $validated = $request->validate([
            'team_name' => 'required|string|max:255',
            'people' => 'sometimes|array|min:1',
            'people.*.firstname' => 'sometimes|required_with:people|string|max:255',
            'people.*.name' => 'sometimes|required_with:people|string|max:255',
            'people.*.email' => 'sometimes|nullable|email',
        ], [
            'team_name.required' => "Le nom de l'équipe est obligatoire.",
            'people.*.firstname.required_with' => "Le prénom du participant est obligatoire lorsque la liste des participants est fournie.",
            'people.*.name.required_with' => "Le nom du participant est obligatoire lorsque la liste des participants est fournie.",
        ]);

        // === Authentication & chef (leader) resolution ===
        // The action requires an authenticated user. We resolve the current user to a
        // local INS record to obtain the canonical INS_ID and contact email used later
        // when creating the team and sending notifications.
        $authUser = auth()->user();
        if (!$authUser) {
            return back()->withErrors(['msg' => 'Vous devez être connecté pour créer une équipe.']);
        }

        $chefEmail = $authUser->INS_MAIL ?? ($authUser->email ?? null);
        if (empty($chefEmail)) {
            return back()->withErrors(['msg' => 'Impossible de déterminer l\'email de l\'utilisateur connecté.']);
        }

        $chefRecord = User::where('INS_MAIL', $chefEmail)->first();
        if (!$chefRecord) {
            return back()->withErrors(['msg' => 'Aucun inscrit trouvé pour l\'email du chef connecté.']);
        }

        $chefId = $chefRecord->INS_ID;
        $chefParticipates = $request->has('participation') || $request->boolean('participation');

    // === Course selection & registration window checks ===
    // Resolve the target course number and perform lightweight window checks to
    // reject submissions outside the insription dates. More robust checks exist in
    // the VerifInscription helpers called below.
    $courseNum = $request->input('course') ?? $request->query('course');
        if (empty($courseNum) || !is_numeric($courseNum)) {
            return back()->withErrors(['msg' => 'Numéro de course manquant ou invalide.']);
        }
        $courseNum = (int) $courseNum;

        $maxEq = DB::table('vik_equipe')->where('COU_NUM', $courseNum)->max('EQU_NUM');
        $newEquNum = $maxEq ? ((int)$maxEq + 1) : 1;

        $courseObj = VerifInscription::fetchCourse($courseNum);
        if (! $courseObj) {
            return back()->withErrors(['msg' => 'Course introuvable.']);
        }

        $now = new \DateTime();
        if (! empty($courseObj->COU_DATE_FIN)) {
            try {
                $end = new \DateTime($courseObj->COU_DATE_FIN);
                if ($end < $now) {
                    return redirect()->route('race.show', ['cou_num' => $courseNum])->with('error', 'Les inscriptions sont terminées pour cette course.');
                }
            } catch (\Exception $_e) {
                // ignore
            }
        }
        try {
            $raid = DB::table('vik_raid')->where('RAID_NUM', $courseObj->RAID_NUM)->first();
            if ($raid) {
                if (! empty($raid->RAID_DATE_DEBUT_INSCRI)) {
                    $startIns = new \DateTime($raid->RAID_DATE_DEBUT_INSCRI);
                    if ($now < $startIns) {
                        return redirect()->route('race.show', ['cou_num' => $courseNum])->with('error', 'Les inscriptions pour cette course ne sont pas encore ouvertes.');
                    }
                }
                if (! empty($raid->RAID_DATE_FIN_INSCRI)) {
                    $endIns = new \DateTime($raid->RAID_DATE_FIN_INSCRI);
                    if ($now > $endIns) {
                        return redirect()->route('race.show', ['cou_num' => $courseNum])->with('error', 'Les inscriptions pour cette course sont clôturées.');
                    }
                }
            }
        } catch (\Exception $_e) {
            // ignore
        }

    // === Normalize members payload ===
    // The client may post participants under either `people` (modern API) or
    // `coureurs` (legacy). Normalize to a common $members array with consistent
    // keys so downstream code can treat each entry uniformly.
    $members = [];
        if ($request->has('people')) {
            foreach ($request->input('people') as $p) {
                $members[] = [
                    'prenom' => $p['firstname'] ?? null,
                    'nom' => $p['name'] ?? null,
                    'email' => $p['email'] ?? null,
                    'licence' => $p['licence'] ?? null,
                    'pps' => $p['pps'] ?? null,
                ];
            }
        } elseif ($request->has('coureurs')) {
            foreach ($request->input('coureurs') as $c) {
                $members[] = [
                    'prenom' => $c['prenom'] ?? null,
                    'nom' => $c['nom'] ?? null,
                    'email' => $c['email'] ?? null,
                    'licence' => $c['licence'] ?? null,
                    'pps' => $c['pps'] ?? null,
                ];
            }
        }

        // === Resolve members to INS_ID ===
        // For each provided person we attempt to match an existing user by email first
        // and then by (firstname, name). We never create new users here; missing
        // participants must be registered separately and the submission will be rejected.
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
            $resolvedMembers->push((object)['INS_ID' => $user->INS_ID, 'pps' => $member['pps'] ?? null]);
        }

        // === Duplicate detection ===
        // Count occurrences of each INS_ID in the resolved list; any ID appearing more
        // than once indicates the same participant was added multiple times.
        // The chain below plucks INS_IDs, counts them, and filters to values > 1.
        $idCounts = $resolvedMembers->pluck('INS_ID')->countBy()->filter(function($c){ return $c > 1; });
        if ($idCounts->isNotEmpty()) {
            $dupIds = $idCounts->keys()->values()->all();
            logger()->warning('Duplicata détecté dans les membres fournis', ['dup_ids' => $dupIds]);
            return back()->withErrors(['msg' => 'Duplication détectée : un même coureur est présent plusieurs fois dans la liste.']);
        }

        if ($chefParticipates && $resolvedMembers->pluck('INS_ID')->contains($chefId)) {
            return back()->withErrors(['msg' => 'Le chef est déjà ajouté comme coureur : un même utilisateur ne peut pas figurer plusieurs fois.']);
        }

        // === Existing participation & overlap checks ===
        // For each participant we verify they are not already registered for this course
        // and that they don't have an overlapping course. When a conflict is found we
        // attempt to render a human-friendly name; if the INS record cannot be fetched
        // we log the ID for admins and return a generic 'Utilisateur inconnu' to the UI.
        foreach ($resolvedMembers as $m) {
            $isInCourse = VerifInscription::isInscritInCourse($m->INS_ID, $courseNum);
            if ($isInCourse) {
                $ins = VerifInscription::fetchInscritById($m->INS_ID);
                if ($ins) {
                    $who = trim(($ins->INS_PRENOM ?? '') . ' ' . ($ins->INS_NOM ?? ''));
                } else {
                    logger()->debug('Inscrit introuvable (duplicate check)', ['ins_id' => $m->INS_ID]);
                    $who = 'Utilisateur inconnu';
                }
                return back()->withErrors(['msg' => "{$who} est déjà inscrit pour cette course dans une autre équipe."]);
            }

            $conflict = VerifInscription::findOverlappingCourseForInscrit($m->INS_ID, $courseObj->COU_DATE_DEPART ?? null, $courseObj->COU_DATE_FIN ?? null, $courseNum);
            if ($conflict) {
                $ins = VerifInscription::fetchInscritById($m->INS_ID);
                if ($ins) {
                    $who = trim(($ins->INS_PRENOM ?? '') . ' ' . ($ins->INS_NOM ?? ''));
                } else {
                    logger()->debug('Inscrit introuvable (overlap check)', ['ins_id' => $m->INS_ID]);
                    $who = 'Utilisateur inconnu';
                }
                $cstart = !empty($conflict->COU_DATE_DEPART) ? (new \DateTime($conflict->COU_DATE_DEPART))->format('d/m/Y H:i') : 'début inconnu';
                $cend = !empty($conflict->COU_DATE_FIN) ? (new \DateTime($conflict->COU_DATE_FIN))->format('d/m/Y H:i') : 'fin inconnue';
                return back()->withErrors(['msg' => $who . ' participe déjà à une autre course ("' . ($conflict->COU_NOM ?? '') . '") du ' . $cstart . ' au ' . $cend . ' — impossible de s\'inscrire en double.']);
            }
    }

    if ($chefParticipates) {
            $chefAlready = VerifInscription::isInscritInCourse($chefId, $courseNum);
                if ($chefAlready) {
                    $who = trim(($chefRecord->INS_PRENOM ?? '') . ' ' . ($chefRecord->INS_NOM ?? '')) ?: 'Le chef';
                    return back()->withErrors(['msg' => "{$who} est déjà inscrit pour cette course dans une autre équipe."]);
                }

            $conflictChef = VerifInscription::findOverlappingCourseForInscrit($chefId, $courseObj->COU_DATE_DEPART ?? null, $courseObj->COU_DATE_FIN ?? null, $courseNum);
                if ($conflictChef) {
                    $who = trim(($chefRecord->INS_PRENOM ?? '') . ' ' . ($chefRecord->INS_NOM ?? '')) ?: 'Le chef';
                    $cstart = !empty($conflictChef->COU_DATE_DEPART) ? (new \DateTime($conflictChef->COU_DATE_DEPART))->format('d/m/Y H:i') : 'début inconnu';
                    $cend = !empty($conflictChef->COU_DATE_FIN) ? (new \DateTime($conflictChef->COU_DATE_FIN))->format('d/m/Y H:i') : 'fin inconnue';
                    return back()->withErrors(['msg' => $who . ' participe déjà à une autre course ("' . ($conflictChef->COU_NOM ?? '') . '") du ' . $cstart . ' au ' . $cend . ' — impossible de s\'inscrire en double.']);
                }
        }

        // === PPS completeness checks ===
        // Build a list of participants who will need a PPS value before the event. We
        // check the stored licence/PPS first, then whether the participant provided a PPS
        // in the form. The list becomes a set of warnings included in the validation
        // payload if the submission fails other checks.
        $ppsWarnings = [];
        foreach ($resolvedMembers as $m) {
            $insRec = VerifInscription::fetchInscritById($m->INS_ID);
            $hasLicence = !empty($insRec->INS_NUM_LICENCE ?? null);
            $hasStoredPps = !empty($insRec->INS_NUM_PPS ?? null);
            $providedPps = !empty($m->pps ?? null);
            if (! $hasLicence && ! $hasStoredPps && ! $providedPps) {
                // Use a friendly name when possible; otherwise the admin can find the ID
                // in the debug logs (we deliberately avoid showing raw IDs to end users).
                $who = $insRec ? trim(($insRec->INS_PRENOM ?? '') . ' ' . ($insRec->INS_NOM ?? '')) : 'Utilisateur inconnu';
                $ppsWarnings[] = "{$who}";
            }
        }

        if ($chefParticipates) {
            $chefHasLicence = !empty($chefRecord->INS_NUM_LICENCE ?? null);
            $chefHasStoredPps = !empty($chefRecord->INS_NUM_PPS ?? null);
            $chefProvidedPps = !empty($request->input('chef_pps'));
            if (! $chefHasLicence && ! $chefHasStoredPps && ! $chefProvidedPps) {
                $ppsWarnings[] = trim(($chefRecord->INS_PRENOM ?? '') . ' ' . ($chefRecord->INS_NOM ?? '')) ?: 'Le chef';
            }
        }

        // === Simulate participations and run verifier ===
        // We create a simulated set of participations representing the current course
        // plus the new team so the verifier can calculate team size and age constraints
        // without performing any DB writes. This avoids partial writes when a rule fails.
        $existingParticipations = VerifInscription::fetchParticipationsForCourse($courseNum);
        $simParticipations = $existingParticipations->map(function($p){
            // Normalise different column naming conventions returned by the query builder
            // (some DB rows use uppercase keys, others lowercase). The verifier expects
            // objects with ins_id and equ_num.
            return (object)[
                'ins_id' => $p->INS_ID ?? $p->ins_id ?? null,
                'equ_num' => $p->EQU_NUM ?? $p->equ_num ?? null,
            ];
        });
        foreach ($resolvedMembers as $m) {
            $simParticipations->push((object)['ins_id' => $m->INS_ID, 'equ_num' => $newEquNum]);
        }

        $teamMembersForValidation = $resolvedMembers;
        if ($chefParticipates) {
            $simParticipations->push((object)['ins_id' => $chefId, 'equ_num' => $newEquNum]);
            $teamMembersForValidation->push((object)['INS_ID' => $chefId]);
        }

    // Run the VerifInscriptionController validators in-memory. These return
    // structured messages used to build the validation payload shown to users.
    $verifier = new VerifInscriptionController();
    $resultNb = $verifier->validateNbParticipants($courseObj, $simParticipations, $teamMembersForValidation);
    $resultAge = $verifier->validateAge($courseObj, $teamMembersForValidation);

        $messages = array_merge($resultNb['messages'] ?? [], $resultAge['messages'] ?? []);
        if ($resolvedMembers->isEmpty() && ! $chefParticipates) {
            $messages[] = 'Vous devez ajouter au moins un coureur ou cocher "Je participe" pour inclure le chef.';
        }
        if (!empty($messages)) {
            $validationPayload = [
                'ok' => false,
                'messages' => $messages,
                'details' => [],
            ];

            if (!empty($ppsWarnings)) {
                $validationPayload['warnings'] = $ppsWarnings;
            }

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

            return back()->withErrors(['msg' => implode(' | ', $messages)])->with('validation_json', $validationPayload);
        }

        // === Database transaction: insert team & participations ===
        // All DB mutations are wrapped in a transaction to ensure atomicity. If any
        // step fails we roll back and return an error to the user.
        DB::beginTransaction();
        try {
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

            DB::table('vik_equipe')->insert($teamData);

            foreach ($resolvedMembers as $memberObj) {
                DB::table('vik_participer')->insert([
                    'INS_ID' => $memberObj->INS_ID,
                    'COU_NUM' => $courseNum,
                    'EQU_NUM' => $newEquNum,
                ]);
            }

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

        try {
            $teamMembers = [];
            foreach ($resolvedMembers as $m) {
                $rec = VerifInscription::fetchInscritById($m->INS_ID);
                if ($rec) {
                    $teamMembers[] = $rec;
                }
            }

            if (!empty($chefRecord->INS_MAIL)) {
                Mail::to($chefRecord->INS_MAIL)->send(new TeamRegisteredChef($chefRecord, $courseObj, $request->input('team_name'), $teamMembers));
            } else {
                logger()->warning('Chef sans email, mail non envoyé', ['chef_ins_id' => $chefId]);
            }

            foreach ($teamMembers as $tm) {
                if (!empty($tm->INS_MAIL)) {
                    if ($tm->INS_ID == $chefId) {
                        continue;
                    }
                    Mail::to($tm->INS_MAIL)->send(new TeamRegisteredRunner($tm, $courseObj, $request->input('team_name'), $chefRecord));
                } else {
                    logger()->warning('Participant sans email, mail non envoyé', ['ins_id' => $tm->INS_ID]);
                }
            }
        } catch (\Exception $e) {
            logger()->error('Erreur lors de l\'envoi des emails d\'inscription : ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }

        $redirect = redirect()->route('race.show', ['cou_num' => $courseNum])->with('success', 'Inscription d\'équipe réussie !');
        if (!empty($ppsWarnings)) {
            $msg = 'Certains participants n\'ont pas de PPS renseigné ; leur PPS devra être fourni avant la course.';
            $redirect = $redirect->with('info', $msg);
        }
        return $redirect;
    }

    /**
     * AJAX endpoint: search inscrits by name or email for autocomplete suggestions.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchInscrits(Request $request)
    {
        $q = trim($request->query('q', ''));
        if ($q === '') {
            return response()->json([]);
        }

        try {
            try { logger()->debug('searchInscrits called', ['q' => $q, 'db' => DB::connection()->getDatabaseName()]); } catch (\Exception $_e) {}
            $query = User::where('INS_PRENOM', 'like', "%{$q}%")
                ->orWhere('INS_NOM', 'like', "%{$q}%")
                ->orWhere(DB::raw("CONCAT(INS_PRENOM, ' ', INS_NOM)"), 'like', "%{$q}%")
                ->orWhere('INS_MAIL', 'like', "%{$q}%");

            $hasPps = false;
            try {
                $hasPps = Schema::hasColumn('VIK_INSCRIT', 'INS_NUM_PPS');
            } catch (\Exception $_e) {
                $hasPps = false;
            }
            $adherentExpr = $hasPps ? "IF(INS_NUM_LICENCE IS NOT NULL OR INS_NUM_PPS IS NOT NULL, 1, 0) as is_adherent" : "IF(INS_NUM_LICENCE IS NOT NULL, 1, 0) as is_adherent";

            $matches = $query->select('INS_ID', 'INS_PRENOM', 'INS_NOM', 'INS_MAIL', 'INS_NAISSANCE', DB::raw($adherentExpr))
                ->limit(10)
                ->get();

            return response()->json($matches);
        } catch (\Exception $e) {
            logger()->error('searchInscrits failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([]);
        }
    }
}