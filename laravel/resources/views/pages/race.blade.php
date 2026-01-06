@extends('layouts.app')

@section('title', 'Détail course')

@section('content')
<div class="min-h-screen w-full">
    <div class="w-full px-0 py-10 lg:py-14">
  
        <div class="grid px-8 grid-cols-1 gap-8 lg:grid-cols-12 lg:gap-12 lg:px-16">
            <div class="lg:col-span-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <a href="{{ url('/raid') }}/{{ $race->RAID_NUM }}" class="mt-2 text-sm text-[#4f2200]/80 underline p-2">> Raid : {{ optional($race->raid)->RAID_NOM }}</a>
                        <h1 class="text-4xl font-extrabold tracking-tight text-black sm:text-5xl">
                            {{ $race->COU_NOM }}
                        </h1>
                        
                    </div>

                    <div class="shrink-0">
                        <a href="{{ url('/inscForm') }}?course={{ $race->COU_NUM }}"
                           class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-4 py-2 text-xl font-semibold text-white hover:bg-emerald-700">
                            S'inscrire
                        </a>
                    </div>
                </div>

                <div class="mt-8 space-y-4">
                    <div class="flex gap-4 border-b border-black/10 pb-3">
                        <div class="w-36 shrink-0 text-sm font-semibold text-black">Inscriptions</div>
                        <div class="text-sm text-black/80">
                            {{ optional($race->raid)->RAID_DATE_DEBUT_INSCRI ? optional($race->raid)->RAID_DATE_DEBUT_INSCRI->format('d/m/Y') : '-' }}
                            →
                            {{ optional($race->raid)->RAID_DATE_FIN_INSCRI ? optional($race->raid)->RAID_DATE_FIN_INSCRI->format('d/m/Y') : '-' }}
                        </div>
                    </div>

                    <div class="flex gap-4 border-b border-black/10 pb-3">
                        <div class="w-36 shrink-0 text-sm font-semibold text-black">Dates</div>
                        <div class="text-sm text-black/80">
                            {{ optional($race->COU_DATE_DEPART)->format('d/m/Y H:i') }}
                            →
                            {{ optional($race->COU_DATE_FIN)->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    <div class="flex gap-4 border-b border-black/10 pb-3">
                        <div class="w-36 shrink-0 text-sm font-semibold text-black">Contact</div>
                        <div class="text-sm text-black/80 break-words">
                            {{ optional($race->raid)->RAID_CONTACT ?? '-' }}
                        </div>
                    </div>

                    @if(!empty(optional($race->raid)->RAID_LIEN_SITE_WEB))
                        <div class="flex gap-4 border-b border-black/10 pb-3">
                            <div class="w-36 shrink-0 text-sm font-semibold text-black">Site</div>
                            <div class="text-sm text-black/80 break-words">
                                <a href="{{ optional($race->raid)->RAID_LIEN_SITE_WEB }}"
                                   target="_blank" rel="noopener"
                                   class="underline decoration-black/30 underline-offset-4 hover:decoration-black">
                                    {{ optional($race->raid)->RAID_LIEN_SITE_WEB }}
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="mt-10">
                    <h2 class="text-xl font-extrabold text-black">Informations de la course</h2>

                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="rounded-md border border-black/10 bg-white/40 px-4 py-3">
                            <div class="text-sm text-black/80 space-y-2">
                                <div><strong>Durée :</strong> {{ $race->COU_DUREE }} min</div>
                                <div>
                                    <strong>Difficulté :</strong>
                                    <div class="mt-1 flex items-center gap-2">
                                        @php
                                            $difficulty = (int) max(0, min(10, $race->COU_DIFFICULTE ?? 0));
                                        @endphp
                                        <div role="img" aria-label="Difficulté : {{ $difficulty }}/10"
                                             title="Difficulté : {{ $difficulty }}/10" class="flex items-center gap-1">
                                            @for ($i = 1; $i <= 10; $i++)
                                                <span class="inline-block w-2 h-2 md:w-3 md:h-3 rounded-full {{ $i <= $difficulty ? 'bg-black' : 'border border-black/10' }}" aria-hidden="true"></span>
                                            @endfor
                                        </div>
                                        <div class="text-sm text-black/60">{{ $difficulty }}/10</div>
                                    </div>
                                </div>
                                <div>
                                    <strong>Dates :</strong>
                                    <div class="text-sm text-black/60">
                                        {{ optional($race->COU_DATE_DEPART)->format('d/m/Y H:i') }}
                                        →
                                        {{ optional($race->COU_DATE_FIN)->format('d/m/Y H:i') }}
                                    </div>
                                </div>
                                @if(!is_null($race->COU_PRIX_REPAS) || !is_null($race->COU_REDUC_LICENCIE))
                                    <div class="flex flex-wrap items-center gap-3">
                                        @if(!is_null($race->COU_PRIX_REPAS))
                                            <span class="inline-block rounded px-2 py-1 text-sm font-medium bg-black/5">🍽️ Prix repas : {{ number_format($race->COU_PRIX_REPAS, 2, ',', ' ') }} €</span>
                                        @endif
                                        @if(!is_null($race->COU_REDUC_LICENCIE))
                                            <span class="inline-block rounded px-2 py-1 text-sm font-medium bg-black/5">🏷️ Réduc licencié : {{ $race->COU_REDUC_LICENCIE }}€</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="rounded-md border border-black/10 bg-white/40 px-4 py-3">
                            <div class="text-sm text-black/80 space-y-3">
                                {{-- Participants --}}
                                @if($race->COU_NB_PART_MIN || $race->COU_NB_PART_MAX)
                                    <div>
                                        <div class="text-sm font-semibold text-black">Participants</div>
                                        <div class="text-sm text-black/60">
                                            @if($race->COU_NB_PART_MIN && $race->COU_NB_PART_MAX)
                                                {{ $race->COU_NB_PART_MIN }} à {{ $race->COU_NB_PART_MAX }} participants
                                            @elseif($race->COU_NB_PART_MIN)
                                                Minimum {{ $race->COU_NB_PART_MIN }} participants
                                            @else
                                                Maximum {{ $race->COU_NB_PART_MAX }} participants
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Équipes --}}
                                @if($race->COU_PART_PAR_EQU_MAX || $race->COU_NB_EQU_MIN || $race->COU_NB_EQU_MAX)
                                    <div>
                                        <div class="text-sm font-semibold text-black">Équipes</div>
                                        <div class="text-sm text-black/60">
                                            @if($race->COU_PART_PAR_EQU_MAX)
                                                Taille d'équipe : 1 à {{ $race->COU_PART_PAR_EQU_MAX }} personne(s) par équipe
                                            @endif

                                            @if($race->COU_NB_EQU_MIN && $race->COU_NB_EQU_MAX)
                                                <div>Nombre d'équipes : {{ $race->COU_NB_EQU_MIN }} à {{ $race->COU_NB_EQU_MAX }}</div>
                                            @elseif($race->COU_NB_EQU_MIN)
                                                <div>Nombre minimum d'équipes : {{ $race->COU_NB_EQU_MIN }}</div>
                                            @elseif($race->COU_NB_EQU_MAX)
                                                <div>Nombre maximum d'équipes : {{ $race->COU_NB_EQU_MAX }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="lg:col-span-7">
                <div class="overflow-hidden rounded-md border border-black/10 bg-[#E7F3FF]">
                    <div id="map"
                        class="w-full"
                        data-lat="{{ optional($race->raid)->RAID_LATITUDE }}"
                        data-lng="{{ optional($race->raid)->RAID_LONGITUDE }}"
                        data-name="{{ e(optional($race->raid)->RAID_NOM ?? $race->COU_NOM) }}"></div>
                </div>

                @if(!empty(optional($race->raid)->RAID_ILLUSTRATION))
                    <div class="mt-4 overflow-hidden rounded-md border border-black/10">
                        <img class="h-auto w-full"
                             src="{{ asset('storage/' . optional($race->raid)->RAID_ILLUSTRATION) }}"
                             alt="Illustration {{ optional($race->raid)->RAID_NOM ?? $race->COU_NOM }}">
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>

<style>
    html, body { margin: 0; padding: 0; }
    #map { height: 320px; }
    @media (min-width: 1024px) { #map { height: 360px; } }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

<script>
(function () {
    const el = document.getElementById('map');
    if (!el) return;

    const lat = parseFloat(el.dataset.lat);
    const lng = parseFloat(el.dataset.lng);
    const name = el.dataset.name || 'Raid';

    if (Number.isNaN(lat) || Number.isNaN(lng)) {
        el.innerHTML = '<div style="padding:16px">Coordonnées manquantes.</div>';
        return;
    }

    const map = L.map('map', { scrollWheelZoom: false }).setView([lat, lng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    L.marker([lat, lng]).addTo(map).bindPopup(`<b>${name}</b>`);

    setTimeout(() => map.invalidateSize(), 150);
})();
</script>
@endpush
