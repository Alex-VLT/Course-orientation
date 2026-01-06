<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'VIK_INSCRIT';

    protected $primaryKey = 'INS_ID';
    
    public $incrementing = true; 

    public $timestamps = false;

    protected $fillable = [
        'INS_NOM',
        'INS_PRENOM',
        'INS_NAISSANCE',
        'INS_CODE_PO',
        'INS_MAIL',
        'INS_VILLE',
        'INS_ADRESSE',
        'INS_TEL',
        'INS_NUM_LICENCE',
        'INS_MDP',
    ];

    protected $hidden = [
        'INS_MDP', 
    ];

    public function getAuthPassword()
    {
        return $this->INS_MDP;
    }
    
    public function getRememberTokenName()
    {
        return ''; 
    }

    
}