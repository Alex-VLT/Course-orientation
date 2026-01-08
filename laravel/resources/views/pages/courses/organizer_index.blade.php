@extends('layouts.app')

@section('title', 'Mes Courses Responsable')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Espace Responsable de Course</h1>
        {{-- Si tu veux ajouter un bouton "Créer une course" ici plus tard --}}
    </div>

    {{-- Courses à venir --}}
    <div class="mb-12">
        <h2 class="text-xl font-bold text-[#A67C52] border-b border-gray-200 pb-2 mb-4">
            Courses à venir
        </h2>
        
        @if($upcomingRaces->isEmpty())
            <div class="bg-gray-50 p-4 rounded text-gray-500 italic">Aucune course à venir sous votre responsabilité.</div>
        @else
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach($upcomingRaces as $race)
                    <div class="bg-white rounded-lg shadow-md border border-gray-100 flex flex-col justify-between overflow-hidden group hover:shadow-lg transition-shadow duration-300">
                        
                        <div class="p-6">
                            <div class="flex justify-between items-start">
                                <div class="text-xs font-bold uppercase tracking-wide text-gray-400 mb-1">
                                    Raid : {{ optional($race->raid)->RAID_NOM }}
                                </div>
                            </div>

                            {{-- Titre cliquable vers la page publique --}}
                            <a href="{{ route('race.show', $race->COU_NUM) }}" class="block group-hover:text-[#A67C52] transition-colors">
                                <h3 class="text-xl font-bold text-black mb-3">
                                    {{ $race->COU_NOM }}
                                </h3>
                            </a>
                            
                            <div class="text-gray-600 space-y-2 text-sm">
                                <p class="flex items-center gap-2">
                                    <span>📅</span> {{ $race->COU_DATE_DEPART->format('d/m/Y H:i') }}
                                </p>
                                <p class="flex items-center gap-2">
                                    <span>📍</span> Difficulté : {{ $race->COU_DIFFICULTE }}
                                </p>
                                <p class="flex items-center gap-2">
                                    <span>👥</span> Inscrits : {{ $race->equipes->count() }} équipes
                                </p>
                            </div>
                        </div>

                        {{-- Zone d'actions (Boutons) --}}
                        <div class="bg-gray-50 p-4 border-t border-gray-100 flex flex-col gap-2">
                            <div class="grid grid-cols-2 gap-2">
                                {{-- Bouton MODIFIER --}}
                                <a href="{{ route('race.edit', $race->COU_NUM) }}" 
                                   class="flex items-center justify-center gap-1 w-full text-center bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 font-semibold py-2 rounded transition text-sm">
                                    ⚙️ Paramètres
                                </a>
                                
                                {{-- Bouton GÉRER --}}
                                <a href="{{ route('race.manage', $race->COU_NUM) }}" 
                                   class="flex items-center justify-center gap-1 w-full text-center bg-[#7DC2A5] hover:bg-[#6ab394] text-white font-bold py-2 rounded transition text-sm">
                                    📋 Gérer
                                </a>
                            </div>
                            
                            {{-- Bouton VOIR PAGE PUBLIQUE (Nouveau) --}}
                            <a href="{{ route('race.show', $race->COU_NUM) }}" 
                               target="_blank"
                               class="flex items-center justify-center gap-1 w-full text-center text-xs text-gray-500 hover:text-black hover:underline transition mt-1">
                                👁️ Voir la page publique
                            </a>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Courses passées --}}
    <div>
        <h2 class="text-xl font-bold text-gray-500 border-b border-gray-200 pb-2 mb-4">Historique des courses</h2>
        @if($pastRaces->isEmpty())
            <p class="text-gray-500 italic">Aucun historique.</p>
        @else
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-3 px-4 text-left font-semibold">Date</th>
                            <th class="py-3 px-4 text-left font-semibold">Nom</th>
                            <th class="py-3 px-4 text-left font-semibold">Raid</th>
                            <th class="py-3 px-4 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($pastRaces as $race)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4">{{ $race->COU_DATE_DEPART->format('d/m/Y') }}</td>
                                <td class="py-3 px-4 font-medium">
                                    <a href="{{ route('race.show', $race->COU_NUM) }}" class="hover:text-[#A67C52] hover:underline" target="_blank">
                                        {{ $race->COU_NOM }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 text-gray-500">{{ optional($race->raid)->RAID_NOM }}</td>
                                <td class="py-3 px-4 text-right space-x-3">
                                    <a href="{{ route('race.show', $race->COU_NUM) }}" target="_blank" class="text-gray-500 hover:text-black" title="Voir la page publique">
                                        👁️ Voir la page publique
                                    </a>
                                    <a href="{{ route('race.edit', $race->COU_NUM) }}" class="text-gray-500 hover:text-black">
                                        ✏️ Modifier
                                    </a>
                                    <a href="{{ route('race.manage', $race->COU_NUM) }}" class="text-[#A67C52] font-bold hover:text-[#6B5033]">
                                        📋 Gérer
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection