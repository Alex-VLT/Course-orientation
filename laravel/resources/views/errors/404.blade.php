@extends('layouts.app')

@section('title', 'Page introuvable - L\'Embuscade')

@section('content')
{{-- Conteneur Principal : Fond Beige + Blobs + Centrage --}}
<div class="h-screen w-full flex items-center justify-center bg-[#f7f5e6] px-4 relative overflow-hidden font-sans text-slate-800">

    {{-- Formes décoratives d'arrière-plan (Identiques à ta page edit) --}}
    <div class="absolute top-[-10%] left-[-10%] w-[40rem] h-[40rem] bg-[#7DC2A5] rounded-full mix-blend-multiply filter blur-[100px] opacity-20 animate-blob"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40rem] h-[40rem] bg-yellow-200 rounded-full mix-blend-multiply filter blur-[100px] opacity-30 animate-blob animation-delay-2000"></div>

    {{-- CARTE CENTRALE --}}
    <div class="w-full max-w-lg bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl border border-white/50 flex flex-col items-center text-center p-10 md:p-12 relative z-10">
        
        {{-- Icône : Boussole / Carte --}}
        <div class="w-20 h-20 bg-[#7DC2A5]/10 rounded-full flex items-center justify-center mb-6 text-[#7DC2A5]">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
            </svg>
        </div>

        {{-- Titre --}}
        <h1 class="text-6xl font-extrabold text-slate-900 mb-2">404</h1>
        <h2 class="text-2xl font-bold text-slate-700 mb-4">Vous êtes hors-piste !</h2>

        {{-- Description --}}
        <p class="text-slate-500 mb-8 leading-relaxed">
            Il semblerait que vous ayez manqué une balise. La page que vous cherchez n'existe pas ou a été déplacée.
        </p>

        {{-- BOUTON ACCUEIL --}}
        {{-- Remplace 'home' par le nom de ta route principale si différent (ex: 'welcome' ou 'mainPage') --}}
        <a href="{{ route('home') }}" class="inline-flex items-center px-8 py-4 bg-[#A67C52] hover:bg-[#8B6A47] text-white font-extrabold rounded-2xl shadow-lg shadow-[#A67C52]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 text-sm uppercase tracking-wider">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            Retour à l'accueil
        </a>
    </div>
</div>
@endsection