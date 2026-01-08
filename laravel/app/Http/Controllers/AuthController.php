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
    // =========================================================================
    // AUTHENTIFICATION (LOGIN / LOGOUT)
    // =========================================================================

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

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    // =========================================================================
    // INSCRIPTION
    // =========================================================================

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

        // 1. Création de l'utilisateur
        // On laisse la base de données gérer l'Auto-Increment de INS_ID
        $user = User::create([
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

        // 2. Si un club est choisi, on l'ajoute dans la table de liaison
        // On utilise l'ID généré par la base ($user->INS_ID)
        if (!empty($validated['club_id'])) {
            DB::table('VIK_ADHERER')->insert([
                'INS_ID' => $user->INS_ID, 
                'CLU_NUM' => $validated['club_id'],
            ]);
        }

        Auth::login($user);

        return redirect('/');
    }

    // =========================================================================
    // MOT DE PASSE OUBLIÉ
    // =========================================================================

    public function showForgotPassword()
    {
        return view('pages.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink(['email' => $request->email]);

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'Lien de réinitialisation envoyé.')
            : back()->withErrors(['email' => 'Email introuvable.']);
    }

    public function showResetForm(string $token)
    {
        return view('pages.auth.reset-password', [
            'token' => $token,
            'email' => request('email'),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:4|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->INS_MDP = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Mot de passe modifié.')
            : back()->withErrors(['email' => 'Lien invalide ou expiré.']);
    }

    // =========================================================================
    // PROFIL UTILISATEUR
    // =========================================================================

    public function profil()
    {
        $insId = Auth::id();
        
        // Récupération utilisateur + Club
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
            ->leftJoin('vik_raid as r', 'r.RAID_NUM', '=', 'c.RAID_NUM')
            ->leftJoin('vik_equipe as e', function ($join) {
                $join->on('e.COU_NUM', '=', 'p.COU_NUM')
                    ->on('e.EQU_NUM', '=', 'p.EQU_NUM');
            })
            ->where('p.INS_ID', $insId)
            ->where('c.COU_DATE_FIN', '>=', $now)
            ->select(
                'c.COU_NUM',
                'c.COU_NOM',
                'r.RAID_NOM',      
                'c.COU_DATE_DEPART',
                'c.COU_DATE_FIN',
                'c.COU_DIFFICULTE',
                'c.COU_DUREE',
                't.TYP_LABEL',
                'p.EQU_NUM',
                'e.EQU_NOM',
                'e.INS_ID as EQU_RESP_ID'
            )
            ->orderBy('c.COU_DATE_DEPART', 'asc')
            ->get();


        // Courses passées
        $coursesPassees = DB::table('vik_participer as p')
            ->join('vik_course as c', 'c.COU_NUM', '=', 'p.COU_NUM')
            ->join('vik_raid as r', 'r.RAID_NUM', '=', 'c.RAID_NUM')
            ->leftJoin('vik_type_course as t', 't.TYP_NUM', '=', 'c.TYP_NUM')
            ->join('vik_equipe as e', function ($join) {
                $join->on('e.COU_NUM', '=', 'p.COU_NUM')
                    ->on('e.EQU_NUM', '=', 'p.EQU_NUM');
            })
            ->where('p.INS_ID', $insId)
            ->where('c.COU_DATE_FIN', '<', $now)
            ->select(
                'c.COU_NUM',
                'c.COU_NOM',
                'r.RAID_NOM',      
                'c.COU_DATE_DEPART',
                'c.COU_DATE_FIN',
                'c.COU_DIFFICULTE',
                'c.COU_DUREE',
                't.TYP_LABEL',
                'p.EQU_NUM',
                'e.EQU_NOM',
                'e.EQU_POINTS',
                'e.INS_ID as EQU_RESP_ID',
                'e.EQU_ORDRE_ARRIVEE'
            )
            ->orderBy('c.COU_DATE_DEPART', 'desc')
            ->get();


        // Membres par équipe (pour les résultats)
        $membersByTeam = [];
        $allowedKeys = [];
        
        // On liste toutes les équipes concernées (passées et futures)
        foreach ($coursesPassees as $c) { $allowedKeys[$c->COU_NUM . '-' . $c->EQU_NUM] = true; }
        foreach ($coursesAVenir as $c) { if(isset($c->EQU_NUM)) $allowedKeys[$c->COU_NUM . '-' . $c->EQU_NUM] = true; }

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

        // Stats Globales
        $nbCourses = DB::table('vik_participer')->where('INS_ID', $insId)->distinct('COU_NUM')->count('COU_NUM');
        $nbPodiums = DB::table('vik_participer as p')->join('vik_equipe as e', function ($join) { $join->on('e.COU_NUM', '=', 'p.COU_NUM')->on('e.EQU_NUM', '=', 'p.EQU_NUM'); })->where('p.INS_ID', $insId)->whereNotNull('e.EQU_ORDRE_ARRIVEE')->whereBetween('e.EQU_ORDRE_ARRIVEE', [1, 3])->distinct('p.COU_NUM')->count('p.COU_NUM');
        $nbVictoires = DB::table('vik_participer as p')->join('vik_equipe as e', function ($join) { $join->on('e.COU_NUM', '=', 'p.COU_NUM')->on('e.EQU_NUM', '=', 'p.EQU_NUM'); })->where('p.INS_ID', $insId)->where('e.EQU_ORDRE_ARRIVEE', 1)->distinct('p.COU_NUM')->count('p.COU_NUM');
        $points = DB::table('vik_participer as p')->join('vik_equipe as e', function ($join) { $join->on('e.COU_NUM', '=', 'p.COU_NUM')->on('e.EQU_NUM', '=', 'p.EQU_NUM'); })->where('p.INS_ID', $insId)->sum(DB::raw('COALESCE(e.EQU_POINTS, 0)'));

        // Liste des clubs pour le select de modification
        $clubs = DB::table('VIK_CLUB')->select('CLU_NUM', 'CLU_NOM')->orderBy('CLU_NOM')->get();

        return view('pages.profil', [
            'user' => $user, 
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
            'club_id' => 'nullable|integer|exists:VIK_CLUB,CLU_NUM',
            'INS_NAISSANCE' => ['required', 'date', 'before:today'],
        ]);

        // Mise à jour User
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

        // Mise à jour Club (Suppression puis réinsertion)
        DB::table('VIK_ADHERER')->where('INS_ID', $insId)->delete();

        if (!empty($validated['club_id'])) {
            DB::table('VIK_ADHERER')->insert([
                'INS_ID' => $insId,
                'CLU_NUM' => $validated['club_id'],
            ]);
        }

        return redirect()->route('profil')->with('success', 'Profil mis à jour.');
    }

    public function deleteAccount(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/login');
        }

        $insId = Auth::id();

        DB::transaction(function () use ($insId) {
            DB::table('VIK_ADHERER')->where('INS_ID', $insId)->delete();
            DB::table('VIK_PARTICIPER')->where('INS_ID', $insId)->delete();
            DB::table('VIK_INSCRIT')->where('INS_ID', $insId)->delete();
        });

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Votre compte a bien été supprimé.');
    }

    // =========================================================================
    // ESPACE ORGANISATEUR / GÉRANT DE CLUB
    // =========================================================================

    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Vérification si l'utilisateur est gérant (dans VIK_CLUB.INS_ID)
        $club = DB::table('VIK_CLUB')
                  ->where('INS_ID', $user->INS_ID)
                  ->first();
    
        $managesClub = $club ? true : false;
        $clubMembers = collect([]); 
        $raids = [];
        $statsRaids = [];
    
        if ($managesClub) {
            // 1. Récupérer les membres (table VIK_ADHERER)
            $clubMembers = DB::table('VIK_INSCRIT')
                             ->join('VIK_ADHERER', 'VIK_INSCRIT.INS_ID', '=', 'VIK_ADHERER.INS_ID')
                             ->where('VIK_ADHERER.CLU_NUM', $club->CLU_NUM)
                             ->select('VIK_INSCRIT.INS_ID','VIK_INSCRIT.INS_NOM', 'VIK_INSCRIT.INS_PRENOM', 'VIK_INSCRIT.INS_MAIL', 'VIK_INSCRIT.INS_TEL', 'VIK_INSCRIT.INS_NAISSANCE', 'VIK_INSCRIT.INS_NUM_LICENCE')
                             ->get();
    
            // 2. Ajouter le gérant (moi) à la liste s'il n'y est pas
            if (!$clubMembers->contains('INS_ID', $user->INS_ID)) {
                $managerDetails = DB::table('VIK_INSCRIT')
                    ->where('INS_ID', $user->INS_ID)
                    ->select('INS_ID', 'INS_NOM', 'INS_PRENOM', 'INS_MAIL', 'INS_TEL', 'INS_NAISSANCE', 'INS_NUM_LICENCE')
                    ->first();
                
                if ($managerDetails) {
                    $clubMembers->push($managerDetails);
                }
            }
            
            // Tri alphabétique
            $clubMembers = $clubMembers->sortBy('INS_NOM');
    
            // 3. Raids du club
            $raids = DB::table('VIK_RAID')
                       ->where('CLU_NUM', $club->CLU_NUM) 
                       ->orderBy('RAID_DATE_DEBUT', 'desc')
                       ->get();
    
            // 4. Statistiques Panel Droite
            $statsRaids = DB::table('VIK_RAID')
                ->leftJoin('VIK_COURSE', 'VIK_RAID.RAID_NUM', '=', 'VIK_COURSE.RAID_NUM')
                ->leftJoin('VIK_PARTICIPER', 'VIK_COURSE.COU_NUM', '=', 'VIK_PARTICIPER.COU_NUM')
                ->leftJoin('VIK_ADHERER', 'VIK_PARTICIPER.INS_ID', '=', 'VIK_ADHERER.INS_ID')
                ->where('VIK_ADHERER.CLU_NUM', $club->CLU_NUM)
                ->select('VIK_RAID.RAID_NOM as nom_raid', DB::raw('count(distinct VIK_PARTICIPER.INS_ID) as nb_inscrits'))
                ->groupBy('VIK_RAID.RAID_NUM', 'VIK_RAID.RAID_NOM')
                ->limit(5)
                ->get();
        }
    
        return view('pages.organisateur', compact('club', 'managesClub', 'clubMembers', 'raids', 'statsRaids'));
    }

    public function unsubscribeTeam(int $cou_num, int $equ_num)
{
    $insId = Auth::id();


    $team = DB::table('vik_equipe')
        ->where('COU_NUM', $cou_num)
        ->where('EQU_NUM', $equ_num)
        ->first();


    if (!$team) {
        abort(404, "Équipe introuvable.");
    }


    if ((int) $team->INS_ID !== (int) $insId) {
        abort(403, "Vous n'êtes pas responsable de cette équipe.");
    }


    $course = DB::table('vik_course')->where('COU_NUM', $cou_num)->first();
    if (!$course) abort(404, "Course introuvable.");


    if (Carbon::parse($course->COU_DATE_FIN)->isPast()) {
        return redirect()->route('profil')->with('success', 'Course terminée : désinscription impossible.');
    }


    DB::transaction(function () use ($cou_num, $equ_num) {
        DB::table('vik_participer')
            ->where('COU_NUM', $cou_num)
            ->where('EQU_NUM', $equ_num)
            ->delete();


        DB::table('vik_equipe')
            ->where('COU_NUM', $cou_num)
            ->where('EQU_NUM', $equ_num)
            ->delete();
    });


    return redirect()->route('profil')->with('success', "Équipe désinscrite de la course.");
}

}

