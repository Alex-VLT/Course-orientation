<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VikClub extends Model
{
    use HasFactory;

    protected $table = 'VIK_CLUB';
    protected $primaryKey = 'CLU_NUM';
    public $timestamps = false;

    protected $fillable = ['INS_ID', 'CLU_NOM', 'CLU_ADRESSE', 'CLU_CODE_POSTAL', 'CLU_VILLE'];
}