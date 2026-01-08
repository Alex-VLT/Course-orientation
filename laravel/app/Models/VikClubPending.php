<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VikClubPending extends Model
{
    use HasFactory;

    protected $table = 'VIK_CLUB_PENDING';

    protected $fillable = [
        'token',
        'INS_ID',
        'created_by',
        'CLU_NOM',
        'CLU_ADRESSE',
        'CLU_CODE_POSTAL',
        'CLU_VILLE',
    ];
}
