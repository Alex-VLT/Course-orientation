<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VikEquipe extends Model
{
    protected $table = 'vik_equipe';
    public $timestamps = false;
    
    public $incrementing = false; 

    protected $fillable = [
        'COU_NUM', 'EQU_NUM', 'INS_ID', 'EQU_NOM', 
        'EQU_ORDRE_ARRIVEE', 'EQU_TEMPS', 'EQU_POINTS', 
        'EQU_PAIEMENT_VALIDE'
    ];

    public function participations()
    {
        return $this->hasMany(Participate::class, 'EQU_NUM', 'EQU_NUM');
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'INS_ID', 'INS_ID');
    }
}