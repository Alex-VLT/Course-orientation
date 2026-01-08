<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\VerifInscription;
use Illuminate\Support\Facades\DB;

class VerifInscriptionController extends Controller
{
    
    /**
     * Validate constraints related to number of participants per team.
     *
     * @param object $course
     * @param \Illuminate\Support\Collection $participations
     * @param \Illuminate\Support\Collection $teamMembers
     * @return array
     */
    public function validateNbParticipants($course, $participations, $teamMembers): array
    {
    $membersCount = $teamMembers->count();
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
     * Validate the age of each team member and whether their age bracket is allowed for the course.
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

    // Accumulation des messages lisibles pour l'utilisateur
        foreach ($teamMembers as $member) {
            $insId = $member->INS_ID;
            $ins = VerifInscription::fetchInscritById($insId);
            $displayName = null;
            if ($ins) {
                $displayName = trim(($ins->INS_PRENOM ?? '') . ' ' . ($ins->INS_NOM ?? '')) ?: null;
            }

            if (! $ins) {
                logger()->debug('Inscrit introuvable during validateAge', ['ins_id' => $insId]);
                $messages[] = 'Inscrit introuvable.';
                continue;
            }

            if (empty($ins->INS_NAISSANCE)) {
                $who = $displayName ?? "INS_ID {$insId}";
                $messages[] = "{$who} : date de naissance manquante dans le profil.";
                continue;
            }

            $age = VerifInscription::getAgeAtDate($ins->INS_NAISSANCE, $startDate);
            if (is_null($age)) {
                $who = $displayName ?? "INS_ID {$insId}";
                $messages[] = "{$who} : date de naissance invalide ({$ins->INS_NAISSANCE}).";
                continue;
            }

            $who = $displayName ?? 'Utilisateur inconnu';

            // Règle : tous ont au moins A
            if ($age < $A) {
                $messages[] = "{$who} a {$age} ans — il doit avoir au moins {$A} ans pour participer.";
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

        // Règle d'équipe : soit il y a au moins un membre >= C, soit tous ont au moins B
        if ($countAtLeastC < 1 && $countBelowB > 0) {
            // Construire des messages plus précis : qui est < B et leur âge
            $messages[] = "Règle d'âge non respectée : il faut au moins un membre ayant >= {$C} ans, ou que tous les membres aient au moins {$B} ans.";
            // Ajout d'une indication : lister les membres en dessous de B pour aider l'utilisateur
            $belowList = [];
            foreach ($teamMembers as $member) {
                $ins = VerifInscription::fetchInscritById($member->INS_ID);
                if (! $ins) continue;
                $age = VerifInscription::getAgeAtDate($ins->INS_NAISSANCE ?? '', $startDate ?? '');
                if (is_null($age)) continue;
                if ($age < $B) {
                    $name = trim(($ins->INS_PRENOM ?? '') . ' ' . ($ins->INS_NOM ?? '')) ?: ("INS_ID {$ins->INS_ID}");
                    $belowList[] = "{$name} ({$age} ans)";
                }
            }
            if (! empty($belowList)) {
                $messages[] = 'Participants en dessous de ' . $B . ' ans : ' . implode(', ', $belowList) . '.';
            }
        }

        return [
            'ok' => empty($messages),
            'messages' => $messages,
        ];
    }

    /**
     * Validate a team for a course.
     *
     * This method fetches data via the VerifInscription model and checks:
     * - the team does not exceed COU_PART_PAR_EQU_MAX members
     * - the number of teams does not exceed COU_NB_EQU_MAX
     * - the total number of participants does not exceed COU_NB_PART_MAX
     *
     * @param int $numeroEquipe
     * @param int $numeroCourse
     * @param bool $deleteIfInvalid (optional) if true, delete the DB team when invalid
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
