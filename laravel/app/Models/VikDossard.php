<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VikDossard extends Model
{
    use HasFactory;

    protected $table = 'vik_dossard';

    protected $primaryKey = 'DOSS_ID';

    public $timestamps = true;

    protected $fillable = ['COU_NUM', 'DOSS_NUM', 'EQUIPE_NUM', 'DOSS_DISTRIBUE'];
}
