@extends('layouts.app')

@section('title', "Connexion - L'Embuscade")

@section('content')
<div class="min-h-screen w-full flex bg-white font-sans text-slate-800">

    {{-- GAUCHE : VISUEL IMMERSIF (50%) --}}
    <div class="hidden lg:flex lg:w-1/2 bg-[#f7f5e6] relative flex-col justify-center items-center p-16 overflow-hidden">
        {{-- Cercle décoratif --}}
        <div class="absolute w-[500px] h-[500px] bg-[#7DC2A5] rounded-full mix-blend-multiply filter blur-[100px] opacity-20 animate-pulse"></div>
        
        <div class="relative z-10 text-center max-w-md">
            {{-- Petit logo ou icône --}}
            <div class="mb-6 inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white text-[#7DC2A5] shadow-sm">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
            </div>

            <h2 class="text-4xl font-extrabold text-slate-900 mb-4 tracking-tight">Bon retour 👋</h2>
            <p class="text-lg text-slate-600 leading-relaxed">
                Connectez-vous pour gérer vos inscriptions, consulter les raids et suivre vos performances.
            </p>
        </div>

        {{-- Footer visuel --}}
        <div class="absolute bottom-8 text-sm font-semibold text-slate-400 uppercase tracking-widest">
            L'Embuscade &bull; Espace Membre
        </div>
    </div>

    {{-- DROITE : FORMULAIRE DE CONNEXION (50%) --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 md:p-12 lg:p-16 overflow-y-auto h-screen bg-white">
        <div class="w-full max-w-md">
            
            <div class="flex justify-between items-baseline mb-8 border-b border-slate-100 pb-4">
                <h1 class="text-2xl font-bold text-slate-900">Connexion</h1>
                <a href="{{ route('register') }}" class="text-sm font-semibold text-[#7DC2A5] hover:text-[#5fa388] transition-colors">
                    Pas encore de compte ?
                </a>
            </div>

            @if (session('status'))
                <div class="mb-6 rounded-xl bg-green-50 border border-green-100 p-4 text-sm text-green-700 font-medium">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-xl bg-red-50 border border-red-100 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Erreur de connexion</h3>
                            <ul class="list-disc pl-5 mt-1 text-sm text-red-700">
                                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-6">
                @csrf

                <div class="space-y-1">
                    <label for="email" class="text-sm font-semibold text-slate-700">Email</label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        value="{{ old('email') }}" 
                        placeholder="jean@mail.fr" 
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:bg-white focus:border-[#7DC2A5] focus:ring-2 focus:ring-[#7DC2A5]/20 outline-none transition-all placeholder-slate-400" 
                        required
                    >
                </div>

                <div class="space-y-1">
                    <div class="flex justify-between items-center">
                        <label for="password" class="text-sm font-semibold text-slate-700">Mot de passe</label>
                        <a href="{{ route('password.request') }}" class="text-xs font-semibold text-[#7DC2A5] hover:text-[#5fa388] transition-colors">
                            Mot de passe oublié ?
                        </a>
                    </div>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        placeholder="••••••••" 
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:bg-white focus:border-[#7DC2A5] focus:ring-2 focus:ring-[#7DC2A5]/20 outline-none transition-all placeholder-slate-400" 
                        required
                    >
                </div>

                <button 
                    type="submit" 
                    class="w-full rounded-xl bg-[#2F5D4F] px-6 py-4 text-sm font-bold text-white shadow-lg hover:bg-[#254a3f] hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 mt-4"
                >
                    Se connecter
                </button>
            </form>

            <div class="mt-8 text-center text-sm text-slate-500">
                Vous n'avez pas de compte ? 
                <a href="{{ route('register') }}" class="font-bold text-slate-800 hover:text-[#7DC2A5] transition-colors">Créer un compte</a>
            </div>

        </div>
    </div>
</div>
@endsection