<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Canonical model for the `vik_equipe` table.
 * We keep Team as a thin subclass for backwards compatibility.
 */
class VikEquipe extends Model
{
    protected $table = 'vik_equipe';
    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        'COU_NUM', 'EQU_NUM', 'INS_ID', 'EQU_NOM',
        'EQU_ORDRE_ARRIVEE', 'EQU_TEMPS', 'EQU_POINTS',
        'EQU_PAIEMENT_VALIDE'
    ];

    protected $casts = [
        'EQU_ORDRE_ARRIVEE' => 'integer',
        'EQU_TEMPS' => 'integer',
        'EQU_POINTS' => 'integer',
    ];

    /**
     * Relation: retrieve participations associated with this team.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function participations()
    {
        return $this->hasMany(Participate::class, 'EQU_NUM', 'EQU_NUM');
    }

    /**
     * Relation: creator / team leader (INS_ID -> User).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createur()
    {
        return $this->belongsTo(User::class, 'INS_ID', 'INS_ID');
    }
}