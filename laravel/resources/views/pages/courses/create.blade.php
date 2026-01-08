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
                    <label class="block text-sm font-semibold">Difficulté <span class="text-red-600">*</span></label>
                    <input name="COU_DIFFICULTE" value="{{ old('COU_DIFFICULTE') }}" required class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" placeholder="Ex: Facile, Modérée, Difficile" />
                </div>

                <div class="border-t pt-4">
                    <h3 class="text-lg font-bold mb-3">Participants et Équipes</h3>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold">Nb min de participants <span class="text-red-600">*</span></label>
                            <input name="COU_NB_PART_MIN" id="COU_NB_PART_MIN" type="number" min="1" value="{{ old('COU_NB_PART_MIN') }}" required class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold">Nb max de participants <span class="text-red-600">*</span></label>
                            <input name="COU_NB_PART_MAX" id="COU_NB_PART_MAX" type="number" min="1" value="{{ old('COU_NB_PART_MAX') }}" required class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-3">
                        <div>
                            <label class="block text-sm font-semibold">Nb min d'équipes <span class="text-red-600">*</span></label>
                            <input name="COU_NB_EQU_MIN" id="COU_NB_EQU_MIN" type="number" min="1" value="{{ old('COU_NB_EQU_MIN') }}" required class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold">Nb max d'équipes <span class="text-red-600">*</span></label>
                            <input name="COU_NB_EQU_MAX" id="COU_NB_EQU_MAX" type="number" min="1" value="{{ old('COU_NB_EQU_MAX') }}" required class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="block text-sm font-semibold">Nb max de participants par équipe <span class="text-red-600">*</span></label>
                        <input name="COU_PART_PAR_EQU_MAX" type="number" min="1" value="{{ old('COU_PART_PAR_EQU_MAX') }}" required class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                    </div>
                </div>

                <div class="border-t pt-4">
                    <h3 class="text-lg font-bold mb-3">Tarifs</h3>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold">Prix du repas (€)</label>
                            <input name="COU_PRIX_REPAS" type="number" step="0.01" min="0" value="{{ old('COU_PRIX_REPAS') }}" class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold">Prix du repas pour licencié (€)</label>
                            <input name="COU_PRIX_REPAS_LICENCIE" type="number" step="0.01" min="0" value="{{ old('COU_PRIX_REPAS_LICENCIE') }}" class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
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

                <div class="flex justify-end gap-3">
                    <a href="{{ route('raids.manager') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-black hover:bg-gray-100">Annuler</a>
                    <button type="submit" class="rounded-md bg-black px-4 py-2 text-white hover:bg-gray-800">Créer la course</button>
                </div>
            </div>

            {{-- SECTION 2: GAUGES AND TEAMS --}}
            <div class="pt-8 border-t border-gray-100">
                <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                    <span class="bg-[#7DC2A5] text-white w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shadow-sm">2</span>
                    Jauges & Équipes
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    {{-- Participants Limits --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Min. Participants</label>
                        <input name="COU_NB_PART_MIN" type="number" min="1" value="{{ old('COU_NB_PART_MIN', 10) }}" 
                               class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-900 py-3 px-4 shadow-sm focus:border-[#7DC2A5] focus:bg-white focus:ring-2 focus:ring-[#7DC2A5] focus:ring-opacity-50 transition duration-200" required />
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Max. Participants</label>
                        <input name="COU_NB_PART_MAX" type="number" min="1" value="{{ old('COU_NB_PART_MAX', 100) }}" 
                               class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-900 py-3 px-4 shadow-sm focus:border-[#7DC2A5] focus:bg-white focus:ring-2 focus:ring-[#7DC2A5] focus:ring-opacity-50 transition duration-200" required />
                    </div>
                    
                    {{-- Max Team Size --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Max. Pers / Équipe</label>
                        <input name="COU_PART_PAR_EQU_MAX" type="number" min="1" value="{{ old('COU_PART_PAR_EQU_MAX', 4) }}" 
                               class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-900 py-3 px-4 shadow-sm focus:border-[#7DC2A5] focus:bg-white focus:ring-2 focus:ring-[#7DC2A5] focus:ring-opacity-50 transition duration-200" required />
                    </div>

                    {{-- Teams Limits --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Min. Équipes</label>
                        <input name="COU_NB_EQU_MIN" type="number" min="1" value="{{ old('COU_NB_EQU_MIN', 5) }}" 
                               class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-900 py-3 px-4 shadow-sm focus:border-[#7DC2A5] focus:bg-white focus:ring-2 focus:ring-[#7DC2A5] focus:ring-opacity-50 transition duration-200" required />
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Max. Équipes</label>
                        <input name="COU_NB_EQU_MAX" type="number" min="1" value="{{ old('COU_NB_EQU_MAX', 25) }}" 
                               class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-900 py-3 px-4 shadow-sm focus:border-[#7DC2A5] focus:bg-white focus:ring-2 focus:ring-[#7DC2A5] focus:ring-opacity-50 transition duration-200" required />
                    </div>
                </div>
            </div>

            {{-- SECTION 3: AGES & PRICES --}}
            <div class="pt-8 border-t border-gray-100">
                <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                    <span class="bg-[#7DC2A5] text-white w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shadow-sm">3</span>
                    Âges & Tarifs
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    {{-- Ages --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Âge Cat. A</label>
                        <input name="COU_AGE_A" type="number" min="0" value="{{ old('COU_AGE_A', 12) }}" 
                               class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-900 py-3 px-4 shadow-sm focus:border-[#7DC2A5] focus:bg-white focus:ring-2 focus:ring-[#7DC2A5] focus:ring-opacity-50 transition duration-200" required />
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Âge Cat. B</label>
                        <input name="COU_AGE_B" type="number" min="0" value="{{ old('COU_AGE_B', 18) }}" 
                               class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-900 py-3 px-4 shadow-sm focus:border-[#7DC2A5] focus:bg-white focus:ring-2 focus:ring-[#7DC2A5] focus:ring-opacity-50 transition duration-200" required />
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Âge Cat. C</label>
                        <input name="COU_AGE_C" type="number" min="0" value="{{ old('COU_AGE_C', 99) }}" 
                               class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-900 py-3 px-4 shadow-sm focus:border-[#7DC2A5] focus:bg-white focus:ring-2 focus:ring-[#7DC2A5] focus:ring-opacity-50 transition duration-200" required />
                    </div>

                    {{-- Prices --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Prix Repas (€)</label>
                        <div class="relative rounded-lg shadow-sm">
                            <input name="COU_PRIX_REPAS" type="number" step="0.01" min="0" value="{{ old('COU_PRIX_REPAS', 0) }}" 
                                   class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-900 py-3 px-4 pr-12 shadow-sm focus:border-[#7DC2A5] focus:bg-white focus:ring-2 focus:ring-[#7DC2A5] focus:ring-opacity-50 transition duration-200" />
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <span class="text-gray-500 sm:text-sm">EUR</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Réduc. Licencié (€)</label>
                        <div class="relative rounded-lg shadow-sm">
                            <input name="COU_REDUC_LICENCIE" type="number" step="0.01" min="0" value="{{ old('COU_REDUC_LICENCIE', 0) }}" 
                                   class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-900 py-3 px-4 pr-12 shadow-sm focus:border-[#7DC2A5] focus:bg-white focus:ring-2 focus:ring-[#7DC2A5] focus:ring-opacity-50 transition duration-200" />
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <span class="text-gray-500 sm:text-sm">EUR</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 4: OPTIONS --}}
            <div class="pt-8 border-t border-gray-100">
                <div class="flex items-center gap-4 p-5 bg-gray-50 rounded-xl border border-gray-200 hover:bg-white hover:border-[#7DC2A5]/50 transition duration-200">
                    <div class="flex h-6 items-center">
                        <input type="checkbox" name="COU_UTILISE_PUCE" id="puce" value="1" {{ old('COU_UTILISE_PUCE') ? 'checked' : '' }} 
                               class="h-6 w-6 rounded border-gray-300 text-[#A67C52] focus:ring-[#A67C52] transition duration-150 ease-in-out cursor-pointer">
                    </div>
                    <div class="text-sm">
                        <label for="puce" class="font-bold text-gray-800 cursor-pointer select-none text-base">
                            Puces électroniques
                        </label>
                        <p class="text-gray-500 mt-1">Cochez cette case si cette course nécessite l'utilisation de puces électroniques pour le chronométrage.</p>
                    </div>
                </div>
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

    function displayErrors(errors) {
        if (errors.length > 0) {
            errorsEl.innerHTML = '<ul class="list-disc pl-5">' + errors.map(e => '<li>' + e + '</li>').join('') + '</ul>';
        } else {
            errorsEl.innerHTML = '';
        }
    }

    function validateAll() {
        const allErrors = [...validateDates(), ...validateMinMax(), ...validateAges()];
        displayErrors(allErrors);
        return allErrors;
    }

    // Event listeners
    courseStart.addEventListener('change', validateAll);
    courseEnd.addEventListener('change', validateAll);
    partMin.addEventListener('input', validateAll);
    partMax.addEventListener('input', validateAll);
    equMin.addEventListener('input', validateAll);
    equMax.addEventListener('input', validateAll);
    ageA.addEventListener('input', validateAll);
    ageB.addEventListener('input', validateAll);
    ageC.addEventListener('input', validateAll);

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
