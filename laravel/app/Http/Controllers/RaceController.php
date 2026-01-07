<?php

namespace App\Http\Controllers;

use App\Models\VikRace;
use App\Models\VikRaid;
use App\Models\User;
use App\Http\Requests\StoreCourseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RaceController extends Controller
{
    public function show(int $race_num)
    {
        $race = VikRace::query()
            ->with(['raid', 'acceptances.tranche'])
            ->findOrFail($race_num);

        $teamsCount = DB::table('VIK_EQUIPE')->where('COU_NUM', $race_num)->count();

        return view('pages.race', compact('race', 'teamsCount'));
    }

    public function create(int $raid_num, Request $request)
    {
        $raid = VikRaid::findOrFail($raid_num);

        // Only the raid responsible may create courses (raid INS_ID is the responsable)
        $user = $request->user();
        if ((int)$raid->INS_ID !== (int)$user->INS_ID) {
            abort(403, 'Seul le responsable du raid peut créer des courses.');
        }

        $responsiblesQuery = User::query()->whereNotNull('INS_NUM_LICENCE');
        if (\Illuminate\Support\Facades\Schema::hasColumn('VIK_INSCRIT', 'INS_NUM_PPS')) {
            $responsiblesQuery->orWhereNotNull('INS_NUM_PPS');
        }
        $responsibles = $responsiblesQuery->get();

        return view('pages.courses.create', compact('raid', 'responsibles'));
    }

    public function store(int $raid_num, StoreCourseRequest $request)
    {
        $raid = VikRaid::findOrFail($raid_num);
        $user = $request->user();
        if ((int)$raid->INS_ID !== (int)$user->INS_ID) {
            abort(403, 'Seul le responsable du raid peut créer des courses.');
        }

        $data = $request->validated();
        $data['RAID_NUM'] = $raid->RAID_NUM;

        // Generate a COU_NUM primary key if necessary (non-incrementing PK)
        $max = VikRace::max('COU_NUM');
        $next = $max ? ((int)$max + 1) : 1;
        $data['COU_NUM'] = $next;

        $race = VikRace::create($data);

        return redirect()->route('race.show', $race->COU_NUM)->with('success', 'Course créée.');
    }

    /**
     * Management UI for a course (only course responsible may access)
     */
    public function manage(int $cou_num, Request $request)
    {
        $race = VikRace::with('dossards')->findOrFail($cou_num);
        $user = $request->user();
        if ((int)$race->INS_ID !== (int)$user->INS_ID) {
            abort(403, 'Seul le responsable de la course peut gérer cette page.');
        }

        return view('pages.courses.manage', compact('race'));
    }

    public function generateDossards(int $cou_num, Request $request)
    {
        $race = VikRace::findOrFail($cou_num);
        $user = $request->user();
        if ((int)$race->INS_ID !== (int)$user->INS_ID) {
            abort(403);
        }

        $count = (int)$request->input('count', 100);
        $start = $race->dossards()->max('DOSS_NUM') ?? 0;
        $toCreate = [];
        for ($i = 1; $i <= $count; $i++) {
            $toCreate[] = ['COU_NUM' => $race->COU_NUM, 'DOSS_NUM' => $start + $i, 'created_at' => now(), 'updated_at' => now()];
        }

        \App\Models\VikDossard::insert($toCreate);

        return redirect()->route('race.manage', $race->COU_NUM)->with('success', "{$count} dossards générés.");
    }

    public function uploadResults(int $cou_num, Request $request)
    {
        $race = VikRace::findOrFail($cou_num);
        $user = $request->user();
        if ((int)$race->INS_ID !== (int)$user->INS_ID) {
            abort(403);
        }

        $request->validate(['results' => ['required', 'file', 'mimes:csv,txt']]);
        $file = $request->file('results');
        $path = $file->storeAs('results', 'course_'.$race->COU_NUM.'_'.time().'.csv');

        return redirect()->route('race.manage', $race->COU_NUM)->with('success', 'Fichier enregistré : ' . $path);
    }

    public function validateCourse(int $cou_num, Request $request)
    {
        $race = VikRace::findOrFail($cou_num);
        $user = $request->user();
        if ((int)$race->INS_ID !== (int)$user->INS_ID) {
            abort(403);
        }

        $race->COU_VALIDE = true;
        $race->save();

        return redirect()->route('race.manage', $race->COU_NUM)->with('success', 'Course validée.');
    }
}
