<?php

namespace App\Http\Requests;

use App\Models\VikRace;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'COU_NOM' => ['required', 'string', 'max:64'],
            'COU_DATE_DEPART' => ['required', 'date'],
            'COU_DATE_FIN' => ['required', 'date', 'after_or_equal:COU_DATE_DEPART'],
            'COU_DUREE' => ['required', 'integer', 'min:1'],
            'COU_DIFFICULTE' => ['required', 'string', 'max:64'],
            'COU_PRIX_REPAS' => ['nullable', 'numeric', 'min:0', 'max_digits:10'],
            'COU_REDUC_LICENCIE' => ['nullable', 'numeric', 'min:0', 'max_digits:10'],
            'COU_NB_PART_MIN' => ['required', 'integer', 'min:1', 'max_digits:5'],
            'COU_NB_PART_MAX' => ['required', 'integer', 'gte:COU_NB_PART_MIN', 'max_digits:5'],
            'COU_NB_EQU_MIN' => ['required', 'integer', 'min:1', 'max_digits:5'],
            'COU_NB_EQU_MAX' => ['required', 'integer', 'gte:COU_NB_EQU_MIN', 'max_digits:5'],
            'COU_PART_PAR_EQU_MAX' => ['required', 'integer', 'min:1', 'max_digits:2'],
            'COU_AGE_A' => ['required', 'integer', 'min:0', 'max:100'],
            'COU_AGE_B' => ['required', 'integer', 'min:0', 'max:100', 'gte:COU_AGE_A'],
            'COU_AGE_C' => ['required', 'integer', 'min:0', 'max:100', 'gte:COU_AGE_B'],
        ];
    }

    public function messages(): array
    {
        return [
            'COU_NB_PART_MIN.max_digits' => 'Le nombre minimum de participants est limité à 5 chiffres.',
            'COU_NB_PART_MAX.max_digits' => 'Le nombre maximum de participants est limité à 5 chiffres.',
            'COU_NB_EQU_MIN.max_digits' => 'Le nombre minimum d\'équipes est limité à 5 chiffres.',
            'COU_NB_EQU_MAX.max_digits' => 'Le nombre maximum d\'équipes est limité à 5 chiffres.',
            'COU_PART_PAR_EQU_MAX.max_digits' => 'Le nombre maximum de participants par équipe est limité à 2 chiffres.',
            'COU_PRIX_REPAS.max_digits' => 'Le prix du repas est limité à 10 chiffres.',
            'COU_REDUC_LICENCIE.max_digits' => 'La réduction licencié est limitée à 10 chiffres.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Verify course dates are within raid dates
            $couNum = $this->route('cou_num');
            if ($couNum) {
                $race = VikRace::with('raid')->find($couNum);
                if ($race && $race->raid) {
                    $courseStart = $this->input('COU_DATE_DEPART');
                    $courseEnd = $this->input('COU_DATE_FIN');

                    if ($courseStart && $courseEnd) {
                        $raidStart = Carbon::parse($race->raid->RAID_DATE_DEBUT);
                        $raidEnd = Carbon::parse($race->raid->RAID_DATE_FIN);
                        $cStart = Carbon::parse($courseStart);
                        $cEnd = Carbon::parse($courseEnd);

                        if ($cStart->startOfDay()->lt($raidStart->startOfDay()) || $cStart->startOfDay()->gt($raidEnd->startOfDay())) {
                            $validator->errors()->add(
                                'COU_DATE_DEPART',
                                'La date de départ de la course doit être comprise entre le '.$raidStart->format('d/m/Y').' et le '.$raidEnd->format('d/m/Y').' (inclus).'
                            );
                        }

                        if ($cEnd->startOfDay()->lt($raidStart->startOfDay()) || $cEnd->startOfDay()->gt($raidEnd->startOfDay())) {
                            $validator->errors()->add(
                                'COU_DATE_FIN',
                                'La date de fin de la course doit être comprise entre le '.$raidStart->format('d/m/Y').' et le '.$raidEnd->format('d/m/Y').' (inclus).'
                            );
                        }
                    }
                }
            }
        });
    }
}
