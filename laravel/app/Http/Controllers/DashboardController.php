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
     *
     * Shows club management interface for club managers, including:
     * - Club members list
     * - Organized raids
     * - Registration statistics
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Retrieve the managed club (via vik_club.INS_ID)
        $club = DB::table('vik_club')
            ->where('INS_ID', $user->INS_ID)
            ->first();

        $managesClub = $club ? true : false;
        $clubMembers = collect([]); // Initialize an empty collection
        $raids = [];
        $statsRaids = [];

        if ($managesClub) {
            // 2. Retrieve club members (those in vik_adherer)
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

            // Ensure the manager is in the list
            // Check if the connected user's ID is already in the collection
            if (! $clubMembers->contains('INS_ID', $user->INS_ID)) {
                // If not, retrieve their info and add them
                $managerDetails = DB::table('vik_inscrit')
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

            // Sort the list by name for cleaner display
            $clubMembers = $clubMembers->sortBy('INS_NOM');

            // 3. Retrieve raids organized by this club
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
     *
     * @param \Illuminate\Http\Request $request
     * @param int $insId The ID of the member to remove
     * @return \Illuminate\Http\RedirectResponse
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
