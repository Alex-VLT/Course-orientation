@extends('layouts.app')

@section('title', 'Erreur Technique - L\'Embuscade')

@section('content')
<div class="h-screen w-full flex items-center justify-center bg-[#f7f5e6] px-4 relative overflow-hidden font-sans text-slate-800">

    {{-- Blobs --}}
    <div class="absolute top-[-10%] left-[-10%] w-[40rem] h-[40rem] bg-[#7DC2A5] rounded-full mix-blend-multiply filter blur-[100px] opacity-20 animate-blob"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40rem] h-[40rem] bg-yellow-200 rounded-full mix-blend-multiply filter blur-[100px] opacity-30 animate-blob animation-delay-2000"></div>

    {{-- CARTE --}}
    <div class="w-full max-w-lg bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl border border-white/50 flex flex-col items-center text-center p-10 md:p-12 relative z-10">
        
        {{-- Icône : Éclair / Serveur cassé --}}
        <div class="w-20 h-20 bg-orange-100 rounded-full flex items-center justify-center mb-6 text-orange-500">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
        </div>

        <h1 class="text-6xl font-extrabold text-slate-900 mb-2">500</h1>
        <h2 class="text-2xl font-bold text-slate-700 mb-4">Problème Technique</h2>

        <p class="text-slate-500 mb-8 leading-relaxed">
            Oups ! Quelque chose s'est cassé de notre côté. Nos équipes sont probablement déjà en train de courir après le bug.
        </p>

        <a href="{{ url()->previous() }}" class="inline-flex items-center px-8 py-4 bg-[#7DC2A5] hover:bg-[#68a88d] text-white font-extrabold rounded-2xl shadow-lg shadow-[#7DC2A5]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 text-sm uppercase tracking-wider">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Réessayer
        </a>
    </div>
</div>
@endsection