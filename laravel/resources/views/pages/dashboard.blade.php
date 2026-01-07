@extends('layouts.app')

@section('title', 'Espace Gestion Club - L\'Embuscade')

@section('content')
{{-- Fond beige remis ici --}}
<div class="min-h-screen w-full bg-[#f7f5e6] px-4 py-8 lg:py-12">
    <div class="max-w-7xl mx-auto">

        {{-- En-tête global --}}
        <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-black">Espace Organisateur</h1>
                @if(isset($club))
                    <p class="text-slate-600 mt-1 font-medium">
                        Gestion du club : <span class="font-bold text-black">{{ $club->CLU_NOM }}</span>
                    </p>
                @endif
            </div>
            
            @if($managesClub)
                <a href="{{ route('raids.create') }}" 
                   class="inline-flex items-center justify-center rounded-xl bg-[#7DC2A5] px-6 py-3 text-sm font-bold text-black shadow-sm hover:brightness-95 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Créer un nouveau Raid
                </a>
            @endif
        </div>

        @if(!$managesClub)
            <div class="rounded-2xl border border-black/5 bg-white/60 p-10 text-center shadow-sm">
                <div class="mx-auto w-16 h-16 bg-white rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-black">Accès restreint</h3>
                <p class="text-slate-600 mt-2 max-w-md mx-auto">Vous devez être gérant d'un club pour accéder à cet espace.</p>
            </div>
        @else
            {{-- Grille principale --}}
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 items-start">
                
                {{-- COLONNE GAUCHE (3/4) : Liste des membres --}}
                <div class="lg:col-span-3 space-y-6">
                    
                    {{-- Carte Membres --}}
                    <div class="rounded-2xl border border-black/5 bg-white shadow-sm overflow-hidden">
                        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                            <h2 class="text-xl font-bold text-black">Membres du Club</h2>
                            <span class="bg-[#f7f5e6] text-black/70 py-1 px-3 rounded-full text-xs font-bold border border-black/5">
                                {{ isset($clubMembers) ? count($clubMembers) : 0 }} inscrits
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-slate-600">
                                <thead class="bg-gray-50 text-xs uppercase text-slate-500">
                                    <tr>
                                        <th class="px-6 py-4 font-semibold">Adhérent</th>
                                        <th class="px-6 py-4 font-semibold">Licence</th>
                                        <th class="px-6 py-4 font-semibold">Contact</th>
                                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($clubMembers ?? [] as $member)
                                        {{-- Vérification si c'est l'utilisateur connecté --}}
                                        @php $isMe = ($member->INS_ID == Auth::id()); @endphp

                                        <tr class="{{ $isMe ? 'bg-[#7DC2A5]/10' : 'hover:bg-gray-50 transition-colors' }}">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="h-10 w-10 rounded-full {{ $isMe ? 'bg-[#7DC2A5] text-black' : 'bg-gray-200 text-gray-600' }} flex items-center justify-center font-bold text-xs">
                                                        {{ substr($member->INS_PRENOM, 0, 1) }}{{ substr($member->INS_NOM, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <div class="font-bold text-black flex items-center gap-2">
                                                            {{ $member->INS_PRENOM }} {{ $member->INS_NOM }}
                                                            @if($isMe)
                                                                <span class="px-2 py-0.5 rounded text-[10px] bg-[#7DC2A5] text-black font-bold uppercase">Moi</span>
                                                            @endif
                                                        </div>
                                                        <div class="text-xs text-slate-500">Né(e) le {{ \Carbon\Carbon::parse($member->INS_NAISSANCE)->format('d/m/Y') }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($member->INS_NUM_LICENCE)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                                        {{ $member->INS_NUM_LICENCE }}
                                                    </span>
                                                @else
                                                    <span class="text-slate-400 italic text-xs">Non renseignée</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex flex-col">
                                                    <span class="font-medium text-black">{{ $member->INS_MAIL }}</span>
                                                    <span class="text-xs text-slate-500">{{ $member->INS_TEL }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <a href="#" class="text-slate-400 hover:text-black font-medium text-xs underline">Détails</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-10 text-center text-slate-500">
                                                Aucun membre trouvé dans ce club pour le moment.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Liste des Raids (Optionnelle, pour avoir l'info complète) --}}
                    <div class="mt-8">
                        <h3 class="text-lg font-bold text-black mb-4">Raids gérés</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($raids ?? [] as $raid)
                                <div class="bg-white p-4 rounded-xl border border-black/5 shadow-sm hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-bold text-black">{{ $raid->RAID_NOM }}</h4>
                                            <p class="text-xs text-slate-500 mt-1">
                                                {{ \Carbon\Carbon::parse($raid->RAID_DATE_DEBUT)->format('d M Y') }}
                                            </p>
                                        </div>
                                        <a href="{{ route('raid.show', $raid->RAID_NUM) }}" class="text-xs bg-black text-white px-3 py-1 rounded-md font-semibold hover:bg-gray-800">Voir</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>

                {{-- COLONNE DROITE (1/4) : Panel Stats --}}
                <div class="lg:col-span-1 space-y-6 lg:sticky lg:top-6">
                    
                    <div class="rounded-2xl bg-black text-white p-6 shadow-lg">
                        <h3 class="text-lg font-bold mb-6 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-[#7DC2A5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            Performances
                        </h3>
                        
                        <div class="space-y-6">
                            <div>
                                <p class="text-gray-400 text-xs uppercase tracking-wider font-semibold">Raids Organisés</p>
                                <p class="text-4xl font-extrabold mt-1 text-[#7DC2A5]">{{ isset($raids) ? count($raids) : 0 }}</p>
                            </div>
                            
                            <div class="w-full h-px bg-gray-800"></div>

                            <div>
                                <p class="text-gray-400 text-xs uppercase tracking-wider font-semibold">Total Coureurs Club</p>
                                <p class="text-4xl font-extrabold mt-1 text-white">{{ isset($clubMembers) ? count($clubMembers) : 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-black/5 bg-white shadow-sm p-5">
                        <h3 class="text-sm font-bold text-black uppercase mb-4 tracking-wide text-center">Inscrits par Raid</h3>
                        
                        <div class="space-y-4">
                            @forelse($statsRaids ?? [] as $stat)
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="font-medium text-slate-700 truncate w-2/3" title="{{ $stat->nom_raid }}">{{ $stat->nom_raid }}</span>
                                        <span class="font-bold text-black">{{ $stat->nb_inscrits }} <span class="text-xs font-normal text-slate-400">part.</span></span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2">
                                        <div class="bg-[#7DC2A5] h-2 rounded-full" style="width: {{ min(($stat->nb_inscrits / 50) * 100, 100) }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <p class="text-xs text-slate-400">Aucune donnée récente.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>
        @endif
    </div>
</div>
@endsection