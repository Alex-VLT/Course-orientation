<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RaceController;
use App\Http\Controllers\RaidController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\VerifInscriptionController;

 // PHP/Laravel method to retrieve a route
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

// --- PUBLIC ROUTES ---

Route::get('/', [RaidController::class, 'index'])->name('home');
Route::get('/mainPage', function () { return view('pages.mainPage'); })->name('mainPage');

Route::get('/raid/{raid_num}', [RaidController::class, 'show'])->name('raid.show');
Route::get('/course/{cou_num}', [RaceController::class, 'show'])->name('race.show');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');

// Inscription Form (Public or Guest logic?)
Route::get('/inscForm', [\App\Http\Controllers\inscFormController::class, 'showForm']);
Route::post('/inscForm', [\App\Http\Controllers\inscFormController::class, 'submitForm']);
Route::get('/inscrits/search', [\App\Http\Controllers\inscFormController::class, 'searchInscrits']);

// JSON Validator (Public)
Route::get('/validate-equipe/{equ}/{cou}', function (int $equ, int $cou) {
    $result = app(VerifInscriptionController::class)->validateEquipe($equ, $cou, false);
    return response()->json($result);
});

// Logs Viewer
Route::get('/logs/{file}', function (string $file) {
    // ... (Keep your log logic here) ...
    return "Log View Placeholder"; 
});

// Legal
Route::get('/mentions-legacy', function () { return view('/pages/legal/mentions'); })->name('mentions-legacy');
Route::get('/confidentiality-legacy', function () { return view('/pages/legal/privacy'); })->name('confidentiality-legacy');


// --- GUEST ROUTES (Login/Register) ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    
    // Password Reset
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'updatePassword'])->name('password.update');
});


