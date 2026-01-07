<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

class StoreRaidRequest extends FormRequest
{
    public function authorize(): bool
    {
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
        'RAID_CONTACT' => ['required', 'string', 'max:100'], // ← Obligatoire, email OU tel
        'RAID_ILLUSTRATION' => ['nullable', 'image', 'max:2048'],
        'RAID_LATITUDE' => ['required', 'numeric', 'between:-90,90'],
        'RAID_LONGITUDE' => ['required', 'numeric', 'between:-180,180'],
        'RAID_LIEN_SITE_WEB' => ['nullable', 'url']
    ];
}



    public function messages(): array
    {
        return [
            'RAID_NOM.required' => 'Le nom du raid est obligatoire.',
            'RAID_DATE_DEBUT.required' => 'La date de début du raid est obligatoire.',
            'RAID_DATE_FIN.required' => 'La date de fin du raid est obligatoire.',
            'RAID_DATE_FIN.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
            'RAID_DATE_DEBUT_INSCRI.required' => "La date de début des inscriptions est obligatoire.",
            'RAID_DATE_FIN_INSCRI.required' => "La date de fin des inscriptions est obligatoire.",
            'RAID_DATE_FIN_INSCRI.after_or_equal' => "La date de fin des inscriptions doit être postérieure ou égale à la date de début des inscriptions.",
            'CLU_NUM.required' => "Le club organisateur est obligatoire.",
            'INS_ID.required' => "Le gérant du raid est obligatoire.",
            'INS_ID.exists' => "Le gérant sélectionné est invalide.",
            'RAID_LIEN_SITE_WEB.url' => "Le lien du site n'est pas une URL valide.",
            'RAID_LATITUDE.numeric' => "La latitude doit être un nombre.",
            'RAID_LONGITUDE.numeric' => "La longitude doit être un nombre.",
            'RAID_LATITUDE.required' => "L'emplacement (latitude) est obligatoire.",
            'RAID_LONGITUDE.required' => "L'emplacement (longitude) est obligatoire.",
            'RAID_LATITUDE.between' => "La latitude doit être comprise entre -90 et 90.",
            'RAID_LONGITUDE.between' => "La longitude doit être comprise entre -180 et 180.",
            'RAID_CONTACT_MAIL.email' => "L'adresse e-mail du contact n'est pas valide.",
            'RAID_ILLUSTRATION.image' => "L'illustration doit être une image (jpg, png, gif).",
            'RAID_ILLUSTRATION.max' => "L'illustration ne doit pas dépasser 2MB.",
            'RAID_CONTACT.required' => "Veuillez fournir un contact: email ou téléphone.",
            'RAID_CONTACT.max' => "Le contact est trop long.",
        ];
    }

    public function attributes(): array
    {
        return [
            'INS_ID' => "gérant du raid",
            'RAID_NOM' => "nom du raid",
            'RAID_DATE_DEBUT' => "date de début",
            'RAID_DATE_FIN' => "date de fin",
            'RAID_DATE_DEBUT_INSCRI' => "date début des inscriptions",
            'RAID_DATE_FIN_INSCRI' => "date fin des inscriptions",
            'CLU_NUM' => "club organisateur",
            'RAID_ILLUSTRATION' => "illustration du raid",
            'RAID_CONTACT' => "téléphone ou mail du contact",
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $contact = $this->input('RAID_CONTACT');
            
            if ($contact) {
                $isEmail = filter_var($contact, FILTER_VALIDATE_EMAIL);
                $isPhone = preg_match('/^[\d\s\+\-\(\)\.]+$/', $contact) && strlen(preg_replace('/\D/', '', $contact)) >= 10;
                
                if (!$isEmail && !$isPhone) {
                    $validator->errors()->add('RAID_CONTACT', 'Le contact doit être un email valide ou un numéro de téléphone valide.');
                }
            }
            
            $respId = $this->input('INS_ID');
            if ($respId) {
                $user = User::find($respId);
                if (!$user || !$user->isAdherent()) {
                    $validator->errors()->add('INS_ID', 'Le responsable doit être un adhérent (licence ou identifiant PPS).');
                }

                $club = $this->input('CLU_NUM');
                if ($club) {
                    $exists = \Illuminate\Support\Facades\DB::table('VIK_ADHERER')
                        ->where('INS_ID', $respId)
                        ->where('CLU_NUM', $club)
                        ->exists();
                    if (!$exists) {
                        $validator->errors()->add('INS_ID', 'Le gérant doit être membre du club sélectionné.');
                    }
                }
            }
        });
    }
}
