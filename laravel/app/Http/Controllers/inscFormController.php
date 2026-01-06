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
        return view('/pages/inscForm');
    }

    public function submitForm(Request $request)
    {
        $validated = $request->validate([
            'chefisparticipant' => 'required|boolean',
            'team_name' => 'required|string|max:255',
            'coureurs' => 'required|array|min:1',
            'coureurs.*.nom' => 'required|string|max:255',
            'coureurs.*.prenom' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $chief = auth()->user();
            // a revoir avec l'authentification

            $team = Team::create([
                'COU_NUM' => request()->query('course'),
                'INS_ID' => $chief->id,
                'EQU_NOM' => $validated['team_name'],
            ]);

            DB::table('equipe')->insert([
                'COU_NUM' => $team->COU_NUM,
                'INS_ID' => $team->id,
                'EQU_NOM' => $validated['team_name'],
                'EQU_ORDRE_ARRIVEE' => null,
                'EQU_TEMPS' => null,
                'EQU_POINTS' => null
            ]);

            foreach ($validated['coureurs'] as $coureurData) {
                $coureur = User::create([
                    'nom' => $coureurData['nom'],
                    'prenom' => $coureurData['prenom'],
                ]);
                DB::table('vik_participer')->insert([
                    'equipe_id' => $team->id,
                    'utilisateur_id' => $coureur->id,
                    'course_number' => $team->course_number,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            if ($validated['chefisparticipant']) {
                DB::table('vik_participer')->insert([
                    'equipe_id' => $team->id,
                    'utilisateur_id' => $chief->id,
                    'course_number' => $team->course_number,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => 'Une erreur est survenue lors de l\'inscription.']);
        }

        return redirect()->route('/pages/example')->with('success', 'Inscription réussie !');
    }
}