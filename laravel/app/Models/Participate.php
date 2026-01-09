<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participate extends Model
{
    protected $table = 'vik_participer';
    public $timestamps = false;


    protected $fillable = [
        'INS_ID',
        'COU_NUM',
        'EQU_NUM',
        'PAR_PARTICIPE',
        'PAR_NUM_PPS'
    ];

    /**
     * Relation: the inscrit/user associated with this participation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'INS_ID', 'INS_ID');
    }
}