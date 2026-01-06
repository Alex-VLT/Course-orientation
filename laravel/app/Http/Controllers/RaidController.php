<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VikRaid;
use App\Models\VikClub;
use Carbon\Carbon;

class RaidController extends Controller
{
    public function index(Request $request)
    {
        // 1. Démarrer la requête sur les raids
        $query = VikRaid::query();

        // 2. Appliquer le filtre de Recherche par nom
        if ($request->filled('search')) {
            $query->where('RAID_NOM', 'like', '%' . $request->search . '%');
        }

        // 3. Appliquer le filtre par Club
        if ($request->filled('club')) {
            $query->where('CLU_NUM', $request->club);
        }

        // 4. Appliquer le filtre par Date
        if ($request->filled('date_filter')) {
            $today = Carbon::now();
            if ($request->date_filter == 'future') {
                $query->where('RAID_DATE_DEBUT', '>=', $today);
            } elseif ($request->date_filter == 'past') {
                $query->where('RAID_DATE_DEBUT', '<', $today);
            }
        }

        // Récupérer les résultats filtrés
        $raids = $query->get();

        // Récupérer la liste des clubs pour le menu déroulant
        $clubs = VikClub::all();

        // Retourner la vue avec les raids ET les clubs
        return view('pages.mainPage', compact('raids', 'clubs'));
    }
}