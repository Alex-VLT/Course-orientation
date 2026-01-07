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
                    @if($teamsCount < $race->COU_NB_EQU_MAX)
                    @auth
                        <div class="shrink-0">
                            <a href="{{ url('/inscForm') }}?course={{ $race->COU_NUM }}"
                               class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-4 py-2 text-md font-semibold text-white hover:bg-emerald-700">
                                S'inscrire
                            </a>
                        </div>
                    @else
                        <div class="shrink-0">
                            <a href="{{ route('login') }}"
                               class="inline-flex items-center gap-2 rounded-md border border-black/10 bg-white/5 px-4 py-2 text-md font-semibold text-black hover:bg-black/5">
                                Se connecter pour s'inscrire
                            </a>
                        </div>
                    @endauth
                    @endif
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

                    @php
                        $raidMinAge = null;
                        $raidMaxAge = null;
                        if ($race->raid) {
                            foreach ($race->raid->courses as $c) {
                                if (!empty($c->acceptances)) {
                                    foreach ($c->acceptances as $acc) {
                                        if ($acc->tranche) {
                                            $raidMinAge = is_null($raidMinAge) ? $acc->tranche->TRA_AGE_MIN : min($raidMinAge, $acc->tranche->TRA_AGE_MIN);
                                            $raidMaxAge = is_null($raidMaxAge) ? $acc->tranche->TRA_AGE_MAX : max($raidMaxAge, $acc->tranche->TRA_AGE_MAX);
                                        }
                                    }
                                }
                            }
                        }
                    @endphp

                    @if(!is_null($raidMinAge) || !is_null($raidMaxAge))
                        <div class="flex gap-4 border-b border-black/10 pb-3">
                            <div class="w-36 shrink-0 text-sm font-semibold text-black">Âge requis</div>
                            <div class="text-sm text-black/80">
                                @if(!is_null($raidMinAge) && !is_null($raidMaxAge))
                                    {{ $raidMinAge }} à {{ $raidMaxAge }} ans
                                @elseif(!is_null($raidMinAge))
                                    À partir de {{ $raidMinAge }} ans
                                @else
                                    Jusqu'à {{ $raidMaxAge }} ans
                                @endif
                            </div>
                        </div>
                    @endif

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
                                        <div class="text-sm text-black/60">{{ $race->COU_DIFFICULTE }}</div>
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
                                            <span class="inline-block rounded px-2 py-1 text-sm font-medium bg-black/5">🏷️ Prix repas licencié : {{ $race->COU_REDUC_LICENCIE }}€</span>
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
                                                <div class="mt-2">Équipes inscrites : {{ $teamsCount ?? 0 }}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>

                <div class="lg:col-span-7 space-y-4">
                    @if($race->acceptances && $race->acceptances->isNotEmpty())
                        <div class="mt-6">
                            <h3 class="text-lg font-extrabold text-black">Tarifs par tranche d'âge</h3>
                            <div class="mt-3 rounded-md border border-black/10 bg-white/40 px-4 py-3">
                                <ul class="space-y-2 text-sm text-black/80">
                                    @foreach($race->acceptances as $acc)
                                        @php $t = $acc->tranche; @endphp
                                        <li class="flex items-center justify-between">
                                            <div>
                                                @if($t)
                                                    {{ $t->TRA_AGE_MIN }} – {{ $t->TRA_AGE_MAX }} ans
                                                @else
                                                    Tranche #{{ $acc->TRA_ID }}
                                                @endif
                                            </div>
                                            <div class="font-semibold">{{ number_format($acc->ACC_PRIX, 2, ',', ' ') }} €</div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <div class="overflow-hidden rounded-md border border-black/10 bg-[#E7F3FF]">
                        <div id="map"
                            class="w-full"
                            data-lat="{{ str_replace(',', '.', optional($race->raid)->RAID_LATITUDE ?? '') }}"
                            data-lng="{{ str_replace(',', '.', optional($race->raid)->RAID_LONGITUDE ?? '') }}"
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
    #map { 
        height: 400px; 
        max-height: 480px;
        width: 100%;
        z-index: 0;
    }
    /* Ensure Leaflet tiles render as images and don't inherit global image styles */
    #map .leaflet-tile, #map img.leaflet-tile {
        display: block;
        image-rendering: auto;
        max-width: none;
        max-height: none;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('map');
    if (!el) return;

    const lat = parseFloat(String(el.dataset.lat || '').replace(',', '.').trim());
    const lng = parseFloat(String(el.dataset.lng || '').replace(',', '.').trim());
    const name = el.dataset.name || 'Raid';

    if (!Number.isFinite(lat) || !Number.isFinite(lng) || lat < -90 || lat > 90 || lng < -180 || lng > 180) {
        el.innerHTML = '<div style="padding:16px">Coordonnées invalides ou manquantes.</div>';
        return;
    }

    const map = L.map('map', { scrollWheelZoom: false, minZoom: 2, maxZoom: 19 }).setView([lat, lng], 13);

    const tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        noWrap: true,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let tileErrors = 0;
    tiles.on('tileerror', () => {
        tileErrors++;
        if (tileErrors > 5) {
            tiles.remove();
            const fallback = L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', { maxZoom: 19, noWrap: true }).addTo(map);
            fallback.on('tileerror', () => {
                el.innerHTML = '<div style="padding:16px">Impossible de charger la carte pour le moment.</div>';
            });
        }
    });

    L.marker([lat, lng]).addTo(map).bindPopup(`<b>${name}</b>`).openPopup();

    setTimeout(() => map.invalidateSize(), 150);
    window.addEventListener('resize', () => setTimeout(() => map.invalidateSize(), 200));
});
</script>
@endpush
