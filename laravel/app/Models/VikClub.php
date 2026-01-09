<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VikClub extends Model
{
    use HasFactory;

    protected $table = 'vik_club';

    protected $primaryKey = 'CLU_NUM';

    public $timestamps = false;

    protected $fillable = ['INS_ID', 'CLU_NOM', 'CLU_ADRESSE', 'CLU_CODE_POSTAL', 'CLU_VILLE'];

    protected static function booted(): void
    {
        static::creating(function (VikClub $club): void {
            if (empty($club->CLU_ADRESSE)) {
                $club->CLU_ADRESSE = 'Adresse inconnue';
            }
            if (empty($club->CLU_CODE_POSTAL)) {
                $club->CLU_CODE_POSTAL = 99999;
            }
            if (empty($club->CLU_VILLE)) {
                $club->CLU_VILLE = 'Inconnue';
            }
        });
    }
}
