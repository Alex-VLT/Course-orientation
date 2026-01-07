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

        return view('pages.ClubManagement', compact('clubs', 'inscrits'));
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
        ]);

        $club->fill($data);
        $club->save();

        return response()->json(['success' => true, 'club' => $club]);
    }
}
