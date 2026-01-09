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
        
    // 1. Retrieve the managed club (via VIK_CLUB.INS_ID)
    $club = DB::table('VIK_CLUB')
                ->where('INS_ID', $user->INS_ID)
                ->first();

        $managesClub = $club ? true : false;
    $clubMembers = collect([]); // Initialize an empty collection
        $raids = [];
        $statsRaids = [];

        if ($managesClub) {
            // 2. Retrieve the club members (those in VIK_ADHERER)
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

            // --- ADD: Ensure the manager (current user) is present in the list ---
            // Check if the logged-in user's ID is already in the collection
            if (!$clubMembers->contains('INS_ID', $user->INS_ID)) {
                // If not, fetch their details and append them
                $managerDetails = DB::table('VIK_INSCRIT')
                    ->where('INS_ID', $user->INS_ID)
                    ->select(
                        'INS_ID', 'INS_NOM', 'INS_PRENOM', 'INS_MAIL', 
                        'INS_TEL', 'INS_NAISSANCE', 'INS_NUM_LICENCE'
                    )
                    ->first();
                
                if ($managerDetails) {
                    $clubMembers->push($managerDetails); // Add to the list
                }
            }
            
            // Sort the list by last name for a tidy presentation
            $clubMembers = $clubMembers->sortBy('INS_NOM');

        // 3. Retrieve raids organized by this club
            $raids = DB::table('VIK_RAID')
                    ->where('CLU_NUM', $club->CLU_NUM) 
                    ->orderBy('RAID_DATE_DEBUT', 'desc')
                    ->get();

        // 4. Stats panel
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

    /**
     * Remove a member from the authenticated user's club (dissociation).
     */
    public function removeMember(Request $request, $insId)
    {
        $user = Auth::user();
        $club = DB::table('VIK_CLUB')->where('INS_ID', $user->INS_ID)->first();

        if (!$club) {
            abort(403, 'Accès non autorisé.');
        }

        // Prevent removing self
        if ($insId == $user->INS_ID) {
            return redirect()->route('dashboard')->with('error', 'Vous ne pouvez pas supprimer votre propre adhésion.');
        }

        $deleted = DB::table('VIK_ADHERER')
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
