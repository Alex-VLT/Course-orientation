<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Mail sent to each runner registered in a team.
 */
class TeamRegisteredRunner extends Mailable
{
    use Queueable, SerializesModels;

    public $runner;
    public $course;
    public $teamName;
    public $chef;

    /**
     * Constructor.
     *
     * @param object $runner Inscrit (DB row)
     * @param object $course Course (DB row)
     * @param string $teamName
     * @param object $chef Responsable (DB row)
     * @return void
     */
    public function __construct($runner, $course, $teamName, $chef)
    {
        $this->runner = $runner;
        $this->course = $course;
        $this->teamName = $teamName;
        $this->chef = $chef;
    }

    /**
     * Build the mailable.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "Vous avez été inscrit(e) - {$this->course->COU_NOM}";
        return $this->subject($subject)
                    ->view('emails.team_registered_runner')
                    ->with([
                        'runner' => $this->runner,
                        'course' => $this->course,
                        'teamName' => $this->teamName,
                        'chef' => $this->chef,
                    ]);
    }
}
