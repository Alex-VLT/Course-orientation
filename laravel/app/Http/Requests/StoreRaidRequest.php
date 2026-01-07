<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

class StoreRaidRequest extends FormRequest
{
    public function authorize(): bool
    {
        // only authenticated users can create a raid; additional club check in controller
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'RAID_NOM' => ['required', 'string', 'max:255'],
            'RAID_DATE_DEBUT' => ['required', 'date'],
            'RAID_DATE_FIN' => ['required', 'date', 'after_or_equal:RAID_DATE_DEBUT'],
            'CLU_NUM' => ['required', 'integer'],
            'INS_ID' => ['required', 'integer', 'exists:VIK_INSCRIT,INS_ID'],
            'RAID_DATE_DEBUT_INSCRI' => ['required', 'date'],
            'RAID_DATE_FIN_INSCRI' => ['required', 'date', 'after_or_equal:RAID_DATE_DEBUT_INSCRI'],
            'RAID_LATITUDE' => ['nullable', 'numeric'],
            'RAID_LONGITUDE' => ['nullable', 'numeric'],
            'RAID_CONTACT' => ['nullable', 'string'],
            'RAID_LIEN_SITE_WEB' => ['nullable', 'url']
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $respId = $this->input('INS_ID');
            if ($respId) {
                $user = User::find($respId);
                if (!$user || !$user->isAdherent()) {
                    $validator->errors()->add('INS_ID', 'Le responsable doit être un adhérent (licence ou identifiant PPS).');
                }
            }
        });
    }
}
