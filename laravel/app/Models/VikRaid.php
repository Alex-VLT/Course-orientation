<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * VikRaid Model (VIK_RAID table)
 *
 * Represents a raid event which can contain multiple courses. A raid is a multi-day or multi-course
 * event organized by a club and managed by a user.
 *
 * @property int $RAID_NUM Primary key - Raid number/ID
 * @property int $CLU_NUM Club ID (references VIK_CLUB)
 * @property int $INS_ID Organizer user ID (references VIK_INSCRIT)
 * @property string $RAID_NOM Raid name
 * @property \DateTime $RAID_DATE_DEBUT_INSCRI Registration start date
 * @property \DateTime $RAID_DATE_FIN_INSCRI Registration end date
 * @property \DateTime $RAID_DATE_DEBUT Raid start date
 * @property \DateTime $RAID_DATE_FIN Raid end date
 * @property string $RAID_CONTACT Contact information
 * @property string $RAID_LIEN_SITE_WEB Website URL
 * @property float $RAID_LATITUDE Latitude coordinate
 * @property float $RAID_LONGITUDE Longitude coordinate
 * @property string $RAID_ILLUSTRATION Illustration/image filename
 */
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

    /**
     * Get all courses in this raid
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function courses()
    {
        return $this->hasMany(\App\Models\VikRace::class, 'RAID_NUM', 'RAID_NUM');
    }

    /**
     * Get the club organizing this raid
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function club()
    {
        return $this->belongsTo(\App\Models\VikClub::class, 'CLU_NUM', 'CLU_NUM');
    }

    /**
     * Get the user responsible for this raid
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function responsable()
    {
        // CORRECTION : C'est belongsTo car la clé étrangère INS_ID est dans VIK_RAID
        return $this->belongsTo(\App\Models\User::class, 'INS_ID', 'INS_ID');
    }
}