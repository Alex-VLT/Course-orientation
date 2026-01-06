<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VikRaid; // On importe le modèle

class RaidController extends Controller
{
    public function index()
    {
        // Récupérer tous les raids
        $raids = VikRaid::all();

        // Retourner la vue 'mainPage' située dans le dossier 'pages'
        // On passe la variable $raids à la vue
        return view('pages.mainPage', compact('raids'));
    }
}