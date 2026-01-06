<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RaidController;

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
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/dashboard', function () {
        return "Bienvenue " . Auth::user()->INS_PRENOM;
    })->name('dashboard');
});

// Route::post('/logs/{disk}/{file}/delete', function(string $disk, string $file) {
//   Storage::disk($disk)->delete($file);
//   return Redirect::back();
// }) -> name("logs.delete");



Route::get('/raid/{raid_num}', [RaidController::class, 'show'])->name('raid.show');

Route::get('/course/{cou_num}', function () {
    abort(501, "Détail course pas encore implémenté");
})->name('course.show');

Route::get('/profil', [AuthController::class, 'profile'])->middleware('auth')->name('profil');
Route::post('/profil', [AuthController::class, 'updateProfile'])->middleware('auth')->name('profil.update');
Route::delete('/profil', [AuthController::class, 'deleteAccount'])
    ->middleware('auth')
    ->name('profil.delete');
