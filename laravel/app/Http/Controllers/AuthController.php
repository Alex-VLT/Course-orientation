<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{    public function showLogin()
    {
        return view('pages.login'); 
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
        return view('pages.register'); 
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

        return redirect('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}