<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VerifInscription extends Model
{
    public $timestamps = false;

    /**
     * Fetch a course by its number.
     *
     * @param int $numeroCourse
     * @return object|null
     */
    public static function fetchCourse(int $numeroCourse)
    {
        return DB::table('vik_course')->where('COU_NUM', $numeroCourse)->first();
    }

    /**
     * Check whether an inscrit (INS_ID) is already a participant of a given course.
     * Returns true if the inscrit participates in the course (regardless of team), false otherwise.
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
     * Retrieve all participations for a given course.
     *
     * @param int $numeroCourse
     * @return \Illuminate\Support\Collection
     */
    public static function fetchParticipationsForCourse(int $numeroCourse)
    {
        return DB::table('vik_participer')->where('cou_num', $numeroCourse)->get();
    }

    /**
     * Retrieve team members for a given course and team number.
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
     * Fetch an inscrit by its INS_ID.
     *
     * @param int $insId
     * @return object|null
     */
    public static function fetchInscritById(int $insId)
    {
        return DB::table('vik_inscrit')->where('INS_ID', $insId)->first();
    }

    /**
     * Compute age from a birth date at a reference date.
     * Returns null if dates are invalid.
     *
     * @param string $birthDate 'YYYY-MM-DD'
     * @param string $atDate 'YYYY-MM-DD'
     * @return int|null
     */
    public static function getAgeAtDate(string $birthDate, string $atDate): ?int
    {
        if (empty($birthDate) || empty($atDate)) {
            return null;
        }

        try {
            // Normalize both datetimes to date-only (midnight) so time-of-day does not
            // affect age calculation. This follows the common "age in full years" rule.
            $dob = new \DateTime($birthDate);
            $ref = new \DateTime($atDate);
            $dob->setTime(0,0,0);
            $ref->setTime(0,0,0);

            // If the date of birth is in the future relative to the reference date,
            // treat it as invalid for age calculation and return null.
            if ($dob > $ref) {
                return null;
            }

            return $dob->diff($ref)->y;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Retrieve age limits (A, B, C) for a course.
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
     * Find a course (other than the excluded one) the inscrit participates in that overlaps the provided time window.
     *
     * Logic:
     * - If the target course has no start or end, we cannot reason about overlap and return null.
     * - For each existing participation of the inscrit, if the existing course has no dates we conservatively
     *   treat it as conflicting and return it. Otherwise we check interval overlap.
     *
     * Returns the conflicting course object (with COU_NUM, COU_NOM, COU_DATE_DEPART, COU_DATE_FIN) or null if none.
     *
     * @param int $insId
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int|null $excludeCourseNum
     * @return object|null
     */
    public static function findOverlappingCourseForInscrit(int $insId, $startDate, $endDate, ?int $excludeCourseNum = null)
    {
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
            return null;
        }

        foreach ($rows as $r) {
            if (empty($r->COU_DATE_DEPART) || empty($r->COU_DATE_FIN)) {
                return $r;
            }
            try {
                $rStart = new \DateTime($r->COU_DATE_DEPART);
                $rEnd = new \DateTime($r->COU_DATE_FIN);
            } catch (\Exception $e) {
                return $r;
            }

            if ($rStart <= $targetEnd && $rEnd >= $targetStart) {
                return $r;
            }
        }

        return null;
    }
}
