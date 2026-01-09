<div style="font-family:Arial,Helvetica,sans-serif;color:#111;">
    <h2>Confirmation d'inscription de l'équipe</h2>

    <p>Bonjour {{ $chef->INS_PRENOM ?? '' }} {{ $chef->INS_NOM ?? '' }},</p>

    <p>Votre équipe <strong>{{ $teamName }}</strong> a bien été inscrite pour la course <strong>{{ $course->COU_NOM }}</strong>.</p>

    <p>Détails de la course :</p>
    @php
        $courseName = $course->COU_NOM ?? 'N/A';
        $courseStart = 'N/A';
        $courseEnd = 'N/A';
        try {
            if (!empty($course->COU_DATE_DEPART)) {
                $courseStart = (new \DateTime($course->COU_DATE_DEPART))->format('d/m/Y H:i');
            }
        } catch (\Exception $__e) {
            $courseStart = 'N/A';
        }
        try {
            if (!empty($course->COU_DATE_FIN)) {
                $courseEnd = (new \DateTime($course->COU_DATE_FIN))->format('d/m/Y H:i');
            }
        } catch (\Exception $__e) {
            $courseEnd = 'N/A';
        }
    @endphp
    <ul>
        <li><strong>Nom :</strong> {{ $courseName }}</li>
        <li><strong>Départ :</strong> {{ $courseStart }}</li>
        <li><strong>Fin :</strong> {{ $courseEnd }}</li>
    </ul>

    <p>Membres inscrits :</p>
    <ul>
        @foreach($members as $m)
            <li>{{ $m->INS_PRENOM ?? '' }} {{ $m->INS_NOM ?? '' }}@if(!empty($m->INS_MAIL)) — {{ $m->INS_MAIL }}@endif</li>
        @endforeach
    </ul>

    <p>Vous êtes enregistré(e) en tant que responsable/chef d'équipe.</p>

    <p>Si vous devez modifier l'équipe, rendez-vous sur la page de la course.</p>

    <p>Cordialement,<br/>L'équipe d'organisation</p>
</div>