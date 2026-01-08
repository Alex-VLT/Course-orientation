<?php

namespace App\Http\Controllers;

use App\Models\VikClub;
use App\Models\VikClubPending;
use App\Models\User;
use App\Mail\ClubResponsibilityConfirmation;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;


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

        if (empty($data['INS_ID'])) {
            return response()->json([
                'success' => false,
                'message' => "Un responsable licencié est requis pour créer le club."
            ], 422);
        }

        $responsible = User::find($data['INS_ID']);
        if (!$responsible || empty($responsible->INS_MAIL)) {
            return response()->json([
                'success' => false,
                'message' => "Impossible d'envoyer l'email : le responsable n'a pas d'adresse mail."
            ], 422);
        }

        $pending = VikClubPending::create([
            'token'           => Str::uuid()->toString(),
            'INS_ID'          => $data['INS_ID'],
            'created_by'      => $request->user()->INS_ID ?? null,
            'CLU_NOM'         => $data['CLU_NOM'],
            'CLU_ADRESSE'     => $data['CLU_ADRESSE'] ?? null,
            'CLU_CODE_POSTAL' => $data['CLU_CODE_POSTAL'] ?? null,
            'CLU_VILLE'       => $data['CLU_VILLE'] ?? null,
        ]);

        Mail::to($responsible->INS_MAIL)
            ->send(new ClubResponsibilityConfirmation($pending, $responsible));

        return response()->json([
            'success' => true,
            'message' => "Une demande de validation a été envoyée au responsable. La création sera effective après confirmation par email."
        ], 201);
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
     * Confirmation par email : crée réellement le club à partir d'une demande en attente
     */
    public function confirm(string $token)
    {
        $pending = VikClubPending::where('token', $token)->first();

        if (!$pending) {
            return redirect()->route('home')->with('error', 'Demande introuvable ou déjà traitée.');
        }

        $club = VikClub::create([
            'INS_ID'          => $pending->INS_ID,
            'CLU_NOM'         => $pending->CLU_NOM,
            'CLU_ADRESSE'     => $pending->CLU_ADRESSE,
            'CLU_CODE_POSTAL' => $pending->CLU_CODE_POSTAL,
            'CLU_VILLE'       => $pending->CLU_VILLE,
        ]);

        $pending->delete();

        return redirect()->route('home')->with('success', "Le club {$club->CLU_NOM} a été validé et créé." );
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
                'message' => "Suppression impossible : cet inscrit est responsable d’un club/raid/course/équipe."
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
                'message' => "Erreur lors de la suppression."
            ], 500);
        }
    }
}
