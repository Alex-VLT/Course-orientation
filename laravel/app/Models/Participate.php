<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class vikInscForm extends Model
{
    protected $table = 'vik_participer';
    protected $fillable = [
        'INS_ID',
        'COU_NUM',
        'EQU_NUM'
    ];


}
