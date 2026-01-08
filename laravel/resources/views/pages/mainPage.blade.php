@extends('layouts.app')

@section('title', 'Accueil - L\'Embuscade')

@section('content')
<div class="min-h-screen w-full bg-[#f7f5e6] px-4 py-8 font-sans text-slate-800">
    
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-4 gap-8">

        {{-- ========================
             COLONNE GAUCHE (FILTRES)
             ======================== --}}
        <aside class="lg:col-span-1">
            @auth
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sticky top-6">
                    <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#7DC2A5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        Filtrer les Raids
                    </h3>
                    
                    <form action="{{ route('home') }}" method="GET" class="space-y-5">
                        
                        {{-- Recherche --}}
                        <div>
                            <label for="search" class="block text-xs font-bold text-slate-500 uppercase mb-1">Recherche</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Nom du raid..." 
                                class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:border-[#7DC2A5] focus:ring-1 focus:ring-[#7DC2A5] transition-all">
                        </div>
                        
                        {{-- Select Club --}}
                        <div>
                            <label for="club" class="block text-xs font-bold text-slate-500 uppercase mb-1">Organisateur</label>
                            <select name="club" id="club" class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:border-[#7DC2A5] focus:ring-1 focus:ring-[#7DC2A5] transition-all cursor-pointer">
                                <option value="">Tous les clubs</option>
                                @foreach($clubs as $club)
                                    <option value="{{ $club->CLU_NUM }}" {{ request('club') == $club->CLU_NUM ? 'selected' : '' }}>
                                        {{ $club->CLU_NOM }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Dates (Radio) --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Période</label>
                            <div class="space-y-2">
                                <label class="flex items-center cursor-pointer group">
                                    <input type="radio" name="date_filter" value="" {{ request('date_filter') == '' ? 'checked' : '' }} class="w-4 h-4 text-[#7DC2A5] focus:ring-[#7DC2A5] border-gray-300">
                                    <span class="ml-2 text-sm text-slate-600 group-hover:text-slate-900">Tout afficher</span>
                                </label>
                                <label class="flex items-center cursor-pointer group">
                                    <input type="radio" name="date_filter" value="future" {{ request('date_filter') == 'future' ? 'checked' : '' }} class="w-4 h-4 text-[#7DC2A5] focus:ring-[#7DC2A5] border-gray-300">
                                    <span class="ml-2 text-sm text-slate-600 group-hover:text-slate-900">À venir</span>
                                </label>
                                <label class="flex items-center cursor-pointer group">
                                    <input type="radio" name="date_filter" value="past" {{ request('date_filter') == 'past' ? 'checked' : '' }} class="w-4 h-4 text-[#7DC2A5] focus:ring-[#7DC2A5] border-gray-300">
                                    <span class="ml-2 text-sm text-slate-600 group-hover:text-slate-900">Terminés</span>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="w-full rounded-xl bg-[#7DC2A5] px-4 py-3 text-sm font-bold text-white shadow-md hover:bg-[#68a88d] transition-all">
                            Appliquer
                        </button>
                        
                        @if(request()->hasAny(['search', 'club', 'date_filter']))
                            <a href="{{ route('home') }}" class="block text-center text-xs font-semibold text-slate-400 hover:text-slate-600 underline">
                                Réinitialiser
                            </a>
                        @endif
                    </form>
                </div>
            @else
                {{-- Placeholder vide pour garder l'alignement --}}
                <div class="hidden lg:block">&nbsp;</div>
            @endauth
        </aside>

        {{-- ========================
             COLONNE CENTRALE (LISTE)
             ======================== --}}
        <main class="lg:col-span-2 space-y-8">
            
            {{-- Intro (AVEC VOTRE TEXTE) --}}
            <section class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100 text-center relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-[#7DC2A5] to-yellow-200"></div>
                
                <h2 class="text-2xl font-extrabold text-slate-900 mb-3">Prêt pour l'aventure en Normandie ?</h2>
                
                <p class="text-slate-600 leading-relaxed mb-4">
                    Découvrez les prochains Raids et courses d'orientation organisés par Vik'Azim et ses clubs partenaires. Que ce soit pour un parcours compétitif ou Rando/Loisirs, trouvez le défi qui vous correspond.
                </p>
                
                <p class="text-slate-600 leading-relaxed mb-4">
                    <strong>Nouveau :</strong> Gérez vos inscriptions, composez vos équipes et suivez vos résultats directement depuis cette application. Créez un compte dès maintenant !
                </p>

                @guest
                    <div class="mt-6">
                        <a href="{{ route('register') }}" class="inline-block bg-slate-900 text-white px-6 py-3 rounded-full text-sm font-bold hover:bg-black transition-all shadow-lg hover:-translate-y-0.5">
                            Créer un compte pour s'inscrire
                        </a>
                    </div>
                @endguest
            </section>

            {{-- Liste des Raids --}}
            <section>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-slate-800">Événements disponibles</h2>
                    <span class="bg-white px-3 py-1 rounded-full text-xs font-bold text-slate-500 shadow-sm border border-slate-100">{{ $raids->count() }} trouvés</span>
                </div>
                
                @if($raids->isEmpty())
                    <div class="bg-white rounded-2xl p-10 text-center border border-slate-100 shadow-sm">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-50 text-slate-300 mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <p class="text-lg font-medium text-slate-600">Aucun raid trouvé.</p>
                        <p class="text-sm text-slate-400">Essayez de modifier vos filtres.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($raids as $raid)
                            <div class="group bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300 flex flex-col h-full">
                                
                                {{-- Image --}}
                                <div class="h-48 w-full overflow-hidden bg-slate-100 relative">
                                    @if($raid->RAID_ILLUSTRATION)
                                        <img src="{{ asset('images/' . $raid->RAID_ILLUSTRATION) }}" alt="{{ $raid->RAID_NOM }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    @else
                                        <div class="flex items-center justify-center h-full text-slate-300">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    @endif
                                    
                                    {{-- Badge Date (Superposé) --}}
                                    <div class="absolute top-3 right-3 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-lg text-xs font-bold text-slate-800 shadow-sm">
                                        {{ \Carbon\Carbon::parse($raid->RAID_DATE_DEBUT)->format('d M') }}
                                    </div>
                                </div>

                                {{-- Contenu --}}
                                <div class="p-5 flex-grow flex flex-col">
                                    <h3 class="text-lg font-bold text-slate-900 mb-2 line-clamp-1 group-hover:text-[#7DC2A5] transition-colors">
                                        {{ $raid->RAID_NOM }}
                                    </h3>
                                    
                                    <div class="text-xs text-slate-500 mb-4 flex items-center gap-4">
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            {{ \Carbon\Carbon::parse($raid->RAID_DATE_DEBUT)->format('d/m/Y') }}
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                            {{ $raid->courses()->count() }} course(s)
                                        </div>
                                    </div>
                                    
                                    <div class="mt-auto pt-4 border-t border-slate-50 flex justify-between items-center">
                                        <div class="text-xs text-slate-400 truncate max-w-[50%]">
                                            {{ $raid->RAID_CONTACT }}
                                        </div>
                                        <a href="{{ route('raid.show', $raid->RAID_NUM) }}" class="inline-flex items-center text-xs font-bold text-[#7DC2A5] hover:text-[#5fa388] transition-colors">
                                            Voir les détails <span class="ml-1">&rarr;</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </main>

        {{-- ========================
             COLONNE DROITE (VIDE)
             ======================== --}}
        <aside class="hidden lg:block lg:col-span-1">
            {{-- Espace vide pour l'équilibre --}}
        </aside>

    </div>
</div>
@endsection