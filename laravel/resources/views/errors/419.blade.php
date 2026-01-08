@extends('layouts.app')

@section('title', 'Session Expirée - L\'Embuscade')

@section('content')
<div class="h-screen w-full flex items-center justify-center bg-[#f7f5e6] px-4 relative overflow-hidden font-sans text-slate-800">

    <div class="absolute top-[-10%] left-[-10%] w-[40rem] h-[40rem] bg-[#7DC2A5] rounded-full mix-blend-multiply filter blur-[100px] opacity-20 animate-blob"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40rem] h-[40rem] bg-yellow-200 rounded-full mix-blend-multiply filter blur-[100px] opacity-30 animate-blob animation-delay-2000"></div>

    <div class="w-full max-w-lg bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl border border-white/50 flex flex-col items-center text-center p-10 md:p-12 relative z-10">
        
        {{-- Icône : Horloge --}}
        <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-6 text-slate-500">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>

        <h1 class="text-6xl font-extrabold text-slate-900 mb-2">419</h1>
        <h2 class="text-2xl font-bold text-slate-700 mb-4">Session Expirée</h2>

        <p class="text-slate-500 mb-8 leading-relaxed">
            Vous êtes resté inactif trop longtemps. Pour des raisons de sécurité, veuillez rafraîchir la page.
        </p>

        <a href="{{ url()->current() }}" class="inline-flex items-center px-8 py-4 bg-[#A67C52] hover:bg-[#8B6A47] text-white font-extrabold rounded-2xl shadow-lg shadow-[#A67C52]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 text-sm uppercase tracking-wider">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Rafraîchir la page
        </a>
    </div>
</div>
@endsection