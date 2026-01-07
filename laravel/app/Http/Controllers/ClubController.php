<?php

namespace App\Http\Controllers;

use App\Models\VikClub;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClubController extends Controller
{
    /**
     * Page de gestion Clubs + Inscrits
     */
    public function index(Request $request): View
    {
        $clubs = VikClub::orderBy('CLU_NOM')->paginate(15);

        $inscrits = User::orderBy('INS_NOM')
            ->orderBy('INS_PRENOM')
            ->paginate(20);

        // Pour choisir un responsable : uniquement les licenciés
        $licensed = User::whereNotNull('INS_NUM_LICENCE')
            ->orderBy('INS_NOM')
            ->orderBy('INS_PRENOM')
            ->get(['INS_ID', 'INS_NOM', 'INS_PRENOM', 'INS_NUM_LICENCE']);

        return view('pages.ClubManagement', compact('clubs', 'inscrits', 'licensed'));
    }

    /**
     * Créer un club (AJAX JSON)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'CLU_NOM'         => ['required', 'string', 'max:64'],
            'CLU_ADRESSE'     => ['nullable', 'string', 'max:255'],
            'CLU_CODE_POSTAL' => ['nullable', 'string', 'max:10'],
            'CLU_VILLE'       => ['nullable', 'string', 'max:64'],
            'INS_ID'          => ['nullable', 'integer', 'exists:vik_inscrit,INS_ID'],
        ]);

        $club = VikClub::create($data);

        return response()->json(['success' => true, 'club' => $club], 201);
    }

    /**
     * Mettre à jour un club (AJAX JSON)
     * Route-model binding : /clubs/{club} => {club} = CLU_NUM (voir route ci-dessous)
     */
    public function update(Request $request, VikClub $club)
    {
        $data = $request->validate([
            'CLU_NOM'         => ['required', 'string', 'max:64'],
            'CLU_ADRESSE'     => ['nullable', 'string', 'max:255'],
            'CLU_CODE_POSTAL' => ['nullable', 'string', 'max:10'],
            'CLU_VILLE'       => ['nullable', 'string', 'max:64'],
            'INS_ID'          => ['nullable', 'integer', 'exists:vik_inscrit,INS_ID'],
        ]);

        $club->update($data);

        return response()->json(['success' => true, 'club' => $club]);
    }

    /**
     * Supprimer un inscrit (AJAX JSON) - avec protection FK
     * On supprime d'abord vik_participer + vik_adherer, et on refuse si propriétaire de club/raid/course/equipe.
     */
    public function destroyInscrit(User $inscrit)
    {
        $insId = $inscrit->INS_ID;

        $ownsSomething =
            \DB::table('vik_club')->where('INS_ID', $insId)->exists() ||
            \DB::table('vik_raid')->where('INS_ID', $insId)->exists() ||
            \DB::table('vik_course')->where('INS_ID', $insId)->exists() ||
            \DB::table('vik_equipe')->where('INS_ID', $insId)->exists();

        if ($ownsSomething) {
            return response()->json([
                'success' => false,
                'message' => "Suppression impossible : cet inscrit est responsable d’un club/raid/course/équipe."
            ], 409);
        }

        \DB::beginTransaction();
        try {
            \DB::table('vik_participer')->where('INS_ID', $insId)->delete();
            \DB::table('vik_adherer')->where('INS_ID', $insId)->delete();
            \DB::table('vik_inscrit')->where('INS_ID', $insId)->delete();

            \DB::commit();
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => "Erreur lors de la suppression."
            ], 500);
        }
    }
}
