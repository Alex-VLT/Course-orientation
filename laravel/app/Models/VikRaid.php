<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VikRaid extends Model
{
    use HasFactory;

    protected $table = 'VIK_RAID';
    protected $primaryKey = 'RAID_NUM';
    
    // Important car tu gères l'ID manuellement
    public $incrementing = false; 
    public $timestamps = false;
    protected $keyType = 'int';

    protected $fillable = [
        'RAID_NUM',
        'CLU_NUM',
        'INS_ID',
        'RAID_NOM',
        'RAID_DATE_DEBUT_INSCRI',
        'RAID_DATE_FIN_INSCRI',
        'RAID_DATE_DEBUT',
        'RAID_DATE_FIN',
        'RAID_CONTACT',
        // 'RAID_CONTACT_MAIL', // SUPPRIMÉ : Utilise RAID_CONTACT s'il n'y a qu'un champ
        'RAID_LIEN_SITE_WEB',
        'RAID_LATITUDE',
        'RAID_LONGITUDE',
        'RAID_ILLUSTRATION',
    ];

    protected $casts = [
        'RAID_DATE_DEBUT_INSCRI' => 'date',
        'RAID_DATE_FIN_INSCRI'   => 'date',
        'RAID_DATE_DEBUT'        => 'date',
        'RAID_DATE_FIN'          => 'date',
        'RAID_LATITUDE'          => 'float',
        'RAID_LONGITUDE'         => 'float',
    ];

    public function courses()
    {
        return $this->hasMany(\App\Models\VikRace::class, 'RAID_NUM', 'RAID_NUM');
    }

    public function club()
    {
        return $this->belongsTo(\App\Models\VikClub::class, 'CLU_NUM', 'CLU_NUM');
    }

    public function responsable()
    {
        // CORRECTION : C'est belongsTo car la clé étrangère INS_ID est dans VIK_RAID
        return $this->belongsTo(\App\Models\User::class, 'INS_ID', 'INS_ID');
    }
}