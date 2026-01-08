@extends('layouts.app')

@section('title', 'Gestion : ' . $race->COU_NOM . " - L'Embuscade")

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">
    
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <a href="{{ route('race.organizer_index') }}" class="text-sm text-gray-500 hover:underline">← Retour à mes courses</a>
            <h1 class="text-3xl font-bold mt-1 text-black">Gestion : {{ $race->COU_NOM }}</h1>
            <div class="text-sm text-gray-600">
                Responsable de la course : <strong>{{ Auth::user()->INS_PRENOM }} {{ Auth::user()->INS_NOM }}</strong>
            </div>
        </div>
        
        <div class="flex gap-2">
             {{-- Fast actions --}}
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    {{-- List of team --}}
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800">
                Équipes inscrites <span class="bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded-full ml-2">{{ $race->equipes->count() }}</span>
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-100 text-gray-600 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="p-4 border-b">N°</th>
                        <th class="p-4 border-b">Équipe & Responsable</th>
                        <th class="p-4 border-b">Membres (Participants)</th>
                        <th class="p-4 border-b text-center">Statut Paiement</th>
                        <th class="p-4 border-b text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse($race->equipes as $equipe)
                        <tr class="hover:bg-gray-50 transition-colors">
                            {{-- Number --}}
                            <td class="p-4 font-mono font-bold text-gray-400 align-top">
                                #{{ $equipe->EQU_NUM }}
                            </td>
                            
                            {{-- Team Name + Responsible --}}
                            <td class="p-4 align-top">
                                <div class="font-bold text-gray-800 text-base">
                                    {{ $equipe->EQU_NOM }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Resp : 
                                    <span class="font-semibold">
                                        {{ optional($equipe->createur)->INS_PRENOM }} {{ optional($equipe->createur)->INS_NOM }}
                                    </span>
                                </div>
                                <div class="text-[10px] text-gray-400">
                                    {{ optional($equipe->createur)->INS_MAIL }}
                                </div>
                            </td>
                            
                            {{-- Members --}}
                            <td class="p-4 align-top">
                                @if($equipe->participations->isEmpty())
                                    <span class="text-gray-400 italic">Aucun membre</span>
                                @else
                                    <ul class="space-y-1">
                                        @foreach($equipe->participations as $part)
                                            <li class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                <span class="font-medium">
                                                    {{ optional($part->user)->INS_PRENOM }} {{ optional($part->user)->INS_NOM }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                            
                            {{-- Status --}}
                            <td class="p-4 text-center align-top">
                                @if($equipe->EQU_PAIEMENT_VALIDE)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✅ PAYÉ
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        ⏳ EN ATTENTE
                                    </span>
                                @endif
                            </td>
                            
                            {{-- Actions --}}
                            <td class="p-4 text-center align-top">
                                <div class="flex flex-col items-center gap-2">
                                    {{-- Paiement Button --}}
                                    <form action="{{ route('race.team.payment', ['cou_num' => $race->COU_NUM, 'equ_num' => $equipe->EQU_NUM]) }}" method="POST">
                                        @csrf
                                        @if($equipe->EQU_PAIEMENT_VALIDE)
                                            <button type="submit" class="text-xs text-orange-600 hover:text-orange-800 font-semibold underline decoration-dotted" title="Invalider le paiement">
                                                Invalider paiement
                                            </button>
                                        @else
                                            <button type="submit" class="bg-[#7DC2A5] hover:bg-[#6ab394] text-white px-3 py-1.5 rounded shadow-sm text-xs font-bold transition w-full whitespace-nowrap">
                                                Valider Paiement
                                            </button>
                                        @endif
                                    </form>

                                    {{-- Delete Button --}}
                                    <form action="{{ route('race.team.delete', ['cou_num' => $race->COU_NUM, 'equ_num' => $equipe->EQU_NUM]) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer définitivement l\'équipe {{ $equipe->EQU_NOM }} ? Cette action est irréversible.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="flex items-center gap-1 text-xs text-red-500 hover:text-red-700 hover:bg-red-50 px-2 py-1 rounded transition mt-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-500">
                                Aucune équipe n'est inscrite à cette course pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection