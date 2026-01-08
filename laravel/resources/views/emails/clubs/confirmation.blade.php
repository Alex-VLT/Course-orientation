@component('mail::message')
# Confirmation de responsabilité de club

Bonjour {{ $responsible->INS_PRENOM ?? $responsible->INS_NOM ?? 'licencié(e)' }},

Vous avez été désigné(e) comme responsable du club **{{ $pending->CLU_NOM }}**.
Merci de confirmer pour valider la création du club.

- Adresse : {{ $pending->CLU_ADRESSE ?? '—' }}
- Code postal / Ville : {{ $pending->CLU_CODE_POSTAL ?? '—' }} {{ $pending->CLU_VILLE ?? '' }}

@component('mail::button', ['url' => $confirmUrl])
Confirmer ma responsabilité
@endcomponent

Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer ce message.

Merci,
L'équipe L'Embuscade
@endcomponent
