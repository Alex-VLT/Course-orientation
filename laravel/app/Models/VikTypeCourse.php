<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VikTypeCourse extends Model
{
    protected $table = 'vik_type_course';

    protected $primaryKey = 'TYP_NUM';

    public $timestamps = false;

    protected $fillable = ['TYP_NUM', 'TYP_LABEL'];
}
