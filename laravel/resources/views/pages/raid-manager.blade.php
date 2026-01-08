@extends('layouts.app')

@section('title', 'Gestion de mes Raids - L\'Embuscade')

@section('content')
<div class="min-h-screen w-full bg-[#f7f5e6] px-4 py-8 lg:py-12" x-data="{ openRaid: null }">
    <div class="max-w-7xl mx-auto">

        {{-- Flash messages --}}
        @if(session('success') || session('error') || session('info'))
            @php $flash = session('success') ?? session('error') ?? session('info'); $type = session('success') ? 'success' : (session('error') ? 'error' : 'info'); @endphp
            <div class="mb-6">
                <div class="rounded-md px-4 py-3 text-sm {{ $type === 'success' ? 'bg-green-50 text-green-800' : ($type === 'error' ? 'bg-red-50 text-red-800' : 'bg-blue-50 text-blue-800') }}">
                    {{ $flash }}
                </div>
            </div>
        @endif

        {{-- En-tête global + filtre année --}}
        <div class="mb-8 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-black">Gestion de mes Raids</h1>
                <p class="text-slate-600 mt-1">Gérez vos raids et créez des courses pour chacun d'eux.</p>
            </div>
            <form method="GET" action="{{ route('raids.manager') }}" class="flex items-center gap-3 bg-white rounded-xl border border-black/5 shadow-sm px-4 py-3">
                <label for="year" class="text-sm font-semibold text-black">Année</label>
                <select id="year" name="year" class="rounded-md bg-gray-100 px-3 py-2 text-sm" onchange="this.form.submit()">
                    @foreach($years ?? [] as $year)
                        <option value="{{ $year }}" {{ ($selectedYear ?? now()->year) == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                    @unless(($years ?? collect())->contains($selectedYear ?? now()->year))
                        <option value="{{ $selectedYear ?? now()->year }}" selected>{{ $selectedYear ?? now()->year }}</option>
                    @endunless
                </select>
                <span class="text-xs text-slate-500">Affichage des raids de {{ $selectedYear ?? now()->year }}</span>
            </form>
        </div>

        {{-- Liste unique des raids de l'année sélectionnée --}}
        <div class="mb-10">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-black">Raids {{ $selectedYear ?? now()->year }}</h2>
                <span class="bg-[#7DC2A5] text-black py-1 px-3 rounded-full text-xs font-bold">
                    {{ count($raids ?? []) }} raid(s)
                </span>
            </div>

            @forelse($raids ?? [] as $raid)
                @php $isPast = $raid->isPast ?? false; @endphp
                <div class="rounded-2xl border border-black/5 shadow-sm overflow-hidden mb-4 {{ $isPast ? 'opacity-70 grayscale bg-white' : 'bg-white' }}">
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div class="flex-1">
                            <h3 class="font-bold text-lg text-black">{{ $raid->RAID_NOM }}</h3>
                            <div class="flex items-center gap-4 mt-2 text-sm text-slate-600">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    {{ \Carbon\Carbon::parse($raid->RAID_DATE_DEBUT)->format('d/m/Y') }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                    {{ $raid->courses_count }} course(s)
                                </span>
                            </div>
                        </div>
                        <button 
                            type="button"
                            @click="openRaid = {{ $raid->RAID_NUM }}"
                            class="rounded-xl {{ $isPast ? 'bg-gray-600 hover:bg-gray-700' : 'bg-black hover:bg-gray-800' }} text-white px-4 py-2 text-sm font-semibold transition-colors"
                        >
                            Détails
                        </button>
                    </div>

                    {{-- Modal de détails --}}
                    <div
                        x-cloak
                        x-show="openRaid == {{ $raid->RAID_NUM }}"
                        x-transition.opacity
                        class="fixed inset-0 z-50 flex items-center justify-center p-4"
                        role="dialog"
                        aria-modal="true"
                        @keydown.escape.window="openRaid = null"
                    >
                        <div class="absolute inset-0 bg-black/50" @click="openRaid = null"></div>
                        <div class="relative w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-xl border p-6">
                            {{-- En-tête modal --}}
                            <div class="flex items-start justify-between gap-4 mb-6 pb-4 border-b">
                                <div>
                                    <h3 class="text-2xl font-bold text-black">{{ $raid->RAID_NOM }}</h3>
                                    <p class="text-sm text-slate-600 mt-1">Raid organisé par {{ optional($raid->club)->CLU_NOM ?? 'Club non renseigné' }}</p>
                                    @if($isPast)
                                        <span class="inline-block mt-2 bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-xs font-bold">Raid terminé</span>
                                    @endif
                                </div>
                                <button type="button" @click="openRaid = null" class="rounded-xl px-3 py-2 text-sm font-semibold hover:bg-slate-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>

                            {{-- Informations du raid --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div class="bg-gray-50 rounded-xl p-4">
                                    <h4 class="font-bold text-black text-sm mb-3">Informations générales</h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-slate-600">Date de début :</span>
                                            <span class="font-semibold text-black">{{ \Carbon\Carbon::parse($raid->RAID_DATE_DEBUT)->format('d/m/Y') }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-600">Date de fin :</span>
                                            <span class="font-semibold text-black">{{ \Carbon\Carbon::parse($raid->RAID_DATE_FIN)->format('d/m/Y') }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-600">Inscriptions ouvertes :</span>
                                            <span class="font-semibold text-black">{{ \Carbon\Carbon::parse($raid->RAID_DATE_DEBUT_INSCRI)->format('d/m/Y') }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-600">Inscriptions fermées :</span>
                                            <span class="font-semibold text-black">{{ \Carbon\Carbon::parse($raid->RAID_DATE_FIN_INSCRI)->format('d/m/Y') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 rounded-xl p-4">
                                    <h4 class="font-bold text-black text-sm mb-3">Contact & Localisation</h4>
                                    <div class="space-y-2 text-sm">
                                        @if($raid->RAID_CONTACT)
                                            <div class="flex justify-between">
                                                <span class="text-slate-600">Contact :</span>
                                                <span class="font-semibold text-black">{{ $raid->RAID_CONTACT }}</span>
                                            </div>
                                        @endif
                                        @if($raid->RAID_LIEN_SITE_WEB)
                                            <div class="flex justify-between">
                                                <span class="text-slate-600">Site web :</span>
                                                <a href="{{ $raid->RAID_LIEN_SITE_WEB }}" target="_blank" class="font-semibold text-blue-600 hover:underline">Visiter</a>
                                            </div>
                                        @endif
                                        @if($raid->RAID_LATITUDE && $raid->RAID_LONGITUDE)
                                            <div class="flex justify-between">
                                                <span class="text-slate-600">Coordonnées :</span>
                                                <span class="font-semibold text-black text-xs">{{ $raid->RAID_LATITUDE }}, {{ $raid->RAID_LONGITUDE }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Liste des courses --}}
                            <div class="mb-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="font-bold text-black">Courses du raid</h4>
                                    <a href="{{ route('race.create', $raid->RAID_NUM) }}" class="rounded-xl bg-[#7DC2A5] px-4 py-2 text-sm font-bold text-black hover:brightness-95 transition-all">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        Créer une course
                                    </a>
                                </div>

                                @if($raid->courses && $raid->courses->count() > 0)
                                    <div class="space-y-2">
                                        @foreach($raid->courses as $course)
                                            <div class="flex items-center justify-between bg-white border border-gray-200 rounded-lg px-4 py-3 hover:bg-gray-50 transition-colors">
                                                <div>
                                                    <div class="font-semibold text-black">{{ $course->COU_NOM }}</div>
                                                    <div class="text-xs text-slate-600 mt-1">
                                                        Départ : {{ \Carbon\Carbon::parse($course->COU_DATE_DEPART)->format('d/m/Y H:i') }}
                                                        · Durée : {{ $course->COU_DUREE }} min
                                                        · Difficulté : {{ $course->COU_DIFFICULTE }}
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <a href="{{ route('race.edit', $course->COU_NUM) }}" class="rounded-md bg-black text-white px-3 py-1 text-xs font-semibold hover:bg-gray-800">
                                                        Modifier
                                                    </a>
                                                    <a href="{{ route('race.show', $course->COU_NUM) }}" class="rounded-md bg-white border border-black/10 px-3 py-1 text-xs font-semibold hover:bg-gray-100">
                                                        Voir détails
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-8 text-slate-500 bg-gray-50 rounded-lg">
                                        <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                        <p class="text-sm">Aucune course créée pour ce raid.</p>
                                    </div>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center justify-end gap-3 pt-4 border-t">
                                <a href="{{ route('raids.edit', $raid->RAID_NUM) }}" class="rounded-xl bg-[#A67C52] hover:bg-[#8B6A47] text-white px-4 py-2 text-sm font-semibold transition-all">
                                    Modifier le raid
                                </a>
                                <a href="{{ route('raid.show', $raid->RAID_NUM) }}" class="rounded-xl border border-black/10 bg-white px-4 py-2 text-sm font-semibold hover:bg-gray-100">
                                    Voir page publique
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-black/5 bg-white/60 p-10 text-center shadow-sm">
                    <div class="mx-auto w-16 h-16 bg-white rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <p class="text-slate-600">Aucun raid pour cette année.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
