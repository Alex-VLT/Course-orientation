<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * VikClub Model (VIK_CLUB table)
 *
 * Represents a sports club in the system. Clubs organize raids and can have members
 * who participate in courses and raids.
 *
 * @property int $CLU_NUM Primary key - Club number/ID
 * @property int $INS_ID Manager user ID (references VIK_INSCRIT)
 * @property string $CLU_NOM Club name
 * @property string $CLU_ADRESSE Street address
 * @property string $CLU_CODE_POSTAL Postal code
 * @property string $CLU_VILLE City
 */
class VikClub extends Model
{
    use HasFactory;

    protected $table = 'VIK_CLUB';
    protected $primaryKey = 'CLU_NUM';
    public $timestamps = false;

    protected $fillable = ['INS_ID', 'CLU_NOM', 'CLU_ADRESSE', 'CLU_CODE_POSTAL', 'CLU_VILLE'];
}