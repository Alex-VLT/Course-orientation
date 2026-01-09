<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * VikDossard Model (VIK_DOSSARD table)
 *
 * Represents a race bib/dossard (numbered tag worn by participants during a course).
 * Contains information about bib assignments to teams and distribution status.
 *
 * @property int $DOSS_ID Primary key - Bib ID
 * @property int $COU_NUM Course number (references VIK_COURSE)
 * @property int $DOSS_NUM Bib number (displayed on the physical bib)
 * @property int $EQUIPE_NUM Team number (references VIK_EQUIPE)
 * @property bool $DOSS_DISTRIBUE Whether the bib has been distributed
 * @property \DateTime $created_at Record creation timestamp
 * @property \DateTime $updated_at Last update timestamp
 */
class VikDossard extends Model
{
    use HasFactory;

    protected $table = 'VIK_DOSSARD';
    protected $primaryKey = 'DOSS_ID';
    public $timestamps = true;

    protected $fillable = ['COU_NUM', 'DOSS_NUM', 'EQUIPE_NUM', 'DOSS_DISTRIBUE'];
}
