<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Team;
use App\Models\User;

class inscFormController extends Controller
{
    public function showForm()
    {
        return view('pages.inscForm');
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

        DB::beginTransaction();
        try {
            // récupérer le numéro de course : d'abord depuis le POST (hidden input), sinon depuis la query string
            $courseNum = $request->input('course') ?? $request->query('course');
            if (empty($courseNum) || !is_numeric($courseNum)) {
                return back()->withErrors(['msg' => 'Numéro de course manquant ou invalide.']);
            }
            $courseNum = (int) $courseNum;

            // calculer EQU_NUM : incrementer le numéro d'équipe pour cette course
            $maxEq = DB::table('vik_equipe')->where('COU_NUM', $courseNum)->max('EQU_NUM');
            $newEquNum = $maxEq ? ((int)$maxEq + 1) : 1;

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

            // Helper : rechercher un inscrit existant — NE PAS créer de nouvel inscrit.
            // Recherche par email si fournie, sinon par prénom+nom exacts.
            $findExistingUser = function (array $data) {
                if (!empty($data['email'])) {
                    $existing = User::where('INS_MAIL', $data['email'])->first();
                    if ($existing) {
                        return $existing;
                    }
                }

                $nom = $data['name'] ?? $data['nom'] ?? null;
                $prenom = $data['firstname'] ?? $data['prenom'] ?? null;
                if ($nom && $prenom) {
                    $existing = User::where('INS_PRENOM', $prenom)->where('INS_NOM', $nom)->first();
                    if ($existing) {
                        return $existing;
                    }
                }

                // not found — caller should handle this as an error
                return null;
            };

            // Normalize members input: prefer 'people' from the view, fallback to 'coureurs'
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

            // Insérer chaque participant dans vik_participer — on utilise uniquement l'INS_ID existant
            foreach ($members as $member) {
                $user = $findExistingUser($member);
                if (!$user) {
                    // rollback + message clair : l'inscrit doit exister avant d'être ajouté à une équipe
                    throw new \RuntimeException('Inscrit introuvable pour ' . ($member['prenom'] ?? '') . ' ' . ($member['nom'] ?? '') . '. Veuillez enregistrer l\'inscrit avant de l\'ajouter.');
                }

                DB::table('vik_participer')->insert([
                    'INS_ID' => $user->INS_ID,
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

        return redirect()->route('mainPage')->with('success', 'Inscription d\'équipe réussie !');
    }
}