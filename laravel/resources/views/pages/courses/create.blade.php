@extends('layouts.app')

@section('title', "Créer une course - L'Embuscade")

@section('content')
<div class="min-h-screen py-12 bg-gray-50/50">
    <div class="max-w-5xl mx-auto bg-white p-8 md:p-10 rounded-2xl shadow-xl border border-gray-100">
        
        {{-- Header Section --}}
        <div class="mb-10 border-b border-gray-100 pb-6">
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Créer une course</h1>
            <p class="text-gray-500 mt-2 text-lg">Pour le raid : <span class="font-bold text-[#A67C52]">« {{ $raid->RAID_NOM }} »</span></p>
        </div>

        {{-- Error Display --}}
        @if($errors->any())
            <div class="mb-8 rounded-lg border-l-4 border-red-500 bg-red-50 p-4 shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-bold text-red-800">Des erreurs sont présentes :</h3>
                        <ul class="mt-2 list-disc list-inside text-sm text-red-700">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('race.store', $raid->RAID_NUM) }}" id="courseForm">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold">Nom de la course <span class="text-red-600">*</span></label>
                    <input name="COU_NOM" value="{{ old('COU_NOM') }}" required class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                </div>

                <div>
                    <label class="block text-sm font-semibold">Type de course <span class="text-red-600">*</span></label>
                    <select name="TYP_NUM" required class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2">
                        <option value="">-- Sélectionner un type --</option>
                        @foreach($types as $type)
                            <option value="{{ $type->TYP_NUM }}" {{ old('TYP_NUM') == $type->TYP_NUM ? 'selected' : '' }}>
                                {{ $type->TYP_LABEL }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold">Date départ <span class="text-red-600">*</span></label>
                        <input name="COU_DATE_DEPART" id="COU_DATE_DEPART" type="datetime-local" value="{{ old('COU_DATE_DEPART') }}" required class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold">Date fin <span class="text-red-600">*</span></label>
                        <input name="COU_DATE_FIN" id="COU_DATE_FIN" type="datetime-local" value="{{ old('COU_DATE_FIN') }}" required class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                    </div>
                </div>

                <div class="text-xs text-gray-600 bg-blue-50 border border-blue-200 rounded p-2">
                    <strong>Dates du raid :</strong> 
                    Du {{ \Carbon\Carbon::parse($raid->RAID_DATE_DEBUT)->format('d/m/Y') }} 
                    au {{ \Carbon\Carbon::parse($raid->RAID_DATE_FIN)->format('d/m/Y') }}
                </div>

                <div>
                    <label class="block text-sm font-semibold">Durée (minutes) <span class="text-red-600">*</span></label>
                    <input name="COU_DUREE" type="number" min="1" value="{{ old('COU_DUREE') }}" required class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                </div>

                <div>
                    <label class="block text-sm font-semibold">Difficulté <span class="text-red-600">*</span></label>
                    <input name="COU_DIFFICULTE" value="{{ old('COU_DIFFICULTE') }}" required class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" placeholder="Ex: Facile, Modérée, Difficile" />
                </div>

                <div class="border-t pt-4">
                    <h3 class="text-lg font-bold mb-3">Participants et Équipes</h3>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold">Nb min de participants <span class="text-red-600">*</span></label>
                            <input name="COU_NB_PART_MIN" id="COU_NB_PART_MIN" type="number" min="1" max="99999" value="{{ old('COU_NB_PART_MIN') }}" required class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold">Nb max de participants <span class="text-red-600">*</span></label>
                            <input name="COU_NB_PART_MAX" id="COU_NB_PART_MAX" type="number" min="1" max="99999" value="{{ old('COU_NB_PART_MAX') }}" required class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-3">
                        <div>
                            <label class="block text-sm font-semibold">Nb min d'équipes <span class="text-red-600">*</span></label>
                            <input name="COU_NB_EQU_MIN" id="COU_NB_EQU_MIN" type="number" min="1" max="99999" value="{{ old('COU_NB_EQU_MIN') }}" required class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold">Nb max d'équipes <span class="text-red-600">*</span></label>
                            <input name="COU_NB_EQU_MAX" id="COU_NB_EQU_MAX" type="number" min="1" max="99999" value="{{ old('COU_NB_EQU_MAX') }}" required class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="block text-sm font-semibold">Nb max de participants par équipe <span class="text-red-600">*</span></label>
                        <input name="COU_PART_PAR_EQU_MAX" type="number" min="1" max="99" value="{{ old('COU_PART_PAR_EQU_MAX') }}" required class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                    </div>
                </div>

                <div class="border-t pt-4">
                    <h3 class="text-lg font-bold mb-3">Tarifs</h3>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold">Prix du repas (€)</label>
                            <input name="COU_PRIX_REPAS" type="number" step="0.01" min="0" max="9999999999" value="{{ old('COU_PRIX_REPAS') }}" class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                        </div>
                        <div>
                                <label class="block text-sm font-semibold">Réduction Licencié (€)</label>
                                <input name="COU_REDUC_LICENCIE" type="number" step="0.01" min="0" max="9999999999" value="{{ old('COU_REDUC_LICENCIE') }}" class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                        </div>
                    </div>
                </div>

                <div class="border-t pt-4">
                    <h3 class="text-lg font-bold mb-3">Tranches d'âge</h3>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4 text-sm">
                        <p class="font-semibold text-blue-900 mb-2">Règles de composition des équipes :</p>
                        <ul class="list-disc list-inside space-y-1 text-blue-800">
                            <li><strong>A ≤ B ≤ C</strong> : Trois valeurs respectant cet ordre</li>
                            <li>Tous les participants doivent avoir <strong>au moins l'âge A</strong></li>
                            <li>Soit <strong>un participant a au moins l'âge C</strong>, soit <strong>tous ont au moins l'âge B</strong></li>
                        </ul>
                        <p class="text-blue-800 mt-3 italic">
                            <strong>Exemple :</strong> Avec A=12, B=16, C=18 : tous doivent avoir 12 ans minimum. 
                            Les équipes avec un participant de moins de 16 ans doivent avoir un participant majeur (18 ans).
                        </p>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm font-semibold">Âge A <span class="text-red-600">*</span></label>
                            <input name="COU_AGE_A" id="COU_AGE_A" type="number" min="0" max="100" value="{{ old('COU_AGE_A') }}" required class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                            <p class="text-xs text-gray-500 mt-1">Âge minimum de tous</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold">Âge B <span class="text-red-600">*</span></label>
                            <input name="COU_AGE_B" id="COU_AGE_B" type="number" min="0" max="100" value="{{ old('COU_AGE_B') }}" required class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                            <p class="text-xs text-gray-500 mt-1">Âge intermédiaire</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold">Âge C <span class="text-red-600">*</span></label>
                            <input name="COU_AGE_C" id="COU_AGE_C" type="number" min="0" max="100" value="{{ old('COU_AGE_C') }}" required class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                            <p class="text-xs text-gray-500 mt-1">Âge maximum (référence)</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold">Responsable de la course (adhérent) <span class="text-red-600">*</span></label>
                    <select name="INS_ID" required class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2">
                        <option value="">-- Sélectionner un responsable --</option>
                        @foreach($responsibles as $r)
                            <option value="{{ $r->INS_ID }}" {{ old('INS_ID') == $r->INS_ID ? 'selected' : '' }}>
                                {{ $r->INS_PRENOM }} {{ $r->INS_NOM }} — {{ $r->INS_NUM_LICENCE ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="date-validation-errors" class="text-red-600 text-sm"></div>
            </div>
            {{-- ACTIONS --}}
            <div class="flex items-center justify-end gap-6 pt-8 border-t border-gray-100">
                <a href="{{ url()->previous() }}" class="text-gray-500 hover:text-gray-900 font-medium px-4 py-2 rounded transition">
                    Annuler
                </a>
                <button type="submit" class="bg-black hover:bg-gray-800 text-white font-bold py-4 px-10 rounded-xl shadow-lg transform hover:-translate-y-1 hover:shadow-xl transition duration-200">
                    Créer la course
                </button>
            </div>

        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('courseForm');
    const courseStart = document.getElementById('COU_DATE_DEPART');
    const courseEnd = document.getElementById('COU_DATE_FIN');
    const errorsEl = document.getElementById('date-validation-errors');

    // Champs participants/équipes
    const partMin = document.getElementById('COU_NB_PART_MIN');
    const partMax = document.getElementById('COU_NB_PART_MAX');
    const equMin = document.getElementById('COU_NB_EQU_MIN');
    const equMax = document.getElementById('COU_NB_EQU_MAX');
    const partPerTeamMax = document.querySelector('[name="COU_PART_PAR_EQU_MAX"]');

    // Champs tarifs
    const mealPrice = document.querySelector('[name="COU_PRIX_REPAS"]');
    const licensedDiscount = document.querySelector('[name="COU_REDUC_LICENCIE"]');

    // Champs tranches d'âge
    const ageA = document.getElementById('COU_AGE_A');
    const ageB = document.getElementById('COU_AGE_B');
    const ageC = document.getElementById('COU_AGE_C');

    // Raid dates from server
    const raidStart = new Date('{{ $raid->RAID_DATE_DEBUT->format('Y-m-d') }}T00:00:00');
    const raidEnd = new Date('{{ $raid->RAID_DATE_FIN->format('Y-m-d') }}T23:59:59');

    function validateDates() {
        const errors = [];
        const now = new Date();
        now.setHours(0, 0, 0, 0);

        if (!courseStart.value || !courseEnd.value) {
            return errors;
        }

        const cStart = new Date(courseStart.value);
        const cEnd = new Date(courseEnd.value);

        // Check not in past
        if (cStart < now) {
            errors.push('La date de départ ne peut pas être dans le passé.');
        }

        // Check start before end
        if (cEnd < cStart) {
            errors.push('La date de fin doit être postérieure ou égale à la date de départ.');
        }

        // Check within raid dates
        if (cStart < raidStart || cStart > raidEnd) {
            errors.push('La date de départ doit être comprise entre le {{ \Carbon\Carbon::parse($raid->RAID_DATE_DEBUT)->format('d/m/Y') }} et le {{ \Carbon\Carbon::parse($raid->RAID_DATE_FIN)->format('d/m/Y') }}.');
        }

        if (cEnd < raidStart || cEnd > raidEnd) {
            errors.push('La date de fin doit être comprise entre le {{ \Carbon\Carbon::parse($raid->RAID_DATE_DEBUT)->format('d/m/Y') }} et le {{ \Carbon\Carbon::parse($raid->RAID_DATE_FIN)->format('d/m/Y') }}.');
        }

        return errors;
    }

    function validateMinMax() {
        const errors = [];

        // Validation participants min/max
        if (partMin.value && partMax.value) {
            const min = parseInt(partMin.value);
            const max = parseInt(partMax.value);
            if (max < min) {
                errors.push('Le nombre maximum de participants doit être supérieur ou égal au minimum.');
            }
        }

        // Validation équipes min/max
        if (equMin.value && equMax.value) {
            const min = parseInt(equMin.value);
            const max = parseInt(equMax.value);
            if (max < min) {
                errors.push('Le nombre maximum d\'\u00e9quipes doit être supérieur ou égal au minimum.');
            }
        }

        return errors;
    }

    function validateAges() {
        const errors = [];

        if (ageA.value && ageB.value && ageC.value) {
            const a = parseInt(ageA.value);
            const b = parseInt(ageB.value);
            const c = parseInt(ageC.value);

            if (a > b) {
                errors.push('L\'\u00e2ge B doit être supérieur ou égal à l\'\u00e2ge A.');
            }
            if (b > c) {
                errors.push('L\'\u00e2ge C doit être supérieur ou égal à l\'\u00e2ge B.');
            }
        }

        return errors;
    }

    function integerDigitsLength(value) {
        if (value === null || value === undefined) {
            return 0;
        }

        const str = String(value).trim();
        if (str === '') {
            return 0;
        }

        const digits = str.replace(/\D/g, '');

        return digits.length;
    }

    function priceIntegerDigitsLength(value) {
        if (value === null || value === undefined) {
            return 0;
        }

        const str = String(value).trim();
        if (str === '') {
            return 0;
        }

        const integerPart = str.split(/[.,]/)[0];

        return integerPart.replace(/\D/g, '').length;
    }

    function enforceMaxDigits(input, maxDigits, mode) {
        if (!input) {
            return;
        }

        const value = input.value;
        if (!value) {
            return;
        }

        const length = mode === 'price' ? priceIntegerDigitsLength(value) : integerDigitsLength(value);
        if (length <= maxDigits) {
            return;
        }

        const maxAttr = input.getAttribute('max');
        if (maxAttr) {
            input.value = maxAttr;
        } else {
            input.value = value.slice(0, -1);
        }
    }

    function validateMaxDigits() {
        const errors = [];

        const constraints = [
            { input: partMin, maxDigits: 5, label: 'Nb min de participants' },
            { input: partMax, maxDigits: 5, label: 'Nb max de participants' },
            { input: equMin, maxDigits: 5, label: "Nb min d'équipes" },
            { input: equMax, maxDigits: 5, label: "Nb max d'équipes" },
            { input: partPerTeamMax, maxDigits: 2, label: "Nb max de participants par équipe" },
        ];

        for (const c of constraints) {
            if (!c.input || !c.input.value) {
                continue;
            }

            if (integerDigitsLength(c.input.value) > c.maxDigits) {
                errors.push(`${c.label} est limité à ${c.maxDigits} chiffres.`);
            }
        }

        const priceConstraints = [
            { input: mealPrice, maxDigits: 10, label: 'Prix du repas' },
            { input: licensedDiscount, maxDigits: 10, label: 'Réduction licencié' },
        ];

        for (const c of priceConstraints) {
            if (!c.input || !c.input.value) {
                continue;
            }

            if (priceIntegerDigitsLength(c.input.value) > c.maxDigits) {
                errors.push(`${c.label} est limité à ${c.maxDigits} chiffres.`);
            }
        }

        return errors;
    }

    function displayErrors(errors) {
        if (errors.length > 0) {
            errorsEl.innerHTML = '<ul class="list-disc pl-5">' + errors.map(e => '<li>' + e + '</li>').join('') + '</ul>';
        } else {
            errorsEl.innerHTML = '';
        }
    }

    function validateAll() {
        const allErrors = [...validateDates(), ...validateMinMax(), ...validateAges(), ...validateMaxDigits()];
        displayErrors(allErrors);
        return allErrors;
    }

    // Event listeners
    courseStart.addEventListener('change', validateAll);
    courseEnd.addEventListener('change', validateAll);
    partPerTeamMax.addEventListener('input', function() {
        enforceMaxDigits(partPerTeamMax, 2, 'int');
        validateAll();
    });
    mealPrice.addEventListener('input', function() {
        enforceMaxDigits(mealPrice, 10, 'price');
        validateAll();
    });
    licensedDiscount.addEventListener('input', function() {
        enforceMaxDigits(licensedDiscount, 10, 'price');
        validateAll();
    });
    ageA.addEventListener('input', validateAll);
    ageB.addEventListener('input', validateAll);
    ageC.addEventListener('input', validateAll);

    partMin.addEventListener('input', function() {
        enforceMaxDigits(partMin, 5, 'int');
        validateAll();
    });
    partMax.addEventListener('input', function() {
        enforceMaxDigits(partMax, 5, 'int');
        validateAll();
    });
    equMin.addEventListener('input', function() {
        enforceMaxDigits(equMin, 5, 'int');
        validateAll();
    });
    equMax.addEventListener('input', function() {
        enforceMaxDigits(equMax, 5, 'int');
        validateAll();
    });

    form.addEventListener('submit', function(e) {
        const errors = validateAll();
        if (errors.length > 0) {
            e.preventDefault();
            displayErrors(errors);
            errorsEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
        return true;
    });
});
</script>
@endpush
@endsection
