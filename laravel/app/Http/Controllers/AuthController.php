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
    public function showLogin()
    {
        return view('pages.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt(['INS_MAIL' => $credentials['email'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            return redirect()->intended('/')->with('success', 'Vous êtes connecté !');
        }

        return back()->withErrors([
            'email' => 'Les identifiants ne correspondent pas.',
        ]);
    }

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

        // 2. Sauvegarde du Club via la table de liaison VIK_ADHERER
        if (!empty($validated['club_id'])) {
            DB::table('VIK_ADHERER')->insert([
                'INS_ID' => $newId,
                'CLU_NUM' => $validated['club_id'],
                // Pas de colonne ADH_ANNEE ici
            ]);
        }

        Auth::login($user);

        return redirect('/');
    }

    // --- PARTIE PROFIL ---

    public function profil()
    {
        $insId = Auth::id();
        
        // Récupération utilisateur + Club via jointures
        $user = DB::table('vik_inscrit as i')
            ->leftJoin('vik_adherer as a', 'a.INS_ID', '=', 'i.INS_ID') 
            ->leftJoin('vik_club as c', 'c.CLU_NUM', '=', 'a.CLU_NUM') 
            ->select('i.*', 'c.CLU_NOM', 'c.CLU_NUM', 'c.CLU_VILLE') 
            ->where('i.INS_ID', $insId)
            ->first();

        if (!$user) {
            abort(404, "Profil introuvable.");
        }

        $now = Carbon::now();

        // Courses à venir
        $coursesAVenir = DB::table('vik_participer as p')
            ->join('vik_course as c', 'c.COU_NUM', '=', 'p.COU_NUM')
            ->leftJoin('vik_type_course as t', 't.TYP_NUM', '=', 'c.TYP_NUM')
            ->leftJoin('vik_raid as r', 'r.RAID_NUM', '=', 'c.RAID_NUM') // Pour afficher le nom du Raid si besoin
            ->where('p.INS_ID', $insId)
            ->where('c.COU_DATE_FIN', '>=', $now)
            ->select('c.COU_NUM', 'c.COU_NOM', 'c.COU_DATE_DEPART', 'c.COU_DATE_FIN', 'c.COU_DIFFICULTE', 'c.COU_DUREE', 't.TYP_LABEL', 'r.RAID_NOM', 'p.EQU_NUM')
            ->orderBy('c.COU_DATE_DEPART', 'asc')
            ->get();

        // Courses passées
        $coursesPassees = DB::table('vik_participer as p')
            ->join('vik_course as c', 'c.COU_NUM', '=', 'p.COU_NUM')
            ->leftJoin('vik_type_course as t', 't.TYP_NUM', '=', 'c.TYP_NUM')
            ->leftJoin('vik_raid as r', 'r.RAID_NUM', '=', 'c.RAID_NUM')
            ->join('vik_equipe as e', function ($join) {
                $join->on('e.COU_NUM', '=', 'p.COU_NUM')->on('e.EQU_NUM', '=', 'p.EQU_NUM');
            })
            ->where('p.INS_ID', $insId)
            ->where('c.COU_DATE_FIN', '<', $now)
            ->select('c.COU_NUM', 'c.COU_NOM', 'c.COU_DATE_DEPART', 'c.COU_DATE_FIN', 'c.COU_DIFFICULTE', 'c.COU_DUREE', 't.TYP_LABEL', 'r.RAID_NOM', 'p.EQU_NUM', 'e.EQU_POINTS', 'e.EQU_ORDRE_ARRIVEE', 'e.EQU_NOM')
            ->orderBy('c.COU_DATE_DEPART', 'desc')
            ->get();

        // Membres par équipe (pour les résultats)
        $membersByTeam = [];
        $allowedKeys = [];
        foreach ($coursesPassees as $c) { $allowedKeys[$c->COU_NUM . '-' . $c->EQU_NUM] = true; }
        
        // Ajout aussi pour les courses à venir pour voir les coéquipiers
        foreach ($coursesAVenir as $c) {
             if(isset($c->EQU_NUM)) $allowedKeys[$c->COU_NUM . '-' . $c->EQU_NUM] = true; 
        }

        if (!empty($allowedKeys)) {
            $allCourseNums = $coursesPassees->pluck('COU_NUM')->merge($coursesAVenir->pluck('COU_NUM'))->unique()->values();
            
            $rows = DB::table('vik_participer as p')
                ->join('vik_inscrit as i', 'i.INS_ID', '=', 'p.INS_ID')
                ->whereIn('p.COU_NUM', $allCourseNums)
                ->select('p.COU_NUM', 'p.EQU_NUM', 'i.INS_PRENOM', 'i.INS_NOM')
                ->orderBy('p.COU_NUM')->orderBy('p.EQU_NUM')->orderBy('i.INS_NOM')
                ->get();

            foreach ($rows as $r) {
                $key = $r->COU_NUM . '-' . $r->EQU_NUM;
                if (!isset($allowedKeys[$key])) continue;
                $membersByTeam[$key][] = ['prenom' => $r->INS_PRENOM, 'nom' => $r->INS_NOM];
            }
        }

        // Stats
        $nbCourses = DB::table('vik_participer')->where('INS_ID', $insId)->distinct('COU_NUM')->count('COU_NUM');
        $nbPodiums = DB::table('vik_participer as p')->join('vik_equipe as e', function ($join) { $join->on('e.COU_NUM', '=', 'p.COU_NUM')->on('e.EQU_NUM', '=', 'p.EQU_NUM'); })->where('p.INS_ID', $insId)->whereNotNull('e.EQU_ORDRE_ARRIVEE')->whereBetween('e.EQU_ORDRE_ARRIVEE', [1, 3])->distinct('p.COU_NUM')->count('p.COU_NUM');
        $nbVictoires = DB::table('vik_participer as p')->join('vik_equipe as e', function ($join) { $join->on('e.COU_NUM', '=', 'p.COU_NUM')->on('e.EQU_NUM', '=', 'p.EQU_NUM'); })->where('p.INS_ID', $insId)->where('e.EQU_ORDRE_ARRIVEE', 1)->distinct('p.COU_NUM')->count('p.COU_NUM');
        $points = DB::table('vik_participer as p')->join('vik_equipe as e', function ($join) { $join->on('e.COU_NUM', '=', 'p.COU_NUM')->on('e.EQU_NUM', '=', 'p.EQU_NUM'); })->where('p.INS_ID', $insId)->sum(DB::raw('COALESCE(e.EQU_POINTS, 0)'));

        // Liste des clubs pour le select
        $clubs = DB::table('VIK_CLUB')->select('CLU_NUM', 'CLU_NOM')->orderBy('CLU_NOM')->get();

        return view('pages.profil', [
            'user' => $user, // Contient maintenant les infos club
            'currentClub' => (object)['CLU_NOM' => $user->CLU_NOM, 'CLU_VILLE' => $user->CLU_VILLE, 'CLU_NUM' => $user->CLU_NUM],
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
            
            // Correction syntaxe validation
            'club_id' => 'nullable|integer|exists:VIK_CLUB,CLU_NUM',
            
            'INS_NAISSANCE' => ['required', 'date', 'before:today'],
        ]);

        // 1. Mise à jour de l'inscrit
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

        // 2. Mise à jour du Club via la table de liaison
        // On supprime d'abord l'ancienne liaison pour cet utilisateur
        DB::table('VIK_ADHERER')->where('INS_ID', $insId)->delete();

        // On insère la nouvelle si un club est sélectionné
        if (!empty($validated['club_id'])) {
            DB::table('VIK_ADHERER')->insert([
                'INS_ID' => $insId,
                'CLU_NUM' => $validated['club_id'],
                // Pas de colonne ADH_ANNEE
            ]);
        }

        return redirect()->route('profil')->with('success', 'Profil mis à jour.');
    }

    // Méthodes mot de passe oubliées...
    public function showForgotPassword() { return view('pages.auth.forgot-password'); }
    public function sendResetLink(Request $request) { /* ... */ }
    public function showResetForm(string $token) { return view('pages.auth.reset-password', ['token' => $token, 'email' => request('email')]); }
    public function updatePassword(Request $request) { /* ... */ }
    public function logout(Request $request) { Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect('/login'); }
}