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
        $validated = $request->validate([
            'chefisparticipant' => 'required|boolean',
            'team_name' => 'required|string|max:255',
            'course' => 'required|integer',
            'coureurs' => 'required|array|min:1',
            'coureurs.*.nom' => 'required|string|max:255',
            'coureurs.*.prenom' => 'required|string|max:255',
            'coureurs.*.email' => 'nullable|email',
        ]);

        $chief = auth()->user();
        if (!$chief) {
            return back()->withErrors(['msg' => 'Vous devez être connecté pour créer une équipe.']);
        }

        DB::beginTransaction();
        try {
            $courseNum = $validated['course'];

            // Créer l'équipe dans la table VIK_EQUIPE et récupérer l'identifiant EQU_NUM
            $teamId = DB::table('VIK_EQUIPE')->insertGetId([
                'COU_NUM' => $courseNum,
                'INS_ID' => $chief->INS_ID,
                'EQU_NOM' => $validated['team_name'],
                'EQU_ORDRE_ARRIVEE' => null,
                'EQU_TEMPS' => null,
                'EQU_POINTS' => null,
            ], 'EQU_NUM');

            // helper: récupère un user existant par email sinon crée un nouvel enregistrement minimal
            $getOrCreateUser = function (array $data) {
                if (!empty($data['email'])) {
                    $existing = User::where('INS_MAIL', $data['email'])->first();
                    if ($existing) {
                        return $existing;
                    }
                }

                // créer un nouvel INSCRIT minimal
                $newId = (int) User::max('INS_ID') + 1;
                $user = User::create([
                    'INS_ID' => $newId,
                    'INS_NOM' => $data['nom'],
                    'INS_PRENOM' => $data['prenom'],
                    'INS_MAIL' => $data['email'] ?? null,
                    'INS_MDP' => null,
                ]);

                return $user;
            };

            // Insérer les membres fournis
            foreach ($validated['coureurs'] as $member) {
                $user = $getOrCreateUser($member);

                // insérer dans VIK_PARTICIPER
                DB::table('VIK_PARTICIPER')->insert([
                    'INS_ID' => $user->INS_ID,
                    'COU_NUM' => $courseNum,
                    'EQU_NUM' => $teamId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // ajouter le chef à VIK_PARTICIPER seulement s'il participe
            if ($validated['chefisparticipant']) {
                // éviter les doublons si le chef a aussi été listé dans les coureurs
                $exists = DB::table('VIK_PARTICIPER')
                    ->where('INS_ID', $chief->INS_ID)
                    ->where('COU_NUM', $courseNum)
                    ->where('EQU_NUM', $teamId)
                    ->exists();

                if (!$exists) {
                    DB::table('VIK_PARTICIPER')->insert([
                        'INS_ID' => $chief->INS_ID,
                        'COU_NUM' => $courseNum,
                        'EQU_NUM' => $teamId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            // log exception for debugging
            logger()->error('Erreur inscription équipe : ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors(['msg' => 'Une erreur est survenue lors de l\'inscription.']);
        }

        return redirect()->route('mainPage')->with('success', 'Inscription d\'équipe réussie !');
    }
}