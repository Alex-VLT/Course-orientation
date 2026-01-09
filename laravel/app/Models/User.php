<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Notifications\ResetPasswordNotification;

/**
 * User Model (VIK_INSCRIT table)
 *
 * Represents a registered user/participant in the system. Extended from Laravel's Authenticatable
 * to support password-based authentication.
 *
 * @property int $INS_ID Primary key - Auto-incremented user ID
 * @property string $INS_NOM Family name
 * @property string $INS_PRENOM First name
 * @property \DateTime $INS_NAISSANCE Birth date
 * @property string $INS_CODE_PO Postal code
 * @property string $INS_MAIL Email address
 * @property string $INS_VILLE City
 * @property string $INS_ADRESSE Street address
 * @property string $INS_TEL Phone number
 * @property string $INS_MDP Password (hashed)
 * @property string $INS_NUM_LICENCE License number (if member of a club)
 * @property string $INS_NUM_PPS Social security number (if required for non-members)
 */
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
     * Get the password attribute for authentication
     *
     * @return string The user's hashed password
     */
    public function getAuthPassword()
    {
        return $this->INS_MDP;
    }

    /**
     * Get the email address for password reset notifications
     *
     * @return string The user's email address
     */
    public function getEmailForPasswordReset()
    {
        return $this->INS_MAIL;
    }

    /**
     * Get the mail notification route for this user
     *
     * @return string The user's email address for notifications
     */
    public function routeNotificationForMail()
    {
        return $this->INS_MAIL;
    }

    /**
     * Send a custom password reset notification to the user
     *
     * @param string $token The password reset token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }

    /**
     * Check if the user is a club member (has a license number)
     *
     * @return bool True if the user has a valid license number, false otherwise
     */
    public function isMember()
    {
        return $this->INS_NUM_LICENCE !== null;
    }

    /**
     * Check if the user is an adherent (member with license or social security number)
     *
     * @return bool True if the user has either a license number or social security number, false otherwise
     */
    public function isAdherent(): bool
    {
        if (!empty($this->INS_NUM_LICENCE)) {
            return true;
        }

        if (property_exists($this, 'INS_NUM_PPS') && !empty($this->INS_NUM_PPS)) {
            return true;
        }

        return false;
    }

    /**
     * Check if the user manages a club
     *
     * Verifies if this user is listed as the organizer (INS_ID) in the VIK_CLUB table
     *
     * @return bool True if the user manages at least one club, false otherwise
     */
    public function managesClub(): bool
    {
        return \App\Models\VikClub::where('INS_ID', $this->INS_ID)->exists();
    }

    /**
     * Check if the user manages at least one raid
     *
     * Verifies if this user is listed as the organizer (INS_ID) in the VIK_RAID table
     *
     * @return bool True if the user manages at least one raid, false otherwise
     */
    public function managesRaid(): bool
    {
        return \App\Models\VikRaid::where('INS_ID', $this->INS_ID)->exists();
    }

    /**
     * Check if the user manages at least one course (race)
     *
     * Verifies if this user is listed as the organizer (INS_ID) in the VIK_RACE table
     *
     * @return bool True if the user manages at least one course, false otherwise
     */
    public function managesCourse(): bool
    {
        return \App\Models\VikRace::where('INS_ID', $this->INS_ID)->exists();
    }

    /**
     * Check if the user is an administrator
     *
     * Checks if the INS_IS_ADMIN attribute is set to 1 (true)
     *
     * @return bool True if the user has admin privileges, false otherwise
     */
    public function isAdmin(): bool
    {
        // Eloquent attributes are accessed via magic properties; property_exists() returns false.
        return intval($this->INS_IS_ADMIN ?? 0) === 1;
    }
}

