@extends('layouts.app')

@section('title', 'Accès Refusé - L\'Embuscade')

@section('content')
<div class="h-screen w-full flex items-center justify-center bg-[#f7f5e6] px-4 relative overflow-hidden font-sans text-slate-800">

    {{-- Blobs --}}
    <div class="absolute top-[-10%] left-[-10%] w-[40rem] h-[40rem] bg-[#7DC2A5] rounded-full mix-blend-multiply filter blur-[100px] opacity-20 animate-blob"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40rem] h-[40rem] bg-yellow-200 rounded-full mix-blend-multiply filter blur-[100px] opacity-30 animate-blob animation-delay-2000"></div>

    {{-- CARTE --}}
    <div class="w-full max-w-lg bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl border border-white/50 flex flex-col items-center text-center p-10 md:p-12 relative z-10">
        
        {{-- Icône : Cadenas / Stop --}}
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mb-6 text-red-500">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
        </div>

        <h1 class="text-6xl font-extrabold text-slate-900 mb-2">403</h1>
        <h2 class="text-2xl font-bold text-slate-700 mb-4">Zone Interdite</h2>

        <p class="text-slate-500 mb-8 leading-relaxed">
            Halte là ! Vous n'avez pas les droits nécessaires pour accéder à cette page ou modifier ce contenu.
        </p>

        <a href="{{ route('home') }}" class="inline-flex items-center px-8 py-4 bg-[#A67C52] hover:bg-[#8B6A47] text-white font-extrabold rounded-2xl shadow-lg shadow-[#A67C52]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 text-sm uppercase tracking-wider">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path>
            </svg>
            Retour en lieu sûr
        </a>
    </div>
</div>
@endsection