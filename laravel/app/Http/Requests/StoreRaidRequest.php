<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRaidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // DB: char(124) -> On limite à 124 sinon SQL Error
            'RAID_NOM' => ['required', 'string', 'max:124'],
            
            'CLU_NUM' => ['required', 'integer', 'exists:VIK_CLUB,CLU_NUM'],
            'INS_ID' => ['required', 'integer', 'exists:VIK_INSCRIT,INS_ID'],
            
            // --- DATES DU RAID ---
            'RAID_DATE_DEBUT' => ['required', 'date', 'after_or_equal:today'],
            'RAID_DATE_FIN' => ['required', 'date', 'after_or_equal:RAID_DATE_DEBUT'],

            // --- DATES INSCRIPTIONS ---
            'RAID_DATE_DEBUT_INSCRI' => ['required', 'date', 'after_or_equal:today'],
            
            'RAID_DATE_FIN_INSCRI' => [
                'required', 
                'date', 
                'after_or_equal:RAID_DATE_DEBUT_INSCRI', // Fin après Début Inscr
                'before:RAID_DATE_DEBUT' // Fin Inscr AVANT Début du Raid (Ta demande)
            ],

            'RAID_CONTACT' => ['required', 'string', 'max:100'], // DB: text mais restons raisonnable
            
            'RAID_ILLUSTRATION' => ['nullable', 'image', 'max:2048'],
            
            'RAID_LATITUDE' => ['required', 'numeric', 'between:-90,90'],
            'RAID_LONGITUDE' => ['required', 'numeric', 'between:-180,180'],
            
            // DB: char(32) -> C'est très court pour une URL ! 
            // Je mets max:32 pour éviter le crash SQL, mais attention.
            'RAID_LIEN_SITE_WEB' => ['nullable', 'url', 'max:32'] 
        ];
    }

    public function messages(): array
    {
        return [
            'RAID_NOM.required' => 'Le nom du raid est obligatoire.',
            'RAID_NOM.max' => 'Le nom du raid ne doit pas dépasser 124 caractères.',
            
            'RAID_DATE_DEBUT.after_or_equal' => 'Le raid ne peut pas commencer dans le passé.',
            'RAID_DATE_FIN.after_or_equal' => 'La fin du raid doit être après son début.',
            
            'RAID_DATE_DEBUT_INSCRI.after_or_equal' => "L'ouverture des inscriptions ne peut pas être dans le passé.",
            
            'RAID_DATE_FIN_INSCRI.after_or_equal' => "La clôture des inscriptions doit être après l'ouverture.",
            'RAID_DATE_FIN_INSCRI.before' => "Les inscriptions doivent être closes AVANT le début du raid.",

            'RAID_LIEN_SITE_WEB.max' => "L'URL est trop longue (max 32 caractères selon la base de données).",
            
            // ... tes autres messages si tu veux ...
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validation Email ou Téléphone pour le contact
            $contact = $this->input('RAID_CONTACT');
            if ($contact) {
                $isEmail = filter_var($contact, FILTER_VALIDATE_EMAIL);
                $isPhone = preg_match('/^[\d\s\+\-\(\)\.]+$/', $contact) && strlen(preg_replace('/\D/', '', $contact)) >= 10;
                
                if (!$isEmail && !$isPhone) {
                    $validator->errors()->add('RAID_CONTACT', 'Le contact doit être un email valide ou un numéro de téléphone valide.');
                }
            }
        });
    }
}