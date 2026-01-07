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
            'naissance' => 'required|date',
            'licence' => 'nullable',
            'pps' => 'nullable|max:9'

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
            'INS_NUM_LICENCE' => $validated['licence'] ?? null,
            'INS_NUM_PPS' => $validated['pps'] ?? null
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

    public function profile()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/login');
        }

        return view('pages.profil', compact('user'));
    }
public function updateProfile(Request $request)
{
    $user = Auth::user();

    $validated = $request->validate([
        'nom' => 'required|string|max:64',
        'prenom' => 'required|string|max:64',
        'email' => 'required|email|unique:VIK_INSCRIT,INS_MAIL,' . $user->INS_ID . ',INS_ID',
        'ville' => 'required|string|max:64',
        'cp' => 'required|integer',
        'adresse' => 'required|string|max:255',
        'tel' => 'required|string|max:32',
        'naissance' => 'required|date',
        'licence' => 'nullable|string|max:32',
        'pps' => 'nullable|string|max:9',
    ]);

    $user->INS_NOM = $validated['nom'];
    $user->INS_PRENOM = $validated['prenom'];
    $user->INS_MAIL = $validated['email'];
    $user->INS_VILLE = $validated['ville'];
    $user->INS_CODE_PO = $validated['cp'];
    $user->INS_ADRESSE = $validated['adresse'];
    $user->INS_TEL = $validated['tel'];
    $user->INS_NAISSANCE = $validated['naissance'];
    $user->INS_NUM_LICENCE = $validated['licence'];
    $user->INS_NUM_PPS = $validated['pps'];

    $user->save();

    return redirect()->route('profil')->with('success', 'Profil mis à jour !');
}
public function deleteAccount(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return redirect('/login');
    }

    DB::transaction(function () use ($user) {

        DB::table('VIK_ADHERER')->where('INS_ID', $user->INS_ID)->delete();
        DB::table('VIK_PARTICIPER')->where('INS_ID', $user->INS_ID)->delete();
        DB::table('VIK_EQUIPE')->where('INS_ID', $user->INS_ID)->delete();
        DB::table('VIK_RAID')->where('INS_ID', $user->INS_ID)->delete();
        DB::table('VIK_COURSE')->where('INS_ID', $user->INS_ID)->delete();
        DB::table('VIK_CLUB')->where('INS_ID', $user->INS_ID)->delete();

        $user->delete();
    });

    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login')->with('success', 'Votre compte a bien été supprimé.');
}
}