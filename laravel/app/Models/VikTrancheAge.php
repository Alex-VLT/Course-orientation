<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VikTrancheAge extends Model
{
    protected $table = 'vik_tranche_age';
    protected $primaryKey = 'TRA_ID';
    public $incrementing = false;
    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = [
        'TRA_ID',
        'TRA_AGE_MIN',
        'TRA_AGE_MAX',
    ];

    protected $casts = [
        'TRA_ID' => 'integer',
        'TRA_AGE_MIN' => 'integer',
        'TRA_AGE_MAX' => 'integer',
    ];
}
