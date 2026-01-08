<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard for the authenticated user.
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Récupération du club géré (Via vik_club.INS_ID)
        $club = DB::table('vik_club')
            ->where('INS_ID', $user->INS_ID)
            ->first();

        $managesClub = $club ? true : false;
        $clubMembers = collect([]); // On initialise une collection vide
        $raids = [];
        $statsRaids = [];

        if ($managesClub) {
            // 2. Récupérer les membres du club (ceux dans vik_adherer)
            $clubMembers = DB::table('vik_inscrit')
                ->join('vik_adherer', 'vik_inscrit.INS_ID', '=', 'vik_adherer.INS_ID')
                ->where('vik_adherer.CLU_NUM', $club->CLU_NUM)
                ->select(
                    'vik_inscrit.INS_ID',
                    'vik_inscrit.INS_NOM',
                    'vik_inscrit.INS_PRENOM',
                    'vik_inscrit.INS_MAIL',
                    'vik_inscrit.INS_TEL',
                    'vik_inscrit.INS_NAISSANCE',
                    'vik_inscrit.INS_NUM_LICENCE'
                )
                ->get();

            // --- AJOUT : S'assurer que le Gérant (Moi) est dans la liste ---
            // On vérifie si l'ID du user connecté est déjà dans la collection
            if (! $clubMembers->contains('INS_ID', $user->INS_ID)) {
                // Si non, on récupère ses infos et on l'ajoute
                $managerDetails = DB::table('vik_inscrit')
                    ->where('INS_ID', $user->INS_ID)
                    ->select(
                        'INS_ID', 'INS_NOM', 'INS_PRENOM', 'INS_MAIL',
                        'INS_TEL', 'INS_NAISSANCE', 'INS_NUM_LICENCE'
                    )
                    ->first();

                if ($managerDetails) {
                    $clubMembers->push($managerDetails); // On l'ajoute à la liste
                }
            }

            // On trie la liste par nom pour que ce soit propre
            $clubMembers = $clubMembers->sortBy('INS_NOM');

            // 3. Récupérer les raids organisés par ce club
            $raids = DB::table('vik_raid')
                ->where('CLU_NUM', $club->CLU_NUM)
                ->orderBy('RAID_DATE_DEBUT', 'desc')
                ->get();

            // 4. Stats Panel
            $statsRaids = DB::table('vik_raid')
                ->leftJoin('vik_course', 'vik_raid.RAID_NUM', '=', 'vik_course.RAID_NUM')
                ->leftJoin('vik_participer', 'vik_course.COU_NUM', '=', 'vik_participer.COU_NUM')
                ->leftJoin('vik_adherer', 'vik_participer.INS_ID', '=', 'vik_adherer.INS_ID')
                ->where('vik_adherer.CLU_NUM', $club->CLU_NUM)
                ->select('vik_raid.RAID_NOM as nom_raid', DB::raw('count(distinct vik_participer.INS_ID) as nb_inscrits'))
                ->groupBy('vik_raid.RAID_NUM', 'vik_raid.RAID_NOM')
                ->limit(5)
                ->get();
        }

        return view('pages.dashboard', compact('club', 'managesClub', 'clubMembers', 'raids', 'statsRaids'));
    }

    /**
     * Remove a member from the authenticated user's club (dissociation).
     */
    public function removeMember(Request $request, $insId)
    {
        $user = Auth::user();
        $club = DB::table('vik_club')->where('INS_ID', $user->INS_ID)->first();

        if (! $club) {
            abort(403, 'Accès non autorisé.');
        }

        // Prevent removing self
        if ($insId == $user->INS_ID) {
            return redirect()->route('dashboard')->with('error', 'Vous ne pouvez pas supprimer votre propre adhésion.');
        }

        $deleted = DB::table('vik_adherer')
            ->where('INS_ID', $insId)
            ->where('CLU_NUM', $club->CLU_NUM)
            ->delete();

        if ($deleted) {
            return redirect()->route('dashboard')->with('success', 'Membre retiré du club.');
        } else {
            return redirect()->route('dashboard')->with('error', 'Membre introuvable ou déjà retiré.');
        }
    }
}
