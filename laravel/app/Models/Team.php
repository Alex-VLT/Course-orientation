<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $table = 'vik_equipe';
    protected $fillable = [
        'COU_NUM',
        'INS_ID',
        'EQU_NOM',
        'EQU_ORDRE_ARRIVEE',
        'EQU_TEMPS',
        'EQU_POINTS'
    ];

    protected function casts(): array
    {
        return [
            'EQU_ORDRE_ARRIVEE' => 'integer',
            'EQU_TEMPS' => 'integer',
            'EQU_POINTS' => 'integer',
        ];
    }


}
