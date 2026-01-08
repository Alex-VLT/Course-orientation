<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'VIK_INSCRIT';
    protected $primaryKey = 'INS_ID';
    public $timestamps = false;
    public $incrementing = true;

    protected $fillable = [
        'INS_NOM',
        'INS_PRENOM',
        'INS_NAISSANCE',
        'INS_CODE_PO',
        'INS_MAIL',
        'INS_VILLE',
        'INS_ADRESSE',
        'INS_TEL',
        'INS_MDP',
        'INS_NUM_LICENCE',
        'INS_NUM_PPS'
    ];

    protected $hidden = ['INS_MDP'];

    /**
     * Auth password
     */
    public function getAuthPassword()
    {
        return $this->INS_MDP;
    }

    /**
     * Email for password reset
     */
    public function getEmailForPasswordReset()
    {
        return $this->INS_MAIL;
    }

    /**
     * route for mail
     */
    public function routeNotificationForMail()
    {
        return $this->INS_MAIL;
    }

    /**
     * Notification reset custom
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }

     public function isMember()
    {
        return $this->INS_NUM_LICENCE !== null;
    }

    /**
     * Whether the user is an adherent. Some installations may have an additional
     * identifier `INS_NUM_PPS` — check that too if present.
     */
    public function isAdherent(): bool
    {
        if (!empty($this->INS_NUM_LICENCE)) {
            return true;
        }

        // Some DBs may include an alternate identifier INS_NUM_PPS
        if (property_exists($this, 'INS_NUM_PPS') && !empty($this->INS_NUM_PPS)) {
            return true;
        }

        return false;
    }

    /**
     * Whether the user manages a club (exists in VIK_CLUB with INS_ID)
     */
    public function managesClub(): bool
    {
        return \App\Models\VikClub::where('INS_ID', $this->INS_ID)->exists();
    }

    /**
     * Whether the user is responsible for at least one raid
     */
    public function managesRaid(): bool
    {
        return \App\Models\VikRaid::where('INS_ID', $this->INS_ID)->exists();
    }

    /**
     * Whether the user is responsible for at least one course
     */
    public function managesCourse(): bool
    {
        return \App\Models\VikRace::where('INS_ID', $this->INS_ID)->exists();
    }
}

