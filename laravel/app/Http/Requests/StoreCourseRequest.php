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
