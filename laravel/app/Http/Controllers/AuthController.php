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
        return view('pages.auth.register');
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
            'naissance' => 'required|date'
        ]);


        $newId = User::max('INS_ID') + 1;

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
        ]);

        Auth::login($user);

        return redirect('/');
    }

    public function showForgotPassword()
    {
        return view('pages.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            ['email' => $request->email]
        );

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

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function profil()
    {
        $insId = Auth::id();
        $user = DB::table('vik_inscrit')->where('INS_ID', $insId)->first();

        if (!$user) {
            abort(404, "Profil introuvable.");
        }

        $now = Carbon::now();

      
        $coursesAVenir = DB::table('vik_participer as p')
            ->join('vik_course as c', 'c.COU_NUM', '=', 'p.COU_NUM')
            ->join('vik_raid as r', 'r.RAID_NUM', '=', 'c.RAID_NUM')
            ->leftJoin('vik_type_course as t', 't.TYP_NUM', '=', 'c.TYP_NUM')
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
                'e.EQU_NOM'
            )
            ->orderBy('c.COU_DATE_DEPART', 'asc')
            ->get();


      
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
                'e.EQU_ORDRE_ARRIVEE'
            )
            ->orderBy('c.COU_DATE_DEPART', 'desc')
            ->get();


        
        $membersByTeam = [];

        $allCourses = $coursesAVenir->concat($coursesPassees);

        $allowedKeys = [];
        foreach ($allCourses as $c) {
            if (!isset($c->EQU_NUM)) continue;
            $allowedKeys[$c->COU_NUM . '-' . $c->EQU_NUM] = true;
        }

        if (!empty($allowedKeys)) {
            $courseNums = $allCourses->pluck('COU_NUM')->unique()->values();

            $rows = DB::table('vik_participer as p')
                ->join('vik_inscrit as i', 'i.INS_ID', '=', 'p.INS_ID')
                ->whereIn('p.COU_NUM', $courseNums)
                ->select('p.COU_NUM', 'p.EQU_NUM', 'i.INS_PRENOM', 'i.INS_NOM')
                ->orderBy('p.COU_NUM')
                ->orderBy('p.EQU_NUM')
                ->orderBy('i.INS_NOM')
                ->get();

            foreach ($rows as $r) {
                $key = $r->COU_NUM . '-' . $r->EQU_NUM;
                if (!isset($allowedKeys[$key])) continue;

                $membersByTeam[$key][] = [
                    'prenom' => $r->INS_PRENOM,
                    'nom' => $r->INS_NOM,
                ];
            }
        }


     
        $nbCourses = DB::table('vik_participer')
            ->where('INS_ID', $insId)
            ->distinct('COU_NUM')
            ->count('COU_NUM');

        $nbPodiums = DB::table('vik_participer as p')
            ->join('vik_equipe as e', function ($join) {
                $join->on('e.COU_NUM', '=', 'p.COU_NUM')
                     ->on('e.EQU_NUM', '=', 'p.EQU_NUM');
            })
            ->where('p.INS_ID', $insId)
            ->whereNotNull('e.EQU_ORDRE_ARRIVEE')
            ->whereBetween('e.EQU_ORDRE_ARRIVEE', [1, 3])
            ->distinct('p.COU_NUM')
            ->count('p.COU_NUM');

        $nbVictoires = DB::table('vik_participer as p')
            ->join('vik_equipe as e', function ($join) {
                $join->on('e.COU_NUM', '=', 'p.COU_NUM')
                     ->on('e.EQU_NUM', '=', 'p.EQU_NUM');
            })
            ->where('p.INS_ID', $insId)
            ->where('e.EQU_ORDRE_ARRIVEE', 1)
            ->distinct('p.COU_NUM')
            ->count('p.COU_NUM');

        $points = DB::table('vik_participer as p')
            ->join('vik_equipe as e', function ($join) {
                $join->on('e.COU_NUM', '=', 'p.COU_NUM')
                     ->on('e.EQU_NUM', '=', 'p.EQU_NUM');
            })
            ->where('p.INS_ID', $insId)
            ->sum(DB::raw('COALESCE(e.EQU_POINTS, 0)'));


        $currentClub = DB::table('vik_adherer as a')
            ->join('vik_club as c', 'c.CLU_NUM', '=', 'a.CLU_NUM')
            ->where('a.INS_ID', $insId)
            ->select('c.CLU_NUM', 'c.CLU_NOM', 'c.CLU_VILLE')
            ->first();

        $clubs = DB::table('vik_club')
            ->select('CLU_NUM', 'CLU_NOM', 'CLU_VILLE')
            ->orderBy('CLU_NOM')
            ->get();
    
        return view('pages.profil', [
            'user' => $user,
            'stats' => [
                'nbCourses' => $nbCourses,
                'nbPodiums' => $nbPodiums,
                'nbVictoires' => $nbVictoires,
                'points' => $points,
            ],
            'coursesAVenir' => $coursesAVenir,
            'coursesPassees' => $coursesPassees,
            'membersByTeam' => $membersByTeam,
            'currentClub' => $currentClub,
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

            'CLU_NUM' => ['required', 'integer', 'exists:vik_club,CLU_NUM'],

            'INS_NUM_LICENCE' => ['nullable', 'string', 'max:32'],
            'INS_NAISSANCE' => ['required', 'date', 'before:today'],
        ], [
            'INS_TEL.regex' => 'Le numéro de téléphone doit contenir exactement 10 chiffres.',
            'INS_CODE_PO.regex' => 'Le code postal doit contenir exactement 5 chiffres.',
            'INS_NAISSANCE.before' => 'La date de naissance doit être dans le passé.',
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

        DB::table('vik_adherer')->where('INS_ID', $insId)->delete();

        DB::table('vik_adherer')->insert([
            'INS_ID' => $insId,
            'CLU_NUM' => (int) $validated['CLU_NUM'],
        ]);


        return redirect()->route('profil')->with('success', 'Profil mis à jour.');
    }

}