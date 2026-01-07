<?php
namespace App\Http\Controllers;
use App\Models\VikRace;

class insFormController extends Controller {

    public function chercheCourse()  {
        $courses = VikRace::find(request()->query('course'));
        return view('pages.inscForm', ['courses' => $courses]);
    }
}
?>