<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class vikInscForm extends Model
{
    protected $table = 'vik_equipe';
    protected $fillable = [
        'COU_NUM',
        'EQU_NUM',
        'INS_ID',
        'EQU_NOM',
        'EQU_ORDRE_ARRIVEE',
        'EQU_TEMPS',
        'EQU_POINTS'
    ];


}
