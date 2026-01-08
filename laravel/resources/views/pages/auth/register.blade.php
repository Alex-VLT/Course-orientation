@extends('layouts.app')

@section('title', "Inscription - L'Embuscade")

@section('content')
<div class="min-h-screen w-full flex bg-white font-sans text-slate-800">

    {{-- GAUCHE : VISUEL IMMERSIF (50% de l'écran pour l'équilibre) --}}
    <div class="hidden lg:flex lg:w-1/2 bg-[#f7f5e6] relative flex-col justify-center items-center p-16 overflow-hidden">
        {{-- Cercle décoratif --}}
        <div class="absolute w-[500px] h-[500px] bg-[#7DC2A5] rounded-full mix-blend-multiply filter blur-[100px] opacity-20 animate-pulse"></div>
        
        <div class="relative z-10 text-center max-w-md">
            <div class="mb-6 inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white text-[#7DC2A5] shadow-sm">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <h2 class="text-4xl font-extrabold text-slate-900 mb-4 tracking-tight">Rejoignez la course.</h2>
            <p class="text-lg text-slate-600 leading-relaxed">
                Créez votre compte pour gérer vos inscriptions, suivre vos performances et accéder aux raids exclusifs.
            </p>
        </div>

        {{-- Footer visuel --}}
        <div class="absolute bottom-8 text-sm font-semibold text-slate-400 uppercase tracking-widest">
            L'Embuscade &bull; Saison 2025
        </div>
    </div>

    {{-- DROITE : FORMULAIRE ÉPURÉ --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 md:p-12 lg:p-16 overflow-y-auto h-screen bg-white">
        <div class="w-full max-w-lg">
            
            <div class="flex justify-between items-baseline mb-8 border-b border-slate-100 pb-4">
                <h1 class="text-2xl font-bold text-slate-900">Inscription</h1>
                <a href="{{ route('login') }}" class="text-sm font-semibold text-[#7DC2A5] hover:text-[#5fa388] transition-colors">
                    J'ai déjà un compte
                </a>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-xl bg-red-50 border border-red-100 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Merci de corriger les erreurs :</h3>
                            <ul class="list-disc pl-5 mt-1 text-sm text-red-700">
                                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" class="space-y-5">
                @csrf

                {{-- Groupe Identité --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-slate-700">Nom</label>
                        <input type="text" name="nom" value="{{ old('nom') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 focus:bg-white focus:border-[#7DC2A5] focus:ring-2 focus:ring-[#7DC2A5]/20 outline-none transition-all placeholder-slate-400" required>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-slate-700">Prénom</label>
                        <input type="text" name="prenom" value="{{ old('prenom') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 focus:bg-white focus:border-[#7DC2A5] focus:ring-2 focus:ring-[#7DC2A5]/20 outline-none transition-all placeholder-slate-400" required>
                    </div>
                </div>

                {{-- Contact Rapide --}}
                <div class="space-y-1">
                    <label class="text-sm font-semibold text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 focus:bg-white focus:border-[#7DC2A5] focus:ring-2 focus:ring-[#7DC2A5]/20 outline-none transition-all placeholder-slate-400" placeholder="exemple@domaine.com" required>
                </div>

                {{-- Infos Perso --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-slate-700">Naissance</label>
                        <input type="date" name="naissance" max="{{ date('Y-m-d', strtotime('-12 years'))}}" value="{{ old('naissance') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 focus:bg-white focus:border-[#7DC2A5] focus:ring-2 focus:ring-[#7DC2A5]/20 outline-none transition-all" required>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-slate-700">Téléphone</label>
                        <input type="text" name="tel" pattern="[0-9]{10}" maxlength="10" inputmode="numeric" value="{{ old('tel') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 focus:bg-white focus:border-[#7DC2A5] focus:ring-2 focus:ring-[#7DC2A5]/20 outline-none transition-all placeholder-slate-400" placeholder="06.." required>
                    </div>
                </div>

                {{-- Adresse --}}
                <div class="space-y-1">
                    <label class="text-sm font-semibold text-slate-700">Adresse</label>
                    <div class="grid grid-cols-3 gap-2">
                        <input type="text" name="adresse" maxlength="255" value="{{ old('adresse') }}" class="col-span-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 focus:bg-white focus:border-[#7DC2A5] focus:ring-2 focus:ring-[#7DC2A5]/20 outline-none transition-all placeholder-slate-400" placeholder="Numéro et rue" required>
                        <input type="text" name="cp" pattern="[0-9]{5}" maxlength="5" inputmode="numeric" value="{{ old('cp') }}" class="col-span-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 focus:bg-white focus:border-[#7DC2A5] focus:ring-2 focus:ring-[#7DC2A5]/20 outline-none transition-all placeholder-slate-400" placeholder="CP" required>
                        <input type="text" name="ville" maxlength="64" value="{{ old('ville') }}" class="col-span-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 focus:bg-white focus:border-[#7DC2A5] focus:ring-2 focus:ring-[#7DC2A5]/20 outline-none transition-all placeholder-slate-400" placeholder="Ville" required>
                    </div>
                </div>

                {{-- Club Toggle (Style Carte) --}}
                <div class="rounded-xl border border-slate-200 p-4 bg-white shadow-sm">
                    <div class="flex items-center justify-between">
                        <label for="is_club" class="text-sm font-bold text-slate-800 cursor-pointer select-none">
                            Êtes-vous membre d'un club ?
                        </label>
                        <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                            <input type="checkbox" name="is_club" id="is_club" value="1" {{ old('is_club') ? 'checked' : '' }} class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-4 appearance-none cursor-pointer border-slate-300 checked:right-0 checked:border-[#7DC2A5] transition-all duration-200"/>
                            <label for="is_club" class="toggle-label block overflow-hidden h-5 rounded-full bg-slate-300 cursor-pointer"></label>
                        </div>
                    </div>

                    {{-- Inputs cachés --}}
                    <div id="club_inputs" class="hidden mt-4 pt-4 border-t border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-slate-500 font-bold mb-1 block">LICENCE</label>
                            <input type="text" name="licence" maxlength="32" value="{{ old('licence') }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#7DC2A5] focus:ring-1 focus:ring-[#7DC2A5] outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-slate-500 font-bold mb-1 block">CLUB</label>
                            <select name="club_id" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm bg-white focus:border-[#7DC2A5] focus:ring-1 focus:ring-[#7DC2A5] outline-none">
                                <option value="">Sélectionner...</option>
                                @if(isset($clubs))
                                    @foreach($clubs as $club)
                                        <option value="{{ $club->CLU_NUM }}" {{ old('club_id') == $club->CLU_NUM ? 'selected' : '' }}>{{ $club->CLU_NOM }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Mots de passe --}}
                <div class="grid grid-cols-2 gap-4 pt-2">
                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-slate-700">Mot de passe</label>
                        <input type="password" name="password" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 focus:bg-white focus:border-[#7DC2A5] focus:ring-2 focus:ring-[#7DC2A5]/20 outline-none transition-all" required>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-slate-700">Confirmation</label>
                        <input type="password" name="password_confirmation" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 focus:bg-white focus:border-[#7DC2A5] focus:ring-2 focus:ring-[#7DC2A5]/20 outline-none transition-all" required>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full rounded-xl bg-[#2F5D4F] px-6 py-4 text-sm font-bold text-white shadow-lg hover:bg-[#254a3f] hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200">
                        VALIDER L'INSCRIPTION
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- CSS spécifique pour le Toggle Switch --}}
<style>
    .toggle-checkbox:checked {
        right: 0;
        border-color: #7DC2A5;
    }
    .toggle-checkbox:checked + .toggle-label {
        background-color: #7DC2A5;
    }
</style>

@vite('resources/js/register.js')
@endsection