<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\RaceController;
use App\Http\Controllers\RaidController;
use App\Http\Controllers\VerifInscriptionController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

Route::get('/logs/{file}', function (string $file) {
    return redirect()->to("/laravel/logs/{$file}");
});

Route::get('/laravel/logs/{file}', function (string $file) {
    if ($file === '.' || $file === '..' || str_contains($file, '..') || str_contains($file, '/') || str_contains($file, '\\')) {
        abort(404);
    }

    if (! preg_match('/\A[a-zA-Z0-9][a-zA-Z0-9._-]*\z/', $file)) {
        abort(404);
    }

    $path = storage_path("logs/{$file}.log");

    if (! File::exists($path)) {
        abort(404);
    }

    return response(File::get($path), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
});

// --- PUBLIC ROUTES ---

Route::get('/', [RaidController::class, 'index'])->name('home');
<<<<<<< Updated upstream
Route::get('/mainPage', function () { return view('pages.mainPage'); })->name('mainPage');
Route::get('/a-propos', function () { return view('pages.about'); })->name('about');
=======
Route::get('/mainPage', function () {
    return view('pages.mainPage');
})->name('mainPage');
>>>>>>> Stashed changes

Route::get('/raid/{raid_num}', [RaidController::class, 'show'])->name('raid.show');
Route::get('/course/{cou_num}', [RaceController::class, 'show'])->name('race.show');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');

// Inscription Form
Route::get('/inscForm', [\App\Http\Controllers\inscFormController::class, 'showForm']);
Route::post('/inscForm', [\App\Http\Controllers\inscFormController::class, 'submitForm']);
Route::get('/inscrits/search', [\App\Http\Controllers\inscFormController::class, 'searchInscrits']);

// JSON Validator
Route::get('/validate-equipe/{equ}/{cou}', function (int $equ, int $cou) {
    $result = app(VerifInscriptionController::class)->validateEquipe($equ, $cou, false);

    return response()->json($result);
});

// Legal
Route::get('/mentions-legacy', function () {
    return view('/pages/legal/mentions');
})->name('mentions-legacy');
Route::get('/confidentiality-legacy', function () {
    return view('/pages/legal/privacy');
})->name('confidentiality-legacy');

<<<<<<< Updated upstream
// --- GUEST ROUTES ---
=======
// --- GUEST ROUTES (Login/Register) ---
>>>>>>> Stashed changes
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
    Route::get('/dashboard/raids/create', [RaidController::class, 'create'])->name('raids.create');
    Route::post('/dashboard/raids', [RaidController::class, 'store'])->name('raids.store');
    Route::get('/raids/{raid_num}/edit', [RaidController::class, 'edit'])->name('raids.edit');
    Route::put('/raids/{raid_num}', [RaidController::class, 'update'])->name('raids.update');

    // Create Course
    Route::get('/raid/{raid_num}/courses/create', [RaceController::class, 'create'])->name('race.create');
    Route::post('/raid/{raid_num}/courses', [RaceController::class, 'store'])->name('race.store');

<<<<<<< Updated upstream
    // --- RACE ORGANIZER MANAGEMENT ---
    
    Route::get('/my-races', [RaceController::class, 'organizerIndex'])->name('race.organizer_index');
    
    Route::get('/course/{cou_num}/manage', [RaceController::class, 'manage'])->name('race.manage');
    Route::get('/course/{cou_num}/edit', [RaceController::class, 'edit'])->name('race.edit');
    Route::put('/course/{cou_num}', [RaceController::class, 'update'])->name('race.update');
    
    // Course Actions
=======
    // --- RACE MANAGEMENT (Organizer) ---

    Route::get('/my-races', [RaceController::class, 'organizerIndex'])->name('race.organizer_index');

    // Manage Specific Race
    Route::get('/course/{cou_num}/manage', [RaceController::class, 'manage'])->name('race.manage');
    Route::get('/course/{cou_num}/edit', [RaceController::class, 'edit'])->name('race.edit');
    Route::put('/course/{cou_num}', [RaceController::class, 'update'])->name('race.update');

    // Race Actions
>>>>>>> Stashed changes
    Route::post('/course/{cou_num}/dossards', [RaceController::class, 'generateDossards'])->name('race.dossards');
    Route::post('/course/{cou_num}/results', [RaceController::class, 'uploadResults'])->name('race.results.upload');
    Route::get('/course/{cou_num}/export', [RaceController::class, 'exportResults'])->name('race.export');

    // --- TEAM MANAGEMENT (Organizer) ---
    
    // Delete Team (Fix: Ensure this matches the route called in view)
    Route::delete('/course/{cou_num}/team/{equ_num}', [RaceController::class, 'deleteTeam'])->name('race.team.delete');
<<<<<<< Updated upstream
    
    // Toggle Payment
=======

    // Payment Toggle
>>>>>>> Stashed changes
    Route::post('/course/{cou_num}/team/{equ_num}/payment', [RaceController::class, 'togglePayment'])->name('race.team.payment');

    // Add Member
    Route::post('/course/{cou_num}/team/{equ_num}/add-member', [RaceController::class, 'addTeamMember'])->name('race.team.add_member');
<<<<<<< Updated upstream
    
    // Remove Member
=======

    // Remove Member from Team (Organizer)
>>>>>>> Stashed changes
    Route::delete('/course/{cou_num}/team/{equ_num}/member/{ins_id}', [RaceController::class, 'removeTeamMember'])->name('race.team.remove_member');

    // Update PPS
    Route::post('/course/{cou_num}/team/{equ_num}/member/{ins_id}/pps', [RaceController::class, 'updatePps'])->name('race.team.member.pps');

    // AJAX Search
    Route::get('/api/users/search', [RaceController::class, 'searchUser'])->name('api.users.search');
<<<<<<< Updated upstream
    
    // --- PARTICIPANT ACTIONS ---
    
    // Unsubscribe
    Route::delete('/course/{cou_num}/me', [RaceController::class, 'unsubscribeParticipant'])->name('race.unsubscribe');
    
    // Specific Team Unsubscribe
    Route::delete('/course/{cou_num}/team/{equ_num}/leave', [AuthController::class, 'unsubscribeTeam'])->name('race.team.unsubscribe');
=======

    // If a user wants to leave:
    Route::delete('/course/{cou_num}/me', [RaceController::class, 'unsubscribeParticipant'])->name('race.team.unsubscribe');
>>>>>>> Stashed changes
});
