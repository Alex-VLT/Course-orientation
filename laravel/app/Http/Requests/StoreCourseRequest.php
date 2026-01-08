<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use App\Models\VikRaid;
use Carbon\Carbon;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'COU_NOM' => ['required', 'string', 'max:255'],
            'TYP_NUM' => ['required', 'integer', 'exists:VIK_TYPE_COURSE,TYP_NUM'],
            'COU_DATE_DEPART' => ['required', 'date', 'after_or_equal:today'],
            'COU_DATE_FIN' => ['required', 'date', 'after_or_equal:COU_DATE_DEPART'],
            'COU_DUREE' => ['nullable', 'integer'],
            'COU_DIFFICULTE' => ['required', 'string', 'max:64'],
            'COU_NB_PART_MIN' => ['required', 'integer', 'min:1'],
            'COU_NB_PART_MAX' => ['required', 'integer', 'gte:COU_NB_PART_MIN'],
            'COU_NB_EQU_MIN' => ['required', 'integer', 'min:1'],
            'COU_NB_EQU_MAX' => ['required', 'integer', 'gte:COU_NB_EQU_MIN'],
            'COU_PART_PAR_EQU_MAX' => ['required', 'integer', 'min:1'],
            'COU_PRIX_REPAS' => ['nullable', 'numeric', 'min:0'],
            'COU_PRIX_REPAS_LICENCIE' => ['nullable', 'numeric', 'min:0'],
            'COU_AGE_A' => ['required', 'integer', 'min:0', 'max:100'],
            'COU_AGE_B' => ['required', 'integer', 'min:0', 'max:100', 'gte:COU_AGE_A'],
            'COU_AGE_C' => ['required', 'integer', 'min:0', 'max:100', 'gte:COU_AGE_B'],
            'INS_ID' => ['required', 'integer', 'exists:VIK_INSCRIT,INS_ID'],
        ];
    }

    public function messages(): array
    {
        return [
            'COU_NOM.required' => 'Le nom de la course est obligatoire.',
            'COU_NOM.string' => 'Le nom de la course doit être un texte.',
            'COU_NOM.max' => 'Le nom de la course ne doit pas dépasser 255 caractères.',
            
            'TYP_NUM.required' => 'Le type de course est obligatoire.',
            'TYP_NUM.integer' => 'Le type de course doit être valide.',
            'TYP_NUM.exists' => 'Le type de course sélectionné est invalide.',
            
            'COU_DATE_DEPART.required' => 'La date de départ est obligatoire.',
            'COU_DATE_DEPART.date' => 'La date de départ doit être une date valide.',
            'COU_DATE_DEPART.after_or_equal' => 'La date de départ ne peut pas être dans le passé.',
            
            'COU_DATE_FIN.required' => 'La date de fin est obligatoire.',
            'COU_DATE_FIN.date' => 'La date de fin doit être une date valide.',
            'COU_DATE_FIN.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de départ.',
            
            'COU_DIFFICULTE.required' => 'Le niveau de difficulté est obligatoire.',
            'COU_DIFFICULTE.string' => 'Le niveau de difficulté doit être un texte.',
            'COU_DIFFICULTE.max' => 'Le niveau de difficulté ne doit pas dépasser 64 caractères.',
            
            'COU_NB_PART_MIN.required' => 'Le nombre minimum de participants est obligatoire.',
            'COU_NB_PART_MIN.integer' => 'Le nombre minimum de participants doit être un nombre entier.',
            'COU_NB_PART_MIN.min' => 'Le nombre minimum de participants doit être au moins 1.',
            
            'COU_NB_PART_MAX.required' => 'Le nombre maximum de participants est obligatoire.',
            'COU_NB_PART_MAX.integer' => 'Le nombre maximum de participants doit être un nombre entier.',
            'COU_NB_PART_MAX.gte' => 'Le nombre maximum de participants doit être supérieur ou égal au minimum.',
            
            'COU_NB_EQU_MIN.required' => 'Le nombre minimum d\'équipes est obligatoire.',
            'COU_NB_EQU_MIN.integer' => 'Le nombre minimum d\'équipes doit être un nombre entier.',
            'COU_NB_EQU_MIN.min' => 'Le nombre minimum d\'équipes doit être au moins 1.',
            
            'COU_NB_EQU_MAX.required' => 'Le nombre maximum d\'équipes est obligatoire.',
            'COU_NB_EQU_MAX.integer' => 'Le nombre maximum d\'équipes doit être un nombre entier.',
            'COU_NB_EQU_MAX.gte' => 'Le nombre maximum d\'équipes doit être supérieur ou égal au minimum.',
            
            'COU_PART_PAR_EQU_MAX.required' => 'Le nombre maximum de participants par équipe est obligatoire.',
            'COU_PART_PAR_EQU_MAX.integer' => 'Le nombre maximum de participants par équipe doit être un nombre entier.',
            'COU_PART_PAR_EQU_MAX.min' => 'Le nombre maximum de participants par équipe doit être au moins 1.',
            
            'COU_PRIX_REPAS.numeric' => 'Le prix du repas doit être un nombre.',
            'COU_PRIX_REPAS.min' => 'Le prix du repas ne peut pas être négatif.',
            
            'COU_PRIX_REPAS_LICENCIE.numeric' => 'Le prix du repas pour licencié doit être un nombre.',
            'COU_PRIX_REPAS_LICENCIE.min' => 'Le prix du repas pour licencié ne peut pas être négatif.',
            
            'COU_AGE_A.required' => 'L\'âge A est obligatoire.',
            'COU_AGE_A.integer' => 'L\'âge A doit être un nombre entier.',
            'COU_AGE_A.min' => 'L\'âge A ne peut pas être négatif.',
            'COU_AGE_A.max' => 'L\'âge A ne doit pas dépasser 100.',
            
            'COU_AGE_B.required' => 'L\'âge B est obligatoire.',
            'COU_AGE_B.integer' => 'L\'âge B doit être un nombre entier.',
            'COU_AGE_B.min' => 'L\'âge B ne peut pas être négatif.',
            'COU_AGE_B.max' => 'L\'âge B ne doit pas dépasser 100.',
            'COU_AGE_B.gte' => 'L\'âge B doit être supérieur ou égal à l\'âge A.',
            
            'COU_AGE_C.required' => 'L\'âge C est obligatoire.',
            'COU_AGE_C.integer' => 'L\'âge C doit être un nombre entier.',
            'COU_AGE_C.min' => 'L\'âge C ne peut pas être négatif.',
            'COU_AGE_C.max' => 'L\'âge C ne doit pas dépasser 100.',
            'COU_AGE_C.gte' => 'L\'âge C doit être supérieur ou égal à l\'âge B.',
            
            'INS_ID.required' => 'Le responsable de la course est obligatoire.',
            'INS_ID.integer' => 'Le responsable sélectionné est invalide.',
            'INS_ID.exists' => 'Le responsable sélectionné n\'existe pas.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Verify responsible is adherent
            $respId = $this->input('INS_ID');
            if ($respId) {
                $user = User::find($respId);
                if (!$user || !$user->isAdherent()) {
                    $validator->errors()->add('INS_ID', 'Le responsable de la course doit être un adhérent.');
                }
            }

            // Verify course dates are within raid dates
            $raidNum = $this->route('raid_num');
            if ($raidNum) {
                $raid = VikRaid::find($raidNum);
                if ($raid) {
                    $courseStart = $this->input('COU_DATE_DEPART');
                    $courseEnd = $this->input('COU_DATE_FIN');

                    if ($courseStart && $courseEnd) {
                        $raidStart = Carbon::parse($raid->RAID_DATE_DEBUT);
                        $raidEnd = Carbon::parse($raid->RAID_DATE_FIN);
                        $cStart = Carbon::parse($courseStart);
                        $cEnd = Carbon::parse($courseEnd);

                        if ($cStart->lt($raidStart) || $cStart->gt($raidEnd)) {
                            $validator->errors()->add(
                                'COU_DATE_DEPART',
                                'La date de départ de la course doit être comprise entre le ' . $raidStart->format('d/m/Y') . ' et le ' . $raidEnd->format('d/m/Y') . '.'
                            );
                        }

                        if ($cEnd->lt($raidStart) || $cEnd->gt($raidEnd)) {
                            $validator->errors()->add(
                                'COU_DATE_FIN',
                                'La date de fin de la course doit être comprise entre le ' . $raidStart->format('d/m/Y') . ' et le ' . $raidEnd->format('d/m/Y') . '.'
                            );
                        }
                    }
                }
            }
        });
    }
}
