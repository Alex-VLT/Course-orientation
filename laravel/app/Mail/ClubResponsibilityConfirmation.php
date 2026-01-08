<?php

namespace App\Mail;

use App\Models\VikClubPending;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClubResponsibilityConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public VikClubPending $pending, public User $responsible)
    {
    }

    public function build(): self
    {
        $confirmUrl = route('clubs.confirm', ['token' => $this->pending->token]);

        return $this
            ->subject('Confirmation de responsabilité de club')
            ->markdown('emails.clubs.confirmation', [
                'pending' => $this->pending,
                'responsible' => $this->responsible,
                'confirmUrl' => $confirmUrl,
            ]);
    }
}
