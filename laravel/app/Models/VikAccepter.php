<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * VikAccepter Model (VIK_ACCEPTER table)
 *
 * Represents the pricing relationship between a course and age brackets.
 * Defines the cost of registration for each age group participating in a course.
 *
 * This is a junction table that links courses to age tranches with specific pricing.
 *
 * @property int $COU_NUM Course number (references VIK_COURSE)
 * @property int $TRA_ID Age bracket ID (references VIK_TRANCHE_AGE)
 * @property float $ACC_PRIX Registration price for this age bracket in this course
 */
class VikAccepter extends Model
{
    protected $table = 'vik_accepter';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'COU_NUM',
        'TRA_ID',
        'ACC_PRIX',
    ];

    protected $casts = [
        'COU_NUM' => 'integer',
        'TRA_ID' => 'integer',
        'ACC_PRIX' => 'decimal:2',
    ];

    /**
     * Get the age bracket for this acceptance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tranche()
    {
        return $this->belongsTo(VikTrancheAge::class, 'TRA_ID', 'TRA_ID');
    }

    /**
     * Get the course this acceptance belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function race()
    {
        return $this->belongsTo(VikRace::class, 'COU_NUM', 'COU_NUM');
    }
}
