<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * VikTypeCourse Model (VIK_TYPE_COURSE table)
 *
 * Represents a course type/category. Courses can be classified by type
 * (e.g., Trail Running, Orienteering, etc.)
 *
 * @property int $TYP_NUM Primary key - Type number/ID
 * @property string $TYP_LABEL Type label/name (e.g., "Trail Running")
 */
class VikTypeCourse extends Model
{
    protected $table = 'VIK_TYPE_COURSE';
    protected $primaryKey = 'TYP_NUM';
    public $timestamps = false;

    protected $fillable = ['TYP_NUM', 'TYP_LABEL'];
}
