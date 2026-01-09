<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * VikRace Model (VIK_COURSE table)
 *
 * Represents a course (race) event in the system. Courses can be standalone events or part of a raid.
 * Contains information about course details, pricing, age brackets, and team capacity constraints.
 *
 * @property int $COU_NUM Primary key - Course number/ID
 * @property int $INS_ID Organizer user ID (references VIK_INSCRIT)
 * @property int $TYP_NUM Course type ID (references VIK_TYPE_COURSE)
 * @property int $RAID_NUM Raid ID (references VIK_RAID) - nullable for standalone courses
 * @property string $COU_NOM Course name
 * @property int $COU_DUREE Course duration in minutes
 * @property string $COU_DIFFICULTE Difficulty level
 * @property \DateTime $COU_DATE_DEPART Course start date/time
 * @property \DateTime $COU_DATE_FIN Course end date/time
 * @property int $COU_NB_PART_MIN Minimum number of participants
 * @property int $COU_NB_PART_MAX Maximum number of participants
 * @property int $COU_NB_EQU_MIN Minimum number of teams
 * @property int $COU_NB_EQU_MAX Maximum number of teams
 * @property int $COU_PART_PAR_EQU_MAX Maximum participants per team
 * @property float $COU_PRIX_REPAS Meal price
 * @property float $COU_REDUC_LICENCIE Discount for club members
 * @property int $COU_AGE_A Age bracket A (years)
 * @property int $COU_AGE_B Age bracket B (years)
 * @property int $COU_AGE_C Age bracket C (years)
 * @property bool $COU_UTILISE_PUCE Whether the course uses RFID chips
 */
class VikRace extends Model
{
    protected $table = 'VIK_COURSE';
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
        'COU_UTILISE_PUCE'
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
        'COU_DATE_FIN'    => 'datetime',
        'COU_AGE_A' => 'integer',
        'COU_AGE_B' => 'integer',
        'COU_AGE_C' => 'integer',
        'COU_UTILISE_PUCE' => 'boolean'
    ];

    protected $appends = [];

    /**
     * Get all race bibs/dossards for this course
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dossards()
    {
        return $this->hasMany(\App\Models\VikDossard::class, 'COU_NUM', 'COU_NUM');
    }

    /**
     * Get the raid this course belongs to (if any)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function raid()
    {
        return $this->hasOne(\App\Models\VikRaid::class, 'RAID_NUM', 'RAID_NUM');
    }

    /**
     * Get all acceptances/registrations for this course
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function acceptances()
    {
        return $this->hasMany(\App\Models\VikAccepter::class, 'COU_NUM', 'COU_NUM');
    }

    
    /**
     * Get age bracket pricing for this course with tranche details
     *
     * @return \Illuminate\Database\Eloquent\Collection Acceptances with loaded tranche relationships
     */
    public function agePrices()
    {
        return $this->acceptances()->with('tranche')->get();
    }

    /**
     * Get all teams registered for this course
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function equipes()
    {
        return $this->hasMany(VikEquipe::class, 'COU_NUM', 'COU_NUM');
    }
}
