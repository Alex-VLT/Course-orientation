<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\RaceController;
use App\Http\Controllers\RaidController;
use Illuminate\Support\Facades\Route;

// Main Page route
Route::get('/', function () {
    return view('/pages/mainPage');
});

// PHP/Laravel method to retrieve a route
Route::get('/logs/{file}', function (string $file) {
    $safeName = preg_replace('/[^A-Za-z0-9._-]/', '', $file) ?? '';
    if ($safeName === '') {
        abort(404);
    }

    $filename = $safeName === 'laravel' ? 'laravel.log' : $safeName.'.log';
    $path = storage_path('logs/'.$filename);

    if (! is_file($path)) {
        abort(404);
    }

    $content = file_get_contents($path);
    if ($content === false) {
        abort(500, 'Impossible de lire le fichier log.');
    }

    return view('log', [
        'file' => $filename,
        'content' => $content,
        'route' => null,
    ]);
})->where('file', '[A-Za-z0-9._-]+');

Route::get('/inscForm', [\App\Http\Controllers\inscFormController::class, 'showForm']);
Route::post('/inscForm', [\App\Http\Controllers\inscFormController::class, 'submitForm']);
// AJAX search for existing inscrits (autocomplete)
Route::get('/inscrits/search', [\App\Http\Controllers\inscFormController::class, 'searchInscrits']);

Route::middleware('guest')->group(function () {
    // Displaying login page
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    // Action taken when the visitor logs in
    Route::post('/login', [AuthController::class, 'login']);
    
    // Displaying register page
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    // Action taken when the visitor registers
    Route::post('/register', [AuthController::class, 'register']);

    // Displaying forgot password page
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    // Action to send the email to reset the password
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

    // Displaying reset password page (takes the reset token as a parameter and the user's email)
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    // Action to reset the password
    Route::post('/reset-password', [AuthController::class, 'updatePassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    // Page of logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/mainPage', function () {
        return view('pages.mainPage');
    })->name('mainPage');

    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Supprimer un membre du club (dissociation)
    Route::delete('/dashboard/members/{ins_id}', [\App\Http\Controllers\DashboardController::class, 'removeMember'])
        ->name('dashboard.members.destroy');

    Route::get('/dashboard/raids/create', [\App\Http\Controllers\RaidController::class, 'create'])->name('raids.create');
    Route::post('/dashboard/raids', [\App\Http\Controllers\RaidController::class, 'store'])->name('raids.store');

    // Raid management
    Route::get('/raids/manage', [\App\Http\Controllers\RaidController::class, 'managerIndex'])->name('raids.manager');
    Route::get('/raids/{raid_num}/edit', [\App\Http\Controllers\RaidController::class, 'edit'])->name('raids.edit');
    Route::put('/raids/{raid_num}', [\App\Http\Controllers\RaidController::class, 'update'])->name('raids.update');
});


// Route to the homepage
Route::get('/', [RaidController::class, 'index'])->name('home');

// JSON test route to validate a team (returns the validation result)
Route::get('/validate-equipe/{equ}/{cou}', function (int $equ, int $cou) {
    $result = app(\App\Http\Controllers\VerifInscriptionController::class)
        ->validateEquipe($equ, $cou, false);

    return response()->json($result);
});

Route::get('/raid/{raid_num}', [RaidController::class, 'show'])->name('raid.show');

Route::get('/course/{cou_num}', [RaceController::class, 'show'])->name('race.show');

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

  // Page listing all the manager's errands
  Route::get('/my-races', [\App\Http\Controllers\RaceController::class, 'organizerIndex'])
        ->name('race.organizer_index');

  // Race management page
  Route::get('/course/{cou_num}/manage', [\App\Http\Controllers\RaceController::class, 'manage'])
        ->name('race.manage');

  // Action to validate the payment
  Route::post('/course/{cou_num}/team/{equ_num}/payment', [\App\Http\Controllers\RaceController::class, 'togglePayment'])
        ->name('race.team.payment');

    Route::get('/course/{cou_num}/edit', [\App\Http\Controllers\RaceController::class, 'edit'])
        ->name('race.edit');

  // Save changes of race
  Route::put('/course/{cou_num}', [\App\Http\Controllers\RaceController::class, 'update'])
        ->name('race.update');

  Route::delete('/course/{cou_num}/team/{equ_num}', [\App\Http\Controllers\RaceController::class, 'deleteTeam'])
     ->name('race.team.delete');

  // Export CSV Results
    Route::get('/course/{cou_num}/export', [\App\Http\Controllers\RaceController::class, 'exportResults'])
        ->name('race.export');

  // Manage Team Members
  Route::post('/course/{cou_num}/team/{equ_num}/add-member', [\App\Http\Controllers\RaceController::class, 'addTeamMember'])
      ->name('race.team.add_member');
      
  Route::delete('/course/{cou_num}/team/{equ_num}/member/{ins_id}', [\App\Http\Controllers\RaceController::class, 'removeTeamMember'])
      ->name('race.team.remove_member');

  // Update PPS for a specific participation
  Route::post('/course/{cou_num}/team/{equ_num}/member/{ins_id}/pps', [\App\Http\Controllers\RaceController::class, 'updatePps'])
      ->name('race.team.member.pps');

  Route::post('/course/{cou_num}/results', [\App\Http\Controllers\RaceController::class, 'uploadResults'])
      ->name('race.results.upload');

  // Route AJAX pour rechercher un utilisateur
  Route::get('/api/users/search', [\App\Http\Controllers\RaceController::class, 'searchUser'])
        ->name('api.users.search');

  // Ajout membre (via ID maintenant)
  Route::post('/course/{cou_num}/team/{equ_num}/add-member', [\App\Http\Controllers\RaceController::class, 'addTeamMember'])
        ->name('race.team.add_member');
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
