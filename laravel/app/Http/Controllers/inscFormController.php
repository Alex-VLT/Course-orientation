<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Team; 
use App\Models\Utilisateur;

class inscFormController extends Controller
{
    public function showForm()
    {
        return view('/pages/inscForm');
    }

    public function submitForm(Request $request){
        $validated = $request->validate([
            'chefisparticipant' => 'required|boolean',
            'team_name' => 'required|string|max:255',
            'coureurs' => 'required|array|min:1',
            'coureurs.*.nom' => 'required|string|max:255',
            'coureurs.*.prenom' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $chief = Utilisateur::create([
                'nom' => $validated['coureurs'][0]['nom'],
                'prenom' => $validated['coureurs'][0]['prenom'],
                'age' => $validated['coureurs'][0]['age'] ?? 0,
                'liscence_number' => $validated['coureurs'][0]['liscence_number'] ?? 'À compléter',
            ]);

            $team = Team::create([
            'team_name' => $validated['team_name'],
            'ins_id' => $chief->id,
            'EQU_ORDRE_ARRIVEE' => null,
            'EQU_TEMPS' => null,
            'EQU_POINTS' => null
        ]);
            foreach ($validated['coureurs'] as $coureurData) {
            $coureur = Utilisateur::create([
                'nom' => $coureurData['nom'],
                'prenom' => $coureurData['prenom'],
                'age' => $coureurData['age'],
                'liscence_number' => $coureurData['liscence_number'],
                'is_chief' => false
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