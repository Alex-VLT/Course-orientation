<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

Route::get('/', function () {
    return view('/pages/exemple');
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

Route::post('/logs/{disk}/{file}/delete', function(string $disk, string $file) {
  Storage::disk($disk)->delete($file);
  return Redirect::back();
}) -> name("logs.delete");

// Route de test JSON pour valider une équipe (renvoie le résultat de validation)
Route::get('/validate-equipe/{equ}/{cou}', function (int $equ, int $cou) {
    $result = app(\App\Http\Controllers\VerifInscriptionController::class)
                ->validateEquipe($equ, $cou, false);
    return response()->json($result);
});

