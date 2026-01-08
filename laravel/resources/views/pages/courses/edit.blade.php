@extends('layouts.app')

@section('title', 'Modifier : ' . $race->COU_NOM . " - L'Embuscade")

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">Modifier la course</h1>
        <a href="{{ route('race.organizer_index') }}" class="text-gray-500 hover:text-black hover:underline transition-colors">
            ← Annuler et retour
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="bg-[#7DC2A5] px-6 py-4 border-b border-gray-200">
            <h2 class="text-white font-bold text-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Paramètres de {{ $race->COU_NOM }}
            </h2>
        </div>

        <form action="{{ route('race.update', $race->COU_NUM) }}" method="POST" class="p-8 space-y-8">
            @csrf
            @method('PUT')

            {{-- General Information --}}
            <div>
                <h3 class="text-lg font-bold text-[#A67C52] border-b pb-2 mb-6">Informations Générales</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nom de la course</label>
                        <input type="text" name="COU_NOM" value="{{ old('COU_NOM', $race->COU_NOM) }}" 
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#7DC2A5] focus:ring focus:ring-[#7DC2A5] focus:ring-opacity-50 py-3 px-4 text-lg">
                        @error('COU_NOM') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Date de départ</label>
                        <input type="datetime-local" name="COU_DATE_DEPART" value="{{ old('COU_DATE_DEPART', $race->COU_DATE_DEPART->format('Y-m-d\TH:i')) }}" 
                               class="w-full rounded-md border-gray-300 shadow-sm py-2 px-3 focus:border-[#7DC2A5] focus:ring focus:ring-[#7DC2A5] focus:ring-opacity-50">
                        @error('COU_DATE_DEPART') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Date de fin</label>
                        <input type="datetime-local" 
                            name="COU_DATE_FIN" 
                            value="{{ old('COU_DATE_FIN', $race->COU_DATE_FIN->format('Y-m-d\TH:i')) }}" 
                               class="w-full rounded-md border-gray-300 shadow-sm py-2 px-3 focus:border-[#7DC2A5] focus:ring focus:ring-[#7DC2A5] focus:ring-opacity-50">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Durée (minutes) <span class="text-red-600">*</span></label>
                        <input type="number" name="COU_DUREE" min="1" value="{{ old('COU_DUREE', $race->COU_DUREE) }}" required
                               class="w-full rounded-md border-gray-300 shadow-sm py-2 px-3 focus:border-[#7DC2A5] focus:ring focus:ring-[#7DC2A5] focus:ring-opacity-50">
                    </div>

                    {{-- Difficulty --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Niveau de difficulté</label>
                        <input type="text" name="COU_DIFFICULTE" value="{{ old('COU_DIFFICULTE', $race->COU_DIFFICULTE) }}" 
                               placeholder="Ex: Débutant, Confirmé, Licorne..."
                               class="w-full rounded-md border-gray-300 shadow-sm py-2 px-3 focus:border-[#7DC2A5] focus:ring focus:ring-[#7DC2A5] focus:ring-opacity-50">
                        <p class="text-xs text-gray-500 mt-1">Indiquez le niveau requis (texte libre).</p>
                    </div>
                </div>
            </div>

            {{-- Prices --}}
            <div>
                <h3 class="text-lg font-bold text-[#A67C52] border-b pb-2 mb-6">Tarifs & Options</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Prix Repas (€)</label>
                        <div class="relative rounded-md shadow-sm">
                            <input type="number" step="0.01" name="COU_PRIX_REPAS" value="{{ old('COU_PRIX_REPAS', $race->COU_PRIX_REPAS) }}" 
                                   class="w-full rounded-md border-gray-300 py-2 pl-3 pr-12 focus:border-[#7DC2A5] focus:ring focus:ring-[#7DC2A5] focus:ring-opacity-50">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">EUR</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Réduction Licencié (€)</label>
                        <div class="relative rounded-md shadow-sm">
                            <input type="number" step="0.01" name="COU_REDUC_LICENCIE" value="{{ old('COU_REDUC_LICENCIE', $race->COU_REDUC_LICENCIE) }}" 
                                   class="w-full rounded-md border-gray-300 py-2 pl-3 pr-12 focus:border-[#7DC2A5] focus:ring focus:ring-[#7DC2A5] focus:ring-opacity-50">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">EUR</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gauges --}}
            <div>
                <h3 class="text-lg font-bold text-[#A67C52] border-b pb-2 mb-6">Tranches d'âge</h3>
                
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
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Âge A</label>
                        <input type="number" name="COU_AGE_A" value="{{ old('COU_AGE_A', $race->COU_AGE_A) }}" 
                               min="0" max="100"
                               class="w-full rounded-md border-gray-300 shadow-sm py-2 px-3 focus:border-[#7DC2A5] focus:ring focus:ring-[#7DC2A5] focus:ring-opacity-50">
                        <p class="text-xs text-gray-500 mt-1">Âge minimum de tous</p>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Âge B</label>
                        <input type="number" name="COU_AGE_B" value="{{ old('COU_AGE_B', $race->COU_AGE_B) }}" 
                               min="0" max="100"
                               class="w-full rounded-md border-gray-300 shadow-sm py-2 px-3 focus:border-[#7DC2A5] focus:ring focus:ring-[#7DC2A5] focus:ring-opacity-50">
                        <p class="text-xs text-gray-500 mt-1">Âge intermédiaire</p>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Âge C</label>
                        <input type="number" name="COU_AGE_C" value="{{ old('COU_AGE_C', $race->COU_AGE_C) }}" 
                               min="0" max="100"
                               class="w-full rounded-md border-gray-300 shadow-sm py-2 px-3 focus:border-[#7DC2A5] focus:ring focus:ring-[#7DC2A5] focus:ring-opacity-50">
                        <p class="text-xs text-gray-500 mt-1">Âge maximum (référence)</p>
                    </div>
                </div>
            </div>

            {{-- 4. Jauges --}}
            <div>
                <h3 class="text-lg font-bold text-[#A67C52] border-b pb-2 mb-6">Jauges & Équipes</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    {{-- Participants --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Min. Participants (Total)</label>
                        <input type="number" name="COU_NB_PART_MIN" value="{{ old('COU_NB_PART_MIN', $race->COU_NB_PART_MIN) }}" 
                               class="w-full rounded-md border-gray-300 shadow-sm py-2 px-3 focus:border-[#7DC2A5] focus:ring focus:ring-[#7DC2A5] focus:ring-opacity-50">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Max. Participants (Total)</label>
                        <input type="number" name="COU_NB_PART_MAX" value="{{ old('COU_NB_PART_MAX', $race->COU_NB_PART_MAX) }}" 
                               class="w-full rounded-md border-gray-300 shadow-sm py-2 px-3 focus:border-[#7DC2A5] focus:ring focus:ring-[#7DC2A5] focus:ring-opacity-50">
                    </div>
                    
                    {{-- Teams --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Max. Pers / Équipe</label>
                        <input type="number" name="COU_PART_PAR_EQU_MAX" value="{{ old('COU_PART_PAR_EQU_MAX', $race->COU_PART_PAR_EQU_MAX) }}" 
                               class="w-full rounded-md border-gray-300 shadow-sm py-2 px-3 focus:border-[#7DC2A5] focus:ring focus:ring-[#7DC2A5] focus:ring-opacity-50">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Min. Équipes</label>
                        <input type="number" name="COU_NB_EQU_MIN" value="{{ old('COU_NB_EQU_MIN', $race->COU_NB_EQU_MIN) }}" 
                               class="w-full rounded-md border-gray-300 shadow-sm py-2 px-3 focus:border-[#7DC2A5] focus:ring focus:ring-[#7DC2A5] focus:ring-opacity-50">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Max. Équipes</label>
                        <input type="number" name="COU_NB_EQU_MAX" value="{{ old('COU_NB_EQU_MAX', $race->COU_NB_EQU_MAX) }}" 
                               class="w-full rounded-md border-gray-300 shadow-sm py-2 px-3 focus:border-[#7DC2A5] focus:ring focus:ring-[#7DC2A5] focus:ring-opacity-50">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-100">
                <a href="{{ route('race.organizer_index') }}" class="text-gray-600 hover:text-gray-900 font-medium px-4 py-2 rounded hover:bg-gray-100 transition">
                    Annuler
                </a>
                <button type="submit" class="cursor-pointer bg-[#A67C52] hover:bg-[#8e6a46] text-white font-bold py-3 px-8 rounded-md shadow-lg transform hover:-translate-y-0.5 transition duration-200">
                    Enregistrer les modifications
                </button>
            </div>

        </form>
    </div>
</div>
@endsection