<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class inscFormController extends Controller
{
    public function showForm()
    {
        return view('/pages/inscForm');
    }
}