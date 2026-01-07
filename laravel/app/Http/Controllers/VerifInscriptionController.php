<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\VerifInscription;
use Illuminate\Support\Facades\DB;

class VerifInscriptionController extends Controller
{
    
    /**
     * Vérifie les contraintes liées au nombre de participants par équipes.
     *
     * @param object $course
     * @param \Illuminate\Support\Collection $participations
     * @param \Illuminate\Support\Collection $teamMembers
     * @return array
     */
    public function validateNbParticipants($course, $participations, $teamMembers): array
    {
        $membersCount = $teamMembers->count();
        // Normaliser les clés de participation (equ_num peut être stocké EQU_NUM selon la casse remontée par PDO)
        $distinctTeams = $participations->map(function($p){ return $p->equ_num ?? $p->EQU_NUM ?? null; })->filter()->unique()->count();
        $totalParticipants = $participations->count();

        $messages = [];

        if (! is_null($course->COU_PART_PAR_EQU_MAX) && ($membersCount) > $course->COU_PART_PAR_EQU_MAX) {
            $messages[] = "Trop de membres pour l'équipe (max {$course->COU_PART_PAR_EQU_MAX}).";
        }

        if (! is_null($course->COU_NB_EQU_MAX) && $distinctTeams > $course->COU_NB_EQU_MAX) {
            $messages[] = "Nombre d'équipes dépassé (max {$course->COU_NB_EQU_MAX}).";
        }

        if (! is_null($course->COU_NB_PART_MAX) && $totalParticipants > $course->COU_NB_PART_MAX) {
            $messages[] = "Nombre total de participants dépassé (max {$course->COU_NB_PART_MAX}).";
        }

        return [
            'ok' => empty($messages),
            'messages' => $messages,
            'details' => [
                'membersCount' => $membersCount,
                'distinctTeams' => $distinctTeams,
                'distinctTeamIds' => $participations->map(function($p){ return $p->equ_num ?? $p->EQU_NUM ?? null; })->filter()->unique()->values()->all(),
                'totalParticipants' => $totalParticipants,
                'participationsSample' => $participations->map(function($p){ return ['INS_ID' => ($p->ins_id ?? $p->INS_ID ?? null), 'equ_num' => ($p->equ_num ?? $p->EQU_NUM ?? null)]; })->take(20)->values()->all(),
            ],
        ];
    }

    /**
     * Vérifie l'age de chaque membre de l'équipe et si sa tranche est acceptée pour la course.
     *
     * @param object $course
     * @param \Illuminate\Support\Collection $teamMembers
     * @return array
     */
    public function validateAge($course, $teamMembers): array
    {
        $messages = [];
        $courseNum = $course->COU_NUM;
        $startDate = $course->COU_DATE_DEPART;

        // Récupérer A, B, C depuis la course
        $A = $course->COU_AGE_A ?? null;
        $B = $course->COU_AGE_B ?? null;
        $C = $course->COU_AGE_C ?? null;

        // Vérifier cohérence des limites
        if (! is_numeric($A) || ! is_numeric($B) || ! is_numeric($C)) {
            $messages[] = 'Configuration d\'âge de la course invalide (A/B/C manquant).';
            return ['ok' => false, 'messages' => $messages];
        }
        if ($A > $B || $B > $C) {
            $messages[] = 'Configuration d\'âge incohérente (il faut A <= B <= C).';
            return ['ok' => false, 'messages' => $messages];
        }

        $countAtLeastC = 0;
        $countAtLeastB = 0;
        $countBelowB = 0;

        foreach ($teamMembers as $member) {
            $insId = $member->INS_ID;
            $ins = VerifInscription::fetchInscritById($insId);
            if (! $ins) {
                $messages[] = "INS_ID {$insId} introuvable.";
                continue;
            }

            if (empty($ins->INS_NAISSANCE)) {
                $messages[] = "INS_ID {$insId} : date de naissance manquante.";
                continue;
            }

            $age = VerifInscription::getAgeAtDate($ins->INS_NAISSANCE, $startDate);
            if (is_null($age)) {
                $messages[] = "INS_ID {$insId} : date de naissance invalide ({$ins->INS_NAISSANCE}).";
                continue;
            }

            // Règle : tous ont au moins A
            if ($age < $A) {
                $messages[] = "INS_ID {$insId} ({$ins->INS_NOM} {$ins->INS_PRENOM}) : {$age} ans < A ({$A}).";
            }

            if ($age >= $C) {
                $countAtLeastC++;
            }
            if ($age >= $B) {
                $countAtLeastB++;
            }
            if ($age < $B) {
                $countBelowB++;
            }
        }

        // Règle d'équipe : soit existant >= C, soit tous >= B
        if ($countAtLeastC < 1 && $countBelowB > 0) {
            $messages[] = "Règle d\'âge non respectée : il faut au moins un membre >= C ({$C}) ou que tous aient >= B ({$B}).";
        }

        return [
            'ok' => empty($messages),
            'messages' => $messages,
        ];
    }

    /**
     * Valide une équipe pour une course.
     *
     * Cette méthode récupère les données via le modèle VerifInscription et vérifie :
     * - que l'équipe n'a pas plus de membres que COU_PART_PAR_EQU_MAX
     * - que le nombre d'équipes n'excède pas COU_NB_EQU_MAX
     * - que le nombre total de participants n'excède pas COU_NB_PART_MAX
     *
     * @param int $numeroEquipe
     * @param int $numeroCourse
     * @param bool $deleteIfInvalid (optionnel) si true, supprime l'équipe en DB si non valide
     * @return array ['ok' => bool, 'messages' => array, 'details' => array]
     */
    public function validateEquipe(int $numeroEquipe, int $numeroCourse, bool $deleteIfInvalid = false): array
    {
        $course = VerifInscription::fetchCourse($numeroCourse);
        if (! $course) {
            return [
                'ok' => false,
                'messages' => ['Course introuvable.'],
                'details' => [],
            ];
        }

        $participations = VerifInscription::fetchParticipationsForCourse($numeroCourse);
        $teamMembers = VerifInscription::fetchTeamMembers($numeroCourse, $numeroEquipe);

        $resultNb = $this->validateNbParticipants($course, $participations, $teamMembers);
        $resultAge = $this->validateAge($course, $teamMembers);

        $messages = array_merge($resultNb['messages'], $resultAge['messages']);

        $ok = empty($messages);

        // Si on veut supprimer l'équipe en cas d'échec
        if (! $ok && $deleteIfInvalid) {
            DB::table('vik_participer')
                ->where('cou_num', $numeroCourse)
                ->where('equ_num', $numeroEquipe)
                ->delete();

            $messages[] = 'Équipe supprimée en raison d\'une validation non satisfaite.';

            // Recalculer les comptes après suppression
            $participations = VerifInscription::fetchParticipationsForCourse($numeroCourse);
            $membersCount = 0;
            $distinctTeams = $participations->pluck('equ_num')->unique()->count();
            $totalParticipants = $participations->count();
        } else {
            $membersCount = $teamMembers->count();
            $distinctTeams = $participations->pluck('equ_num')->unique()->count();
            $totalParticipants = $participations->count();
        }

        return [
            'ok' => $ok,
            'messages' => $messages,
            'details' => [
                'membersCount' => $membersCount,
                'distinctTeams' => $distinctTeams,
                'totalParticipants' => $totalParticipants,
            ],
        ];
    }
}
