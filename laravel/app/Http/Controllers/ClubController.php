<?php

namespace App\Http\Controllers;

use App\Models\VikClub;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class ClubController extends Controller
{
    /**
     * Page de gestion Clubs + Inscrits
     */
    public function index(Request $request): View
    {
        // Only allow admin users (est_Admin flag) - redirect non-admins to main page
        $user = Auth::user();
        if (!$user || (method_exists($user, 'isAdmin') && !$user->isAdmin())) {
            return view('/pages/mainPage');
        }

        $clubs = VikClub::orderBy('CLU_NOM')->paginate(15);

        $inscrits = User::orderBy('INS_NOM')
            ->orderBy('INS_PRENOM')
            ->paginate(20);

        // Ajouter le flag can_delete pour chaque inscrit
        foreach ($inscrits as $inscrit) {
            $insId = $inscrit->INS_ID;
            $ownsSomething = 
                DB::table('vik_club')->where('INS_ID', $insId)->exists() ||
                DB::table('vik_raid')->where('INS_ID', $insId)->exists() ||
                DB::table('vik_course')->where('INS_ID', $insId)->exists() ||
                DB::table('vik_equipe')->where('INS_ID', $insId)->exists();
            
            $inscrit->can_delete = !$ownsSomething;
        }

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
            'CLU_ADRESSE'     => ['required', 'string', 'max:255'],
            'CLU_CODE_POSTAL' => ['required', 'string', 'max:10'],
            'CLU_VILLE'       => ['required', 'string', 'max:64'],
            'INS_ID'          => ['required', 'integer', 'exists:vik_inscrit,INS_ID'],
        ], [
            'CLU_NOM.required' => 'Le nom du club est obligatoire.',
            'CLU_NOM.string' => 'Le nom du club doit être une chaîne de caractères.',
            'CLU_NOM.max' => 'Le nom du club ne peut pas dépasser 64 caractères.',
            'CLU_ADRESSE.required' => 'L\'adresse est obligatoire.',
            'CLU_ADRESSE.string' => 'L\'adresse doit être une chaîne de caractères.',
            'CLU_ADRESSE.max' => 'L\'adresse ne peut pas dépasser 255 caractères.',
            'CLU_CODE_POSTAL.required' => 'Le code postal est obligatoire.',
            'CLU_CODE_POSTAL.string' => 'Le code postal doit être une chaîne de caractères.',
            'CLU_CODE_POSTAL.max' => 'Le code postal ne peut pas dépasser 10 caractères.',
            'CLU_VILLE.required' => 'La ville est obligatoire.',
            'CLU_VILLE.string' => 'La ville doit être une chaîne de caractères.',
            'CLU_VILLE.max' => 'La ville ne peut pas dépasser 64 caractères.',
            'INS_ID.required' => 'Le responsable est obligatoire.',
            'INS_ID.integer' => 'Le responsable doit être un numéro valide.',
            'INS_ID.exists' => 'Le responsable sélectionné n\'existe pas.',
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
            'CLU_ADRESSE'     => ['required', 'string', 'max:255'],
            'CLU_CODE_POSTAL' => ['required', 'string', 'max:10'],
            'CLU_VILLE'       => ['required', 'string', 'max:64'],
            'INS_ID'          => ['required', 'integer', 'exists:vik_inscrit,INS_ID'],
        ], [
            'CLU_NOM.required' => 'Le nom du club est obligatoire.',
            'CLU_NOM.string' => 'Le nom du club doit être une chaîne de caractères.',
            'CLU_NOM.max' => 'Le nom du club ne peut pas dépasser 64 caractères.',
            'CLU_ADRESSE.required' => 'L\'adresse est obligatoire.',
            'CLU_ADRESSE.string' => 'L\'adresse doit être une chaîne de caractères.',
            'CLU_ADRESSE.max' => 'L\'adresse ne peut pas dépasser 255 caractères.',
            'CLU_CODE_POSTAL.required' => 'Le code postal est obligatoire.',
            'CLU_CODE_POSTAL.string' => 'Le code postal doit être une chaîne de caractères.',
            'CLU_CODE_POSTAL.max' => 'Le code postal ne peut pas dépasser 10 caractères.',
            'CLU_VILLE.required' => 'La ville est obligatoire.',
            'CLU_VILLE.string' => 'La ville doit être une chaîne de caractères.',
            'CLU_VILLE.max' => 'La ville ne peut pas dépasser 64 caractères.',
            'INS_ID.required' => 'Le responsable est obligatoire.',
            'INS_ID.integer' => 'Le responsable doit être un numéro valide.',
            'INS_ID.exists' => 'Le responsable sélectionné n\'existe pas.',
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
                DB::table('vik_club')->where('INS_ID', $insId)->exists() ||
                DB::table('vik_raid')->where('INS_ID', $insId)->exists() ||
                DB::table('vik_course')->where('INS_ID', $insId)->exists() ||
                DB::table('vik_equipe')->where('INS_ID', $insId)->exists();

        if ($ownsSomething) {
            return response()->json([
                'success' => false,
                'message' => "Suppression impossible : cet inscrit est responsable d'un club, d'un raid, d'une course ou d'une équipe."
            ], 409);
        }

            DB::beginTransaction();
        try {
                DB::table('vik_participer')->where('INS_ID', $insId)->delete();
                DB::table('vik_adherer')->where('INS_ID', $insId)->delete();
                DB::table('vik_inscrit')->where('INS_ID', $insId)->delete();

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
                DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => "Erreur lors de la suppression. Veuillez réessayer."
            ], 500);
        }
    }
}
