<?php

namespace App\Http\Controllers;

use App\Models\VikRaid;

class RaidController extends Controller
{
    public function show(int $raid_num)
    {
        $raid = VikRaid::query()
            ->with(['courses' => function ($q) {
                $q->orderBy('COU_DATE_DEPART', 'asc');
            }])
            ->findOrFail($raid_num);

        if (!empty($raid->RAID_LIEN_SITE_WEB) && !preg_match('~^https?://~i', $raid->RAID_LIEN_SITE_WEB)) {
            $raid->RAID_LIEN_SITE_WEB = 'https://' . $raid->RAID_LIEN_SITE_WEB;
        }

        return view('pages.raid', compact('raid'));
    }
}
