<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VikRace;

class inscFormController extends Controller
{
    public function showForm(Request $request)
    {
        $course = $request->query('course');
        $courseName = null;

        if ($course) {
            $race = VikRace::query()->find($course);
            if ($race) {
                $courseName = $race->COU_NOM;
            } else {
                // invalid course param -> ignore
                $course = null;
            }
        }

        return view('/pages/inscForm', compact('course', 'courseName'));
    }
}
