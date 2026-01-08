<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Mail sent to the team leader (chef) after a team registration.
 *
 * Public properties are exposed to the view for rendering.
 */
class TeamRegisteredChef extends Mailable
{
    use Queueable, SerializesModels;

    public $chef;
    public $course;
    public $teamName;
    public $members;

    /**
     * Create a new message instance.
     *
     * @param object $chef  Inscrit (DB row) du responsable
     * @param object $course Course (DB row) pour laquelle l'équipe est inscrite
     * @param string $teamName Nom de l'équipe
     * @param array $members Liste d'objets inscrits pour l'équipe
     * @return void
     */
    public function __construct($chef, $course, $teamName, $members)
    {
        $this->chef = $chef;
        $this->course = $course;
        $this->teamName = $teamName;
        $this->members = $members;
    }

    /**
     * Build the mailable.
     *
     * @return $this
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
