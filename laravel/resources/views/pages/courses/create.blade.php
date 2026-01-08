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

        <form method="POST" action="{{ route('race.store', $raid->RAID_NUM) }}" class="space-y-10">
            @csrf

            {{-- SECTION 1: GENERAL INFORMATION --}}
            <div>
                <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                    <span class="bg-[#7DC2A5] text-white w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shadow-sm">1</span>
                    Informations Générales
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    {{-- Race Name --}}
                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Nom de la course *</label>
                        <input name="COU_NOM" value="{{ old('COU_NOM') }}" placeholder="Ex: Sprint Urbain Nocturne"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-900 py-3 px-4 shadow-sm focus:border-[#7DC2A5] focus:bg-white focus:ring-2 focus:ring-[#7DC2A5] focus:ring-opacity-50 transition duration-200" required />
                    </div>

                    {{-- Race Type --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Type de course *</label>
                        <div class="relative">
                            <select name="TYP_NUM" class="appearance-none w-full rounded-lg border-gray-300 bg-gray-50 text-gray-900 py-3 px-4 shadow-sm focus:border-[#7DC2A5] focus:bg-white focus:ring-2 focus:ring-[#7DC2A5] focus:ring-opacity-50 transition duration-200">
                                <option value="1" {{ old('TYP_NUM') == 1 ? 'selected' : '' }}>Compétitif</option>
                                <option value="2" {{ old('TYP_NUM') == 2 ? 'selected' : '' }}>Rando / Loisirs</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Responsible Person --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Responsable (Adhérent) *</label>
                        <div class="relative">
                            <select name="INS_ID" class="appearance-none w-full rounded-lg border-gray-300 bg-gray-50 text-gray-900 py-3 px-4 shadow-sm focus:border-[#7DC2A5] focus:bg-white focus:ring-2 focus:ring-[#7DC2A5] focus:ring-opacity-50 transition duration-200">
                                @foreach($responsibles as $r)
                                    <option value="{{ $r->INS_ID }}" {{ old('INS_ID') == $r->INS_ID ? 'selected' : '' }}>
                                        {{ $r->INS_PRENOM }} {{ $r->INS_NOM }} ({{ $r->INS_NUM_LICENCE ?? 'PPS' }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Start Date --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Date et heure de départ *</label>
                        <input name="COU_DATE_DEPART" type="datetime-local" value="{{ old('COU_DATE_DEPART') }}" 
                               class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-900 py-3 px-4 shadow-sm focus:border-[#7DC2A5] focus:bg-white focus:ring-2 focus:ring-[#7DC2A5] focus:ring-opacity-50 transition duration-200" required />
                    </div>

                    {{-- Duration --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Durée (minutes) *</label>
                        <input name="COU_DUREE" type="number" min="1" value="{{ old('COU_DUREE', 60) }}" 
                               class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-900 py-3 px-4 shadow-sm focus:border-[#7DC2A5] focus:bg-white focus:ring-2 focus:ring-[#7DC2A5] focus:ring-opacity-50 transition duration-200" required />
                        <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            La date de fin sera calculée automatiquement.
                        </p>
                    </div>

                    {{-- Difficulty (Text) --}}
                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Difficulté (Texte) *</label>
                        <input name="COU_DIFFICULTE" type="text" value="{{ old('COU_DIFFICULTE') }}" placeholder="Ex: Débutant, Confirmé, Expert, Licorne..."
                               class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-900 py-3 px-4 shadow-sm focus:border-[#7DC2A5] focus:bg-white focus:ring-2 focus:ring-[#7DC2A5] focus:ring-opacity-50 transition duration-200" required />
                    </div>
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
@endsection