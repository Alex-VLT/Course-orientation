<div style="font-family:Arial,Helvetica,sans-serif;color:#111;">
    <h2>Vous avez été inscrit(e) à une course</h2>

    <p>Bonjour {{ $runner->INS_PRENOM ?? '' }} {{ $runner->INS_NOM ?? '' }},</p>

    <p>Le responsable <strong>{{ $chef->INS_PRENOM ?? '' }} {{ $chef->INS_NOM ?? '' }}</strong> vous a inscrit(e) pour la course <strong>{{ $course->COU_NOM }}</strong> au sein de l'équipe <strong>{{ $teamName }}</strong>.</p>

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

    <p>Si vous avez des questions, contactez l'organisateur.</p>

    <p>Cordialement,<br/>L'équipe d'organisation</p>
</div>