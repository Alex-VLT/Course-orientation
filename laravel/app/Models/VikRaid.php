<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VikRaid extends Model
{
    use HasFactory;

    // Nom de la table dans la BDD
    protected $table = 'VIK_RAID';

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
}