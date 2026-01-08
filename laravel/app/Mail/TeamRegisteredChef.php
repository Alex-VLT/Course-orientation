<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TeamRegisteredChef extends Mailable
{
    use Queueable, SerializesModels;

    public $chef;
    public $course;
    public $teamName;
    public $members;

    /**
     * Create a new message instance.
     */
    public function __construct($chef, $course, $teamName, $members)
    {
        $this->chef = $chef;
        $this->course = $course;
        $this->teamName = $teamName;
        $this->members = $members;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = "Confirmation d'inscription - {$this->course->COU_NOM}";
        return $this->subject($subject)
                    ->view('emails.team_registered_chef')
                    ->with([
                        'chef' => $this->chef,
                        'course' => $this->course,
                        'teamName' => $this->teamName,
                        'members' => $this->members,
                    ]);
    }
}
