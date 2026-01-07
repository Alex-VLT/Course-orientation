<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VikRace;
use App\Models\VikRaid;
use App\Models\VikClub;
use Illuminate\Support\Facades\Auth; 
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Carbon\Carbon;


class DashboardController extends Controller
{
    /**
     * Display the dashboard for the authenticated user.
     */
    public function index()
    {
        $user = Auth::user();
        
        // 1. Récupération du club géré (Via VIK_CLUB.INS_ID)
        $club = DB::table('VIK_CLUB')
                ->where('INS_ID', $user->INS_ID)
                ->first();

        $managesClub = $club ? true : false;
        $clubMembers = collect([]); // On initialise une collection vide
        $raids = [];
        $statsRaids = [];

        if ($managesClub) {
            // 2. Récupérer les membres du club (ceux dans VIK_ADHERER)
            $clubMembers = DB::table('VIK_INSCRIT')
                            ->join('VIK_ADHERER', 'VIK_INSCRIT.INS_ID', '=', 'VIK_ADHERER.INS_ID')
                            ->where('VIK_ADHERER.CLU_NUM', $club->CLU_NUM)
                            ->select(
                                'VIK_INSCRIT.INS_ID',
                                'VIK_INSCRIT.INS_NOM', 
                                'VIK_INSCRIT.INS_PRENOM', 
                                'VIK_INSCRIT.INS_MAIL', 
                                'VIK_INSCRIT.INS_TEL', 
                                'VIK_INSCRIT.INS_NAISSANCE',
                                'VIK_INSCRIT.INS_NUM_LICENCE'
                            )
                            ->get();

            // --- AJOUT : S'assurer que le Gérant (Moi) est dans la liste ---
            // On vérifie si l'ID du user connecté est déjà dans la collection
            if (!$clubMembers->contains('INS_ID', $user->INS_ID)) {
                // Si non, on récupère ses infos et on l'ajoute
                $managerDetails = DB::table('VIK_INSCRIT')
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
            $raids = DB::table('VIK_RAID')
                    ->where('CLU_NUM', $club->CLU_NUM) 
                    ->orderBy('RAID_DATE_DEBUT', 'desc')
                    ->get();

            // 4. Stats Panel
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

        return view('pages.dashboard', compact('club', 'managesClub', 'clubMembers', 'raids', 'statsRaids'));
    }
}
