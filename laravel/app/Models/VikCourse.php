<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VikCourse extends Model
{
    protected $table = 'VIK_COURSE';
    protected $primaryKey = 'COU_NUM';
    public $incrementing = false;
    public $timestamps = false;

    protected $keyType = 'int';

    protected $casts = [
        'COU_DATE_DEPART' => 'datetime',
        'COU_DATE_FIN'    => 'datetime',
    ];
}
