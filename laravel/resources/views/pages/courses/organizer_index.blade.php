@extends('layouts.app')

@section('title', "Mes Courses Responsable - L'Embuscade")

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Espace Responsable de Course</h1>
        
        <form method="GET" class="flex items-center gap-2">
            <label for="year" class="text-sm font-semibold">Année :</label>
            <select name="year" id="year" onchange="this.form.submit()" class="rounded-md border-gray-300 py-1 pl-3 pr-8 text-sm focus:ring-[#A67C52] focus:border-[#A67C52]">
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- SECTION 1: Races Waiting for Action --}}
    @if($racesWaitingForResults->isNotEmpty())
        <div class="mb-12">
            <h2 class="text-xl font-bold text-orange-600 border-b border-orange-200 pb-2 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                En attente de résultats / validation
            </h2>
            <div class="bg-white rounded-lg shadow overflow-hidden border-l-4 border-orange-500">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500">
                        <tr>
                            <th class="py-3 px-4 text-left">Date</th>
                            <th class="py-3 px-4 text-left">Nom</th>
                            <th class="py-3 px-4 text-left">Raid</th>
                            <th class="py-3 px-4 text-center">État Dossiers</th>
                            <th class="py-3 px-4 text-center">État Résultats</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($racesWaitingForResults as $race)
                            <tr class="hover:bg-orange-50">
                                <td class="py-3 px-4">{{ $race->COU_DATE_DEPART->format('d/m/Y') }}</td>
                                <td class="py-3 px-4 font-bold">{{ $race->COU_NOM }}</td>
                                <td class="py-3 px-4 text-gray-500">{{ optional($race->raid)->RAID_NOM }}</td>
                                
                                {{-- Documents Status (No blinking) --}}
                                <td class="py-3 px-4 text-center">
                                    @if($race->documents_complete)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                            ✅ Complets
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                            ⚠️ Incomplets
                                        </span>
                                    @endif
                                </td>

                                {{-- Results Status --}}
                                <td class="py-3 px-4 text-center">
                                    @if($race->results_complete)
                                        <span class="text-green-600 font-bold text-xs">✅ Publiés</span>
                                    @else
                                        <span class="text-orange-600 font-bold text-xs">⚠️ Manquants</span>
                                    @endif
                                </td>

                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('race.manage', $race->COU_NUM) }}" class="inline-flex items-center gap-1 text-orange-700 font-bold hover:bg-orange-200 bg-orange-100 px-3 py-1.5 rounded transition">
                                        Gérer & Résultats
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- SECTION 2: Upcoming Races --}}
    <div class="mb-12">
        <h2 class="text-xl font-bold text-[#A67C52] border-b border-gray-200 pb-2 mb-4">
            Courses à venir
        </h2>
        
        @if($upcomingRaces->isEmpty())
            <div class="bg-gray-50 p-4 rounded text-gray-500 italic border border-gray-200">Aucune course à venir sous votre responsabilité.</div>
        @else
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach($upcomingRaces as $race)
                    <div class="bg-white rounded-lg shadow-md border border-gray-100 flex flex-col justify-between overflow-hidden group hover:shadow-lg transition-shadow duration-300">
                        
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-2">
                                <div class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                    Raid : {{ optional($race->raid)->RAID_NOM }}
                                </div>
                                {{-- Documents Badge (No blinking) --}}
                                @if($race->documents_complete)
                                    <span class="text-[10px] bg-green-100 text-green-800 px-2 py-0.5 rounded-full font-bold">✅ Dossiers OK</span>
                                @else
                                    <span class="text-[10px] bg-red-100 text-red-800 px-2 py-0.5 rounded-full font-bold">⚠️ Dossiers Manquants</span>
                                @endif
                            </div>

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
                                    <span>👥</span> Inscrits : {{ $race->equipes->count() }} équipes
                                </p>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 border-t border-gray-100 flex flex-col gap-2">
                            <div class="grid grid-cols-2 gap-2">
                                <a href="{{ route('race.edit', $race->COU_NUM) }}" 
                                   class="flex items-center justify-center gap-1 w-full text-center bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 font-semibold py-2 rounded transition text-sm">
                                    ⚙️ Paramètres
                                </a>
                                <a href="{{ route('race.manage', $race->COU_NUM) }}" 
                                   class="flex items-center justify-center gap-1 w-full text-center bg-[#7DC2A5] hover:bg-[#6ab394] text-white font-bold py-2 rounded transition text-sm">
                                    📋 Gérer
                                </a>
                            </div>
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

    {{-- SECTION 3: History (Completed) --}}
    <div>
        <h2 class="text-xl font-bold text-gray-500 border-b border-gray-200 pb-2 mb-4">Historique des courses (Terminées & Complètes)</h2>
        @if($racesWithResults->isEmpty())
            <p class="text-gray-500 italic">Aucun historique pour cette période.</p>
        @else
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-3 px-4 text-left font-semibold">Date</th>
                            <th class="py-3 px-4 text-left font-semibold">Nom</th>
                            <th class="py-3 px-4 text-left font-semibold">Raid</th>
                            <th class="py-3 px-4 text-center font-semibold">État Dossiers</th>
                            <th class="py-3 px-4 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($racesWithResults as $race)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4">{{ $race->COU_DATE_DEPART->format('d/m/Y') }}</td>
                                <td class="py-3 px-4 font-medium">
                                    <a href="{{ route('race.show', $race->COU_NUM) }}" class="hover:text-[#A67C52] hover:underline" target="_blank">
                                        {{ $race->COU_NOM }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 text-gray-500">{{ optional($race->raid)->RAID_NOM }}</td>
                                
                                {{-- Dossier Check --}}
                                <td class="py-3 px-4 text-center">
                                    <span class="text-green-600 font-bold text-xs">✅ Complets</span>
                                </td>

                                <td class="py-3 px-4 text-right space-x-2">
                                    <a href="{{ route('race.show', $race->COU_NUM) }}" target="_blank" class="text-gray-500 hover:text-black text-xs border border-gray-300 px-2 py-1 rounded">
                                        Voir public
                                    </a>
                                    <a href="{{ route('race.manage', $race->COU_NUM) }}" class="text-[#A67C52] font-bold hover:text-[#6B5033] bg-[#A67C52]/10 px-2 py-1 rounded text-xs">
                                        Gérer
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