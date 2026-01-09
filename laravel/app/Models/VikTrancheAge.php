<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * VikTrancheAge Model (VIK_TRANCHE_AGE table)
 *
 * Represents an age bracket/category used to group participants by age range.
 * Age brackets are used to calculate course fees and organize participants.
 *
 * @property int $TRA_ID Primary key - Age bracket ID
 * @property int $TRA_AGE_MIN Minimum age for this bracket
 * @property int $TRA_AGE_MAX Maximum age for this bracket
 */
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
