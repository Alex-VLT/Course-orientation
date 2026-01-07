<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\VikClub;
use App\Models\User;

class ClubController extends Controller
{
    /**
     * Display a listing of the clubs.
     */
    public function index(Request $request): View
    {
        $clubs = VikClub::paginate(15);
        $inscrits = User::orderBy('INS_NOM')->paginate(20);
        // licensed users (have a license number) for assigning as responsables
        $licensed = User::whereNotNull('INS_NUM_LICENCE')->orderBy('INS_NOM')->get();

        return view('pages.ClubManagement', compact('clubs', 'inscrits', 'licensed'));
    }

    /**
     * Update a club via AJAX
     */
    public function update(Request $request, VikClub $club)
    {
        $data = $request->validate([
            'CLU_NOM' => ['required', 'string', 'max:255'],
            'CLU_ADRESSE' => ['nullable', 'string', 'max:255'],
            'CLU_CODE_POSTAL' => ['nullable', 'string', 'max:20'],
            'CLU_VILLE' => ['nullable', 'string', 'max:255'],
            'INS_ID' => ['nullable', 'integer', 'exists:VIK_INSCRIT,INS_ID'],
        ]);

        $club->fill($data);
        $club->save();

        return response()->json(['success' => true, 'club' => $club]);
    }

    /**
     * Store a newly created club via AJAX
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'CLU_NOM' => ['required', 'string', 'max:255'],
            'CLU_ADRESSE' => ['nullable', 'string', 'max:255'],
            'CLU_CODE_POSTAL' => ['nullable', 'string', 'max:20'],
            'CLU_VILLE' => ['nullable', 'string', 'max:255'],
            'INS_ID' => ['nullable', 'integer', 'exists:VIK_INSCRIT,INS_ID'],
        ]);

        $club = VikClub::create($data);

        return response()->json(['success' => true, 'club' => $club]);
    }
}
