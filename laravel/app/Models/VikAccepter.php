<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VikAccepter extends Model
{
    protected $table = 'vik_accepter';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'COU_NUM',
        'TRA_ID',
        'ACC_PRIX',
    ];

    protected $casts = [
        'COU_NUM' => 'integer',
        'TRA_ID' => 'integer',
        'ACC_PRIX' => 'decimal:2',
    ];

    public function tranche()
    {
        return $this->belongsTo(VikTrancheAge::class, 'TRA_ID', 'TRA_ID');
    }

    public function race()
    {
        return $this->belongsTo(VikRace::class, 'COU_NUM', 'COU_NUM');
    }
}
