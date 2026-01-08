<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VerifInscription extends Model
{
    public $timestamps = false;

    /**
     * Récupère la course par son numéro.
     *
     * @param int $numeroCourse
     * @return object|null
     */
    public static function fetchCourse(int $numeroCourse)
    {
        return DB::table('vik_course')->where('COU_NUM', $numeroCourse)->first();
    }

    /**
     * Vérifie si un inscrit (INS_ID) est déjà participant d'une course donnée.
     * Retourne true si l'inscrit participe déjà à la course (quelque soit l'équipe), false sinon.
     *
     * @param int $insId
     * @param int $numeroCourse
     * @return bool
     */
    public static function isInscritInCourse(int $insId, int $numeroCourse): bool
    {
        return DB::table('vik_participer')
            ->where('INS_ID', $insId)
            ->where('COU_NUM', $numeroCourse)
            ->exists();
    }

    /**
     * Récupère toutes les participations pour une course donnée.
     *
     * @param int $numeroCourse
     * @return \Illuminate\Support\Collection
     */
    public static function fetchParticipationsForCourse(int $numeroCourse)
    {
        return DB::table('vik_participer')->where('cou_num', $numeroCourse)->get();
    }

    /**
     * Récupère les membres d'une équipe pour une course donnée.
     *
     * @param int $numeroCourse
     * @param int $numeroEquipe
     * @return \Illuminate\Support\Collection
     */
    public static function fetchTeamMembers(int $numeroCourse, int $numeroEquipe)
    {
        return DB::table('vik_participer')
            ->where('cou_num', $numeroCourse)
            ->where('equ_num', $numeroEquipe)
            ->get();
    }

    /**
     * Récupère un inscrit par son INS_ID.
     *
     * @param int $insId
     * @return object|null
     */
    public static function fetchInscritById(int $insId)
    {
        return DB::table('vik_inscrit')->where('INS_ID', $insId)->first();
    }

    /**
     * Calcule l'âge d'après une date de naissance à une date de référence.
     * Retourne null si la date est invalide.
     *
     * @param string $birthDate 'YYYY-MM-DD'
     * @param string $atDate 'YYYY-MM-DD'
     * @return int|null
     */
    public static function getAgeAtDate(string $birthDate, string $atDate): ?int
    {
        try {
            $dob = new \DateTime($birthDate);
            $ref = new \DateTime($atDate);
            return $dob->diff($ref)->y;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Récupère les limites d'âge (A, B, C) pour une course.
     *
     * @param int $numeroCourse
     * @return array ['A' => int|null, 'B' => int|null, 'C' => int|null]
     */
    public static function fetchCourseAgeLimits(int $numeroCourse): array
    {
        $row = DB::table('vik_course')
            ->where('COU_NUM', $numeroCourse)
            ->select('COU_AGE_A', 'COU_AGE_B', 'COU_AGE_C')
            ->first();

        if (! $row) {
            return ['A' => null, 'B' => null, 'C' => null];
        }

        return ['A' => $row->COU_AGE_A, 'B' => $row->COU_AGE_B, 'C' => $row->COU_AGE_C];
    }

    /**
     * Recherche une course (autre que celle fournie en exclusion) à laquelle l'inscrit
     * participe et dont la fenêtre temporelle chevauche la plage fournie.
     *
     * Logique :
     * - Si la course cible ($startDate/$endDate) n'a pas de dates, on considère qu'on ne
     *   peut pas vérifier et on retourne null (aucun blocage ici).
     * - Pour chaque participation existante de l'inscrit, si la course existante n'a pas
     *   de dates on la considère comme potentiellement conflictuelle (conservatif) et la
     *   retourne. Sinon on vérifie le chevauchement des intervalles.
     *
     * Retourne l'objet course conflictuel (avec COU_NUM, COU_NOM, COU_DATE_DEPART, COU_DATE_FIN)
     * ou null si aucun conflit détecté.
     *
     * @param int $insId
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int|null $excludeCourseNum
     * @return object|null
     */
    public static function findOverlappingCourseForInscrit(int $insId, $startDate, $endDate, ?int $excludeCourseNum = null)
    {
        // if target course has no start or end, we can't reason about overlap reliably
        if (empty($startDate) || empty($endDate)) {
            return null;
        }

        $rows = DB::table('vik_participer as p')
            ->join('vik_course as c', 'p.COU_NUM', '=', 'c.COU_NUM')
            ->where('p.INS_ID', $insId)
            ->when($excludeCourseNum, function($q) use ($excludeCourseNum) {
                return $q->where('c.COU_NUM', '<>', $excludeCourseNum);
            })
            ->select('c.COU_NUM', 'c.COU_NOM', 'c.COU_DATE_DEPART', 'c.COU_DATE_FIN')
            ->get();

        try {
            $targetStart = new \DateTime($startDate);
            $targetEnd = new \DateTime($endDate);
        } catch (\Exception $e) {
            // if we can't parse the target dates, don't block here
            return null;
        }

        foreach ($rows as $r) {
            // if existing course has no start or end, be conservative and treat as overlapping
            if (empty($r->COU_DATE_DEPART) || empty($r->COU_DATE_FIN)) {
                return $r;
            }
            try {
                $rStart = new \DateTime($r->COU_DATE_DEPART);
                $rEnd = new \DateTime($r->COU_DATE_FIN);
            } catch (\Exception $e) {
                // parse error: treat as overlapping
                return $r;
            }

            // intervals overlap if start1 <= end2 && end1 >= start2
            if ($rStart <= $targetEnd && $rEnd >= $targetStart) {
                return $r;
            }
        }

        return null;
    }
}