// --- AUTHENTICATED ROUTES ---
Route::middleware('auth')->group(function () {
    
    // Auth Actions
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard & Profile
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::delete('/dashboard/members/{ins_id}', [\App\Http\Controllers\DashboardController::class, 'removeMember'])->name('dashboard.members.destroy');
    
    Route::get('/profil', [AuthController::class, 'profil'])->name('profil');
    Route::put('/profil', [AuthController::class, 'updateProfil'])->name('profil.update');
    Route::delete('/profil', [AuthController::class, 'deleteAccount'])->name('profil.delete');
    Route::delete('/compte/supprimer', [AuthController::class, 'deleteAccount'])->name('account.delete');

    // Clubs Management
    Route::get('/clubs', [ClubController::class, 'index'])->name('clubs.manage');
    Route::post('/clubs', [ClubController::class, 'store'])->name('clubs.store');
    Route::put('/clubs/{club}', [ClubController::class, 'update'])->name('clubs.update');
    Route::delete('/inscrits/{inscrit}', [ClubController::class, 'destroyInscrit'])->name('inscrits.destroy');

    // Raids Management
    Route::get('/raids/manage', [RaidController::class, 'managerIndex'])->name('raids.manager');
    Route::get('/dashboard/raids/create', [RaidController::class, 'create'])->name('raids.create'); // Old alias?
    Route::post('/dashboard/raids', [RaidController::class, 'store'])->name('raids.store');
    Route::get('/raids/{raid_num}/edit', [RaidController::class, 'edit'])->name('raids.edit');
    Route::put('/raids/{raid_num}', [RaidController::class, 'update'])->name('raids.update');

    // Create Course (Raid Responsible)
    Route::get('/raid/{raid_num}/courses/create', [RaceController::class, 'create'])->name('race.create');
    Route::post('/raid/{raid_num}/courses', [RaceController::class, 'store'])->name('race.store');

    // --- RACE MANAGEMENT (Organizer) ---
    
    Route::get('/my-races', [RaceController::class, 'organizerIndex'])->name('race.organizer_index');
    
    // Manage Specific Race
    Route::get('/course/{cou_num}/manage', [RaceController::class, 'manage'])->name('race.manage');
    Route::get('/course/{cou_num}/edit', [RaceController::class, 'edit'])->name('race.edit');
    Route::put('/course/{cou_num}', [RaceController::class, 'update'])->name('race.update');
    
    // Race Actions
    Route::post('/course/{cou_num}/dossards', [RaceController::class, 'generateDossards'])->name('race.dossards');
    Route::post('/course/{cou_num}/results', [RaceController::class, 'uploadResults'])->name('race.results.upload');
    Route::get('/course/{cou_num}/export', [RaceController::class, 'exportResults'])->name('race.export');

    // --- TEAM MANAGEMENT (Organizer Action) ---
    // This route is for the ORGANIZER deleting a team
    Route::delete('/course/{cou_num}/team/{equ_num}', [RaceController::class, 'deleteTeam'])->name('race.team.delete');
    
    // Payment Toggle
    Route::post('/course/{cou_num}/team/{equ_num}/payment', [RaceController::class, 'togglePayment'])->name('race.team.payment');

    // Add Member to Team (Organizer)
    Route::post('/course/{cou_num}/team/{equ_num}/add-member', [RaceController::class, 'addTeamMember'])->name('race.team.add_member');
    
    // Remove Member from Team (Organizer)
    Route::delete('/course/{cou_num}/team/{equ_num}/member/{ins_id}', [RaceController::class, 'removeTeamMember'])->name('race.team.remove_member');

Route::get('/course/{cou_num}',[RaceController::class, 'show'])->name('race.show');
Route::get('/a-propos', function () { return view('pages.about'); })->name('about');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');
// Clubs management (admin area)
Route::middleware('auth')->group(function () {
  // Management page
  Route::get('/clubs', [ClubController::class, 'index'])->name('clubs.manage');

  // AJAX endpoints for creating/updating clubs
  Route::post('/clubs', [ClubController::class, 'store'])->name('clubs.store');
  Route::put('/clubs/{club}', [ClubController::class, 'update'])->name('clubs.update');

  // Delete an inscrit (used from management UI)
  Route::delete('/inscrits/{inscrit}', [ClubController::class, 'destroyInscrit'])->name('inscrits.destroy');
});
Route::middleware('auth')->group(function () {
  Route::post('/course/{cou_num}/dossards', [\App\Http\Controllers\RaceController::class, 'generateDossards'])->name('race.dossards');
  Route::post('/course/{cou_num}/results', [\App\Http\Controllers\RaceController::class, 'uploadResults'])->name('race.results.upload');

  // Page listant toutes les courses du responsable
  Route::get('/my-races', [\App\Http\Controllers\RaceController::class, 'organizerIndex'])
        ->name('race.organizer_index');

  // Page de gestion d'une course
  Route::get('/course/{cou_num}/manage', [\App\Http\Controllers\RaceController::class, 'manage'])
        ->name('race.manage');

  // Action pour valider le paiement
  Route::post('/course/{cou_num}/team/{equ_num}/payment', [\App\Http\Controllers\RaceController::class, 'togglePayment'])
        ->name('race.team.payment');

  Route::get('/course/{cou_num}/edit', [\App\Http\Controllers\RaceController::class, 'edit'])
    ->name('race.edit');

  // Sauvegarder les modifications
  Route::put('/course/{cou_num}', [\App\Http\Controllers\RaceController::class, 'update'])
        ->name('race.update');

  Route::delete('/course/{cou_num}/team/{equ_num}', [\App\Http\Controllers\RaceController::class, 'deleteTeam'])
     ->name('race.team.delete');
});

// Course creation under a raid (only for raid responsable)
Route::get('/raid/{raid_num}/courses/create', [\App\Http\Controllers\RaceController::class, 'create'])->name('race.create')->middleware('auth');
Route::post('/raid/{raid_num}/courses', [\App\Http\Controllers\RaceController::class, 'store'])->name('race.store')->middleware('auth');


Route::get('/profil', [AuthController::class, 'profil'])->middleware('auth')->name('profil');
Route::post('/profil', [AuthController::class, 'updateProfil'])->middleware('auth')->name('profil.update');
Route::put('/profil', [AuthController::class, 'updateProfil'])->name('profil.update');
Route::delete('/compte/supprimer', [AuthController::class, 'deleteAccount'])->name('account.delete');
Route::delete('/profil', [AuthController::class, 'deleteAccount'])
    ->middleware('auth')
    ->name('profil.delete');
Route::delete('/course/{cou_num}/team/{equ_num}', [AuthController::class, 'unsubscribeTeam'])
    ->name('race.team.unsubscribe');
Route::put('/course/{cou_num}/team/{equ_num}/member/{ins_id}/pps', [AuthController::class, 'updateMemberPps'])
    ->name('race.team.member.pps');


// Allow a logged user to unsubscribe themselves from a course (not the whole team)
Route::delete('/course/{cou_num}/me', [\App\Http\Controllers\RaceController::class, 'unsubscribeParticipant'])
  ->name('race.unsubscribe');

// Legal Routes
Route::get('/mentions-legacy', function () {
    return view('/pages/legal/mentions');
})->name('mentions-legacy');
Route::get('/confidentiality-legacy', function () {
    return view('/pages/legal/privacy');
})->name('confidentiality-legacy');
    // Update PPS (Organizer)
    Route::post('/course/{cou_num}/team/{equ_num}/member/{ins_id}/pps', [RaceController::class, 'updatePps'])->name('race.team.member.pps');

    // AJAX User Search
    Route::get('/api/users/search', [RaceController::class, 'searchUser'])->name('api.users.search');
    
    // If a user wants to leave:
    Route::delete('/course/{cou_num}/me', [RaceController::class, 'unsubscribeParticipant'])->name('race.team.unsubscribe');
});
