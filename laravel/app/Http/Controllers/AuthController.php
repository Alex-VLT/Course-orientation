<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends Controller
{
    // ... (Méthodes Login/Logout inchangées) ...
    public function showLogin() { return view('pages.auth.login'); }
    public function login(Request $request) { /* ...votre code login... */ }
    public function logout(Request $request) { Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect('/login'); }

    // --- INSCRIPTION ---

    public function showRegister()
    {
        $clubs = DB::table('VIK_CLUB')
            ->select('CLU_NUM', 'CLU_NOM')
            ->orderBy('CLU_NOM', 'asc')
            ->get();

        return view('pages.auth.register', compact('clubs'));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:64',
            'prenom' => 'required|string|max:64',
            'email' => 'required|email|unique:VIK_INSCRIT,INS_MAIL',
            'password' => 'required|min:4',
            'ville' => 'required',
            'cp' => 'required|integer',
            'adresse' => 'required',
            'tel' => 'required',
            'naissance' => 'required|date',
            'licence' => 'nullable|string|max:32',
            'club_id' => 'nullable|integer|exists:VIK_CLUB,CLU_NUM',
        ]);

        $newId = User::max('INS_ID') + 1;

        // 1. Création de l'inscrit
        $user = User::create([
            'INS_ID' => $newId,
            'INS_NOM' => $validated['nom'],
            'INS_PRENOM' => $validated['prenom'],
            'INS_MAIL' => $validated['email'],
            'INS_MDP' => Hash::make($validated['password']),
            'INS_VILLE' => $validated['ville'],
            'INS_CODE_PO' => $validated['cp'],
            'INS_ADRESSE' => $validated['adresse'],
            'INS_TEL' => $validated['tel'],
            'INS_NAISSANCE' => $validated['naissance'],
            'INS_NUM_LICENCE' => $validated['licence'] ?? null,
        ]);

        // 2. Sauvegarde du Club (Correction : suppression de ADH_ANNEE)
        if (!empty($validated['club_id'])) {
            DB::table('VIK_ADHERER')->insert([
                'INS_ID' => $newId,
                'CLU_NUM' => $validated['club_id'],
                // 'ADH_ANNEE' => date('Y')  <-- LIGNE SUPPRIMÉE
            ]);
        }

        Auth::login($user);

        return redirect('/');
    }

    // --- PROFIL ---

    public function profil()
    {
        $insId = Auth::id();
        
        $user = DB::table('vik_inscrit as i')
            ->leftJoin('vik_adherer as a', 'a.INS_ID', '=', 'i.INS_ID') 
            ->leftJoin('vik_club as c', 'c.CLU_NUM', '=', 'a.CLU_NUM') 
            ->select('i.*', 'c.CLU_NOM', 'c.CLU_NUM') 
            ->where('i.INS_ID', $insId)
            ->first();

        if (!$user) {
            abort(404, "Profil introuvable.");
        }

        $now = Carbon::now();

        // (Code courses inchangé...)
        $coursesAVenir = DB::table('vik_participer as p')->join('vik_course as c', 'c.COU_NUM', '=', 'p.COU_NUM')->leftJoin('vik_type_course as t', 't.TYP_NUM', '=', 'c.TYP_NUM')->where('p.INS_ID', $insId)->where('c.COU_DATE_FIN', '>=', $now)->select('c.COU_NUM', 'c.COU_NOM', 'c.COU_DATE_DEPART', 'c.COU_DATE_FIN', 'c.COU_DIFFICULTE', 'c.COU_DUREE', 't.TYP_LABEL')->orderBy('c.COU_DATE_DEPART', 'asc')->get();
        $coursesPassees = DB::table('vik_participer as p')->join('vik_course as c', 'c.COU_NUM', '=', 'p.COU_NUM')->leftJoin('vik_type_course as t', 't.TYP_NUM', '=', 'c.TYP_NUM')->join('vik_equipe as e', function ($join) { $join->on('e.COU_NUM', '=', 'p.COU_NUM')->on('e.EQU_NUM', '=', 'p.EQU_NUM'); })->where('p.INS_ID', $insId)->where('c.COU_DATE_FIN', '<', $now)->select('c.COU_NUM', 'c.COU_NOM', 'c.COU_DATE_DEPART', 'c.COU_DATE_FIN', 'c.COU_DIFFICULTE', 'c.COU_DUREE', 't.TYP_LABEL', 'p.EQU_NUM', 'e.EQU_POINTS', 'e.EQU_ORDRE_ARRIVEE')->orderBy('c.COU_DATE_DEPART', 'desc')->get();
        
        $membersByTeam = [];
        $allowedKeys = [];
        foreach ($coursesPassees as $c) { $allowedKeys[$c->COU_NUM . '-' . $c->EQU_NUM] = true; }
        if (!empty($allowedKeys)) {
            $pastCourseNums = $coursesPassees->pluck('COU_NUM')->unique()->values();
            $rows = DB::table('vik_participer as p')->join('vik_inscrit as i', 'i.INS_ID', '=', 'p.INS_ID')->whereIn('p.COU_NUM', $pastCourseNums)->select('p.COU_NUM', 'p.EQU_NUM', 'i.INS_PRENOM', 'i.INS_NOM')->orderBy('p.COU_NUM')->orderBy('p.EQU_NUM')->orderBy('i.INS_NOM')->get();
            foreach ($rows as $r) {
                $key = $r->COU_NUM . '-' . $r->EQU_NUM;
                if (!isset($allowedKeys[$key])) continue;
                $membersByTeam[$key][] = ['prenom' => $r->INS_PRENOM, 'nom' => $r->INS_NOM];
            }
        }

        $nbCourses = DB::table('vik_participer')->where('INS_ID', $insId)->distinct('COU_NUM')->count('COU_NUM');
        $nbPodiums = DB::table('vik_participer as p')->join('vik_equipe as e', function ($join) { $join->on('e.COU_NUM', '=', 'p.COU_NUM')->on('e.EQU_NUM', '=', 'p.EQU_NUM'); })->where('p.INS_ID', $insId)->whereNotNull('e.EQU_ORDRE_ARRIVEE')->whereBetween('e.EQU_ORDRE_ARRIVEE', [1, 3])->distinct('p.COU_NUM')->count('p.COU_NUM');
        $nbVictoires = DB::table('vik_participer as p')->join('vik_equipe as e', function ($join) { $join->on('e.COU_NUM', '=', 'p.COU_NUM')->on('e.EQU_NUM', '=', 'p.EQU_NUM'); })->where('p.INS_ID', $insId)->where('e.EQU_ORDRE_ARRIVEE', 1)->distinct('p.COU_NUM')->count('p.COU_NUM');
        $points = DB::table('vik_participer as p')->join('vik_equipe as e', function ($join) { $join->on('e.COU_NUM', '=', 'p.COU_NUM')->on('e.EQU_NUM', '=', 'p.EQU_NUM'); })->where('p.INS_ID', $insId)->sum(DB::raw('COALESCE(e.EQU_POINTS, 0)'));

        $clubs = DB::table('VIK_CLUB')->select('CLU_NUM', 'CLU_NOM')->orderBy('CLU_NOM')->get();

        return view('pages.profil', [
            'user' => $user,
            'stats' => ['nbCourses' => $nbCourses, 'nbPodiums' => $nbPodiums, 'nbVictoires' => $nbVictoires, 'points' => $points],
            'coursesAVenir' => $coursesAVenir,
            'coursesPassees' => $coursesPassees,
            'membersByTeam' => $membersByTeam,
            'clubs' => $clubs,
        ]);
    }

    public function updateProfil(Request $request)
    {
        $insId = Auth::id();

        $request->merge([
            'INS_TEL' => preg_replace('/\D+/', '', $request->INS_TEL ?? ''),
            'INS_CODE_PO' => preg_replace('/\D+/', '', $request->INS_CODE_PO ?? ''),
        ]);

        $validated = $request->validateWithBag('profileUpdate', [
            'INS_NOM' => ['required', 'string', 'max:64'],
            'INS_PRENOM' => ['required', 'string', 'max:64'],
            'INS_MAIL' => ['required', 'email', 'max:255'],
            'INS_TEL' => ['required', 'regex:/^\d{10}$/'],
            'INS_CODE_PO' => ['required', 'regex:/^\d{5}$/'],
            'INS_VILLE' => ['required', 'string', 'max:64'],
            'INS_ADRESSE' => ['required', 'string', 'max:255'],
            'INS_NUM_LICENCE' => ['nullable', 'string', 'max:32'],
            'club_id' => 'nullable|integer|exists:VIK_CLUB,CLU_NUM',
            'INS_NAISSANCE' => ['required', 'date', 'before:today'],
        ]);

        DB::table('vik_inscrit')->where('INS_ID', $insId)->update([
            'INS_NOM' => $validated['INS_NOM'],
            'INS_PRENOM' => $validated['INS_PRENOM'],
            'INS_MAIL' => $validated['INS_MAIL'],
            'INS_TEL' => $validated['INS_TEL'],
            'INS_ADRESSE' => $validated['INS_ADRESSE'],
            'INS_VILLE' => $validated['INS_VILLE'],
            'INS_CODE_PO' => $validated['INS_CODE_PO'],
            'INS_NUM_LICENCE' => $validated['INS_NUM_LICENCE'] ?? null,
            'INS_NAISSANCE' => $validated['INS_NAISSANCE'],
        ]);

        // 2. Update table ADHERER
        DB::table('VIK_ADHERER')->where('INS_ID', $insId)->delete();

        if (!empty($validated['club_id'])) {
            DB::table('VIK_ADHERER')->insert([
                'INS_ID' => $insId,
                'CLU_NUM' => $validated['club_id'],
                // 'ADH_ANNEE' => date('Y') 
            ]);
        }

        return redirect()->route('profil')->with('success', 'Profil mis à jour.');
    }
}