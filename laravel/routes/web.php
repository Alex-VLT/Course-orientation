<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RaceController;
use App\Http\Controllers\RaidController;
use App\Http\Controllers\ContactController;

Route::get('/', function () {
    return view('/pages/mainPage');
});

Route::get('/logs/{file}', function (string $file) {
  if ($file === 'laravel') {
    $content = Storage::disk('laravelLog')->get('laravel.log');
    return view('log', [
        'file'=>'laravel.log', 
        'content'=>$content, 
        'route'=>route('logs.delete', ['disk'=>'laravelLog', 'file'=>'laravel.log'])
        ]);
  } else {
    Log::debug("accessing log path : ".Storage::disk('log')->path("$file.log"));
    if (Storage::disk('log')->exists("$file.log")) {
      Log::debug("exists : OK");
      $content = Storage::disk('log')->get("$file.log");
      return view('log', [
        'file'=>"$file.log", 
        'content'=>$content, 
        'route'=>null
        ]);
    } else {
      Log::debug("exists : OK");
      return "<h1>$file.log</h1><p style='color:red'>Not Found</p>";
    }
  }
});

Route::get('/inscForm', [\App\Http\Controllers\inscFormController::class, 'showForm']);
Route::post('/inscForm', [\App\Http\Controllers\inscFormController::class, 'submitForm']);
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'updatePassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/mainPage', function () {
        return view('pages.mainPage');
    })->name('mainPage');

    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/dashboard/raids/create', [\App\Http\Controllers\RaidController::class, 'create'])->name('raids.create');
    Route::post('/dashboard/raids', [\App\Http\Controllers\RaidController::class, 'store'])->name('raids.store');
});


// Route pour la page d'accueil
Route::get('/', [RaidController::class, 'index'])->name('home');

// Route::post('/logs/{disk}/{file}/delete', function(string $disk, string $file) {
//   Storage::disk($disk)->delete($file);
//   return Redirect::back();
// }) -> name("logs.delete");

// Route de test JSON pour valider une équipe (renvoie le résultat de validation)
Route::get('/validate-equipe/{equ}/{cou}', function (int $equ, int $cou) {
    $result = app(\App\Http\Controllers\VerifInscriptionController::class)
                ->validateEquipe($equ, $cou, false);
    return response()->json($result);
});

Route::get('/raid/{raid_num}', [RaidController::class, 'show'])->name('raid.show');

Route::get('/course/{cou_num}',[RaceController::class, 'show'])->name('race.show');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');
Route::middleware('auth')->group(function () {
  Route::get('/course/{cou_num}/manage', [\App\Http\Controllers\RaceController::class, 'manage'])->name('race.manage');
  Route::post('/course/{cou_num}/dossards', [\App\Http\Controllers\RaceController::class, 'generateDossards'])->name('race.dossards');
  Route::post('/course/{cou_num}/results', [\App\Http\Controllers\RaceController::class, 'uploadResults'])->name('race.results.upload');
  Route::post('/course/{cou_num}/validate', [\App\Http\Controllers\RaceController::class, 'validateCourse'])->name('race.validate');
});

// Course creation under a raid (only for raid responsable)
Route::get('/raid/{raid_num}/courses/create', [\App\Http\Controllers\RaceController::class, 'create'])->name('race.create')->middleware('auth');
Route::post('/raid/{raid_num}/courses', [\App\Http\Controllers\RaceController::class, 'store'])->name('race.store')->middleware('auth');


Route::get('/profil', [AuthController::class, 'profil'])->middleware('auth')->name('profil');
Route::post('/profil', [AuthController::class, 'updateProfil'])->middleware('auth')->name('profil.update');

Route::delete('/profil', [AuthController::class, 'deleteAccount'])
    ->middleware('auth')
    ->name('profil.delete');
