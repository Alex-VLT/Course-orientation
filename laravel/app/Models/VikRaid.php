<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VikRaid extends Model
{
    protected $table = 'VIK_RAID';
    protected $primaryKey = 'RAID_NUM';
    public $incrementing = false; 
    public $timestamps = false;
    use HasFactory;
    
    // Nom de la table dans la BDD
    
    // Clé primaire personnalisée
    protected $primaryKey = 'RAID_NUM';
    
    // Indique si la clé primaire est auto-incrémentée (Oui c'est un INT)
    public $incrementing = true;
    
    // Type de la clé primaire
    protected $keyType = 'int';
    
    // Désactiver les timestamps si les colonnes created_at/updated_at n'existent pas dans ton script SQL
    public $timestamps = false;
    
    // Colonnes remplissables
    protected $fillable = [
        'CLU_NUM', 'INS_ID', 'RAID_NOM', 'RAID_DATE_DEBUT_INSCRI',
        'RAID_DATE_FIN_INSCRI', 'RAID_DATE_DEBUT', 'RAID_DATE_FIN',
        'RAID_CONTACT', 'RAID_LIEN_SITE_WEB', 'RAID_LATITUDE',
        'RAID_LONGITUDE', 'RAID_ILLUSTRATION'
    ];

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

}
