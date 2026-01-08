<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TeamRegisteredRunner extends Mailable
{
    use Queueable, SerializesModels;

    public $runner;
    public $course;
    public $teamName;
    public $chef;

    public function __construct($runner, $course, $teamName, $chef)
    {
        $this->runner = $runner;
        $this->course = $course;
        $this->teamName = $teamName;
        $this->chef = $chef;
    }

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
