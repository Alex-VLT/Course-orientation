<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participate extends Model
{
    protected $table = 'vik_participer';
    protected $fillable = [
        'INS_ID',
        'COU_NUM',
        'EQU_NUM'
    ];
    

    public function user()
    {
        return $this->belongsTo(User::class, 'INS_ID', 'INS_ID');
    }
}
