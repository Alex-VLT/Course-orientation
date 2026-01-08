<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CourseInscriptionNotification extends Notification
{
    protected $course;
    protected $team;
    protected $chef;

    public function __construct($course, $team, $chef)
    {
        $this->course = $course;
        $this->team = $team;
        $this->chef = $chef;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $courseName = $this->course->COU_NOM ?? 'Nom inconnu';
        $courseDifficulty = $this->course->COU_DIFFICULTE ?? 'N/A';
        $courseDuration = $this->course->COU_DUREE ?? 'N/A';
        $courseDate = $this->course->COU_DATE_DEPART ? \Carbon\Carbon::parse($this->course->COU_DATE_DEPART)->format('d/m/Y à H:i') : 'Date non définie';
        $teamName = $this->team->EQU_NOM ?? 'Équipe sans nom';
        $chefName = trim(($this->chef->INS_PRENOM ?? '') . ' ' . ($this->chef->INS_NOM ?? '')) ?: 'Le responsable de l\'équipe';

        $participantName = trim(($notifiable->INS_PRENOM ?? '') . ' ' . ($notifiable->INS_NOM ?? '')) ?: 'Coureur';

        return (new MailMessage)
            ->subject("Inscription à la course : {$courseName}")
            ->greeting("Bonjour {$participantName} 👋")
            ->line("Vous avez été inscrit avec succès à la course **{$courseName}** !")
            ->line('')
            ->line('📋 **Détails de votre inscription :**')
            ->line("- **Équipe** : {$teamName}")
            ->line("- **Responsable** : {$chefName}")
            ->line("- **Difficulté** : {$courseDifficulty}/5")
            ->line("- **Durée estimée** : {$courseDuration} minutes")
            ->line("- **Date et heure de départ** : {$courseDate}")
            ->line('')
            ->line("Vous êtes maintenant membre de l'équipe **{$teamName}**. Préparez-vous bien et bon courage pour cette belle aventure ! 🏃‍♂️")
            ->line('')
            ->line("Si vous avez des questions ou besoin d'assistance, n'hésitez pas à contacter votre responsable d'équipe.")
            ->salutation('Bonne chance ! 🎉');
    }
}
