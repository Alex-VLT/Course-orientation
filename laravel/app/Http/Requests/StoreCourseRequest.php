<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

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
            'COU_DATE_DEPART' => ['required', 'date'],
            'COU_DATE_FIN' => ['required', 'date', 'after_or_equal:COU_DATE_DEPART'],
            'COU_DUREE' => ['nullable', 'integer'],
            'COU_DIFFICULTE' => ['nullable', 'integer'],
            'COU_NB_PART_MIN' => ['nullable', 'integer'],
            'COU_NB_PART_MAX' => ['nullable', 'integer'],
            'INS_ID' => ['required', 'integer', 'exists:VIK_INSCRIT,INS_ID'],
        ];
    }

    public function messages(): array
    {
        return [
            'COU_NOM.required' => 'Le nom de la course est obligatoire.',
            'COU_DATE_DEPART.required' => 'La date de départ est obligatoire.',
            'COU_DATE_FIN.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de départ.',
            'INS_ID.required' => 'Le responsable de la course est obligatoire.',
            'INS_ID.exists' => 'Le responsable sélectionné est invalide.',
        ];
    }

    public function attributes(): array
    {
        return [
            'INS_ID' => 'responsable de la course',
            'COU_NOM' => 'nom de la course',
            'COU_DATE_DEPART' => 'date de départ',
            'COU_DATE_FIN' => 'date de fin',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $respId = $this->input('INS_ID');
            if ($respId) {
                $user = User::find($respId);
                if (!$user || !$user->isAdherent()) {
                    $validator->errors()->add('INS_ID', 'Le responsable de la course doit être un adhérent.');
                }
            }
        });
    }
}
