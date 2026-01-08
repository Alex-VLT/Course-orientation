<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VikRace extends Model
{
    protected $table = 'vik_course';

    protected $primaryKey = 'COU_NUM';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = [
        'COU_NUM',
        'INS_ID',
        'TYP_NUM',
        'RAID_NUM',
        'COU_NOM',
        'COU_DUREE',
        'COU_DIFFICULTE',
        'COU_DATE_DEPART',
        'COU_DATE_FIN',
        'COU_NB_PART_MIN',
        'COU_NB_PART_MAX',
        'COU_NB_EQU_MIN',
        'COU_NB_EQU_MAX',
        'COU_PART_PAR_EQU_MAX',
        'COU_PRIX_REPAS',
        'COU_REDUC_LICENCIE',
        'COU_AGE_A',
        'COU_AGE_B',
        'COU_AGE_C',
    ];

    protected $casts = [
        'COU_NUM' => 'integer',
        'INS_ID' => 'integer',
        'TYP_NUM' => 'integer',
        'RAID_NUM' => 'integer',
        'COU_DUREE' => 'integer',
        'COU_NB_PART_MIN' => 'integer',
        'COU_NB_PART_MAX' => 'integer',
        'COU_NB_EQU_MIN' => 'integer',
        'COU_NB_EQU_MAX' => 'integer',
        'COU_PART_PAR_EQU_MAX' => 'integer',
        'COU_PRIX_REPAS' => 'decimal:2',
        'COU_REDUC_LICENCIE' => 'decimal:2',
        'COU_DATE_DEPART' => 'datetime',
        'COU_DATE_FIN' => 'datetime',
        'COU_AGE_A' => 'integer',
        'COU_AGE_B' => 'integer',
        'COU_AGE_C' => 'integer',
    ];

    protected $appends = [];

    public function dossards()
    {
        return $this->hasMany(\App\Models\VikDossard::class, 'COU_NUM', 'COU_NUM');
    }

    public function raid()
    {
        return $this->hasOne(\App\Models\VikRaid::class, 'RAID_NUM', 'RAID_NUM');
    }

    public function acceptances()
    {
        return $this->hasMany(\App\Models\VikAccepter::class, 'COU_NUM', 'COU_NUM');
    }

    public function agePrices()
    {
        return $this->acceptances()->with('tranche')->get();
    }

    public function equipes()
    {
        return $this->hasMany(VikEquipe::class, 'COU_NUM', 'COU_NUM');
    }
}
