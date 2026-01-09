@extends('layouts.app')

@section('title', 'À propos - L\'Embuscade')

@section('content')
<div class="min-h-screen w-full bg-[#f7f5e6] px-4 py-12 font-sans text-slate-800">
    
    {{-- Conteneur central limité en largeur pour la lecture --}}
    <div class="max-w-5xl mx-auto space-y-16">

        {{-- ========================
             1. EN-TÊTE ET INTRO
             ======================== --}}
        <header class="text-center space-y-6">
            {{-- Petit badge --}}
            <span class="inline-block bg-white px-4 py-2 rounded-full text-xs font-bold text-[#7DC2A5] shadow-sm border border-slate-100 uppercase tracking-wider mb-2">
                Projet BUT Informatique - SAE 3
            </span>
            <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 leading-tight">
                L'Embuscade :<br>Le défi sportif et numérique.
            </h1>
            <p class="text-lg text-slate-600 max-w-3xl mx-auto leading-relaxed">
                Un projet développé par une équipe d'étudiants de 2ème année (2A) pour digitaliser la gestion d'un événement unique à Caen.
            </p>
        </header>

        {{-- ========================
             2. SECTION PHOTO (GARDÉE MAIS RECONTEXTUALISÉE)
             ======================== --}}
        <section class="relative my-20">
            {{-- Effets de flou décoratifs --}}
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[110%] h-[110%] bg-gradient-to-tr from-[#7DC2A5]/30 via-transparent to-yellow-200/30 blur-[100px] -z-10 rounded-full opacity-70"></div>

            {{-- Cadre de la photo --}}
            <div class="bg-white p-3 md:p-4 rounded-[2.5rem] md:rounded-[3.5rem] shadow-2xl border-2 border-white relative z-10 transform hover:scale-[1.01] transition-transform duration-500 ease-out">
                <div class="rounded-[2rem] md:rounded-[3rem] overflow-hidden relative aspect-video bg-slate-200">
                    {{-- Placeholder image --}}
                    <img src="{{ asset('images/photo_groupe.jpg') }}" 
                        alt="L'équipe projet SAE 3" 
                        class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent pointer-events-none"></div>
                </div>
            </div>

            {{-- Légende --}}
            <div class="text-center mt-8 md:mt-12">
                <h2 class="text-3xl font-bold text-slate-900 mb-3">Une équipe de 2ème Année</h2>
                <div class="w-16 h-1 bg-[#7DC2A5] mx-auto rounded-full mb-4"></div>
                <p class="text-slate-600 text-lg max-w-2xl mx-auto">
                    Nous mettons en pratique nos compétences en développement web, base de données et gestion de projet pour répondre à un besoin réel.
                </p>
            </div>
        </section>

        {{-- ========================
             3. CONTEXTE ET CONCEPT
             ======================== --}}
        <section class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            {{-- Carte 1 : Le concept L'Embuscade --}}
            <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-md transition-all">
                <div class="absolute top-0 left-0 w-full h-3 bg-gradient-to-r from-yellow-400 to-orange-300"></div>
                
                <div class="w-14 h-14 bg-yellow-100 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                   {{-- Icône Cocktail/Sport --}}
                   <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <h3 class="text-2xl font-bold text-slate-900 mb-4">Pourquoi "L'Embuscade" ?</h3>
                <p class="text-slate-600 leading-relaxed text-lg">
                    C'est un clin d'œil à la culture caennaise. Tout comme le célèbre <strong>cocktail</strong> emblématique de la ville, notre événement est un mélange détonant. Mais ici, l'ivresse vient de l'effort : c'est une <strong>course à pied</strong> pleine de surprises où il faut savoir gérer son endurance pour ne pas tomber dans le piège.
                </p>
            </div>

            {{-- Carte 2 : Le but informatique --}}
            <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-md transition-all">
                <div class="absolute top-0 left-0 w-full h-3 bg-gradient-to-r from-[#7DC2A5] to-[#7DC2A5]/50"></div>
                
                <div class="w-14 h-14 bg-[#7DC2A5]/10 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    {{-- Icône Code --}}
                    <svg class="w-8 h-8 text-[#7DC2A5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                </div>
                <h3 class="text-2xl font-bold text-slate-900 mb-4">L'Objectif Informatique</h3>
                <p class="text-slate-600 leading-relaxed text-lg">
                    Dans le cadre de la SAE 3, notre mission est de concevoir une application web complète. De la gestion des inscriptions à l'affichage des résultats en temps réel, nous développons un outil performant pour faciliter la vie des organisateurs et des coureurs.
                </p>
            </div>
        </section>

        {{-- ========================
             4. L'ÉQUIPE (9 MEMBRES)
             ======================== --}}
        <section class="py-10">
            <h3 class="text-2xl font-bold text-slate-900 mb-8 text-center">Les développeurs du projet</h3>
            
            {{-- Grille de 9 personnes --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @php
                    $teamMembers = [
                        'Brillant Lukas', 'Delacoudre Willelm', 'Duhamel Mathéo',
                        'Heuze Raphael', 'Lenormand Alexandre', 'Mary Ilhan',
                        'Quesnel Lenny', 'Soulet Titouan', 'Violette Alex',
                    ];
                @endphp

                @foreach($teamMembers as $member)
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center space-x-4 hover:border-[#7DC2A5] transition-colors">
                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-[#7DC2A5] font-bold">
                        {{-- Initiale fictive --}}
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <span class="font-semibold text-slate-700">{{ $member }}</span>
                </div>
                @endforeach
            </div>
        </section>

        {{-- ========================
             5. APPEL À L'ACTION FINAL
             ======================== --}}
        <section class="text-center py-8 border-t border-slate-200/60">
            <div class="flex flex-col sm:flex-row justify-center items-center gap-4 mt-6">
                <a href="{{ route('home') }}" class="inline-flex items-center px-8 py-4 rounded-full bg-slate-900 text-white font-bold text-sm hover:bg-black transition-all hover:-translate-y-1 shadow-lg">
                    Retour à l'accueil
                </a>
            </div>
        </section>

    </div>
</div>
@endsection