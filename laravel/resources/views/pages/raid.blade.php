@extends('layouts.app')

@section('title', 'Détail raid')

@section('content')
<div class="min-h-screen w-full">
    <div class="w-full px-0 py-10 lg:py-14">
  
        <div class="grid w-full grid-cols-1 gap-8 lg:grid-cols-12 lg:gap-12 lg:px-16">
            <div class="lg:col-span-5 ml-4">
                <h1 class="text-4xl font-extrabold tracking-tight text-black sm:text-5xl">
                    {{ $raid->RAID_NOM }}
                </h1>

                <div class="mt-8 space-y-4">
                    <div class="flex gap-4 border-b border-black/10 pb-3">
                        <div class="w-36 shrink-0 text-sm font-semibold text-black">Inscriptions</div>
                        <div class="text-sm text-black/80">
                            {{ optional($raid->RAID_DATE_DEBUT_INSCRI)->format('d/m/Y') }}
                            →
                            {{ optional($raid->RAID_DATE_FIN_INSCRI)->format('d/m/Y') }}
                        </div>
                    </div>

                    <div class="flex gap-4 border-b border-black/10 pb-3">
                        <div class="w-36 shrink-0 text-sm font-semibold text-black">Dates du raid</div>
                        <div class="text-sm text-black/80">
                            {{ optional($raid->RAID_DATE_DEBUT)->format('d/m/Y') }}
                            →
                            {{ optional($raid->RAID_DATE_FIN)->format('d/m/Y') }}
                        </div>
                    </div>

                    <div class="flex gap-4 border-b border-black/10 pb-3">
                        <div class="w-36 shrink-0 text-sm font-semibold text-black">Contact</div>
                        <div class="text-sm text-black/80 break-words">
                            {{ $raid->RAID_CONTACT }}
                        </div>
                    </div>

                    @if(!empty($raid->RAID_LIEN_SITE_WEB))
                        <div class="flex gap-4 border-b border-black/10 pb-3">
                            <div class="w-36 shrink-0 text-sm font-semibold text-black">Site</div>
                            <div class="text-sm text-black/80 break-words">
                                <a href="{{ $raid->RAID_LIEN_SITE_WEB }}"
                                   target="_blank" rel="noopener"
                                   class="underline decoration-black/30 underline-offset-4 hover:decoration-black">
                                    {{ $raid->RAID_LIEN_SITE_WEB }}
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
                {{-- Courses associées --}}
<div class="mt-10">
    <h2 class="text-xl font-extrabold text-black">Courses</h2>

    @if($raid->courses->isEmpty())
        <p class="mt-2 text-sm text-black/60">Aucune course associée à ce raid.</p>
    @else
        <div class="mt-4 space-y-3">
            @foreach($raid->courses as $course)
                <div class="flex items-center justify-between gap-4 rounded-md border border-black/10 bg-white/40 px-4 py-3">
                    <div class="min-w-0">
                        <div class="truncate font-semibold text-black">
                            {{ $course->COU_NOM }}
                        </div>

                        <div class="mt-1 text-sm text-black/60">
                            {{ optional($course->COU_DATE_DEPART)->format('d/m/Y H:i') }}
                            →
                            {{ optional($course->COU_DATE_FIN)->format('d/m/Y H:i') }}
                            • Durée: {{ $course->COU_DUREE }} min
                            • Difficulté: {{ $course->COU_DIFFICULTE }}
                        </div>
                    </div>

                    <a href="{{ route('race.show', $course->COU_NUM) }}"
                       class="shrink-0 rounded-md bg-black px-4 py-2 text-sm font-semibold text-white hover:bg-black/90">
                        Voir
                    </a>
                </div>
            @endforeach
        </div>
    @endif
</div>

            </div>

            <div class="lg:col-span-7 flex flex-col min-h-0">
                <div class="flex-1 min-h-0 overflow-hidden rounded-md border border-black/10 bg-[#E7F3FF]">
                    <div id="map"
                        class="w-full h-full"
                        data-lat="{{ str_replace(',', '.', $raid->RAID_LATITUDE ?? '') }}"
                        data-lng="{{ str_replace(',', '.', $raid->RAID_LONGITUDE ?? '') }}"
                        data-name="{{ e($raid->RAID_NOM) }}"></div>
                </div>

                @if(!empty($raid->RAID_ILLUSTRATION))
                    <div class="mt-4 overflow-hidden rounded-md border border-black/10">
                        <img class="h-auto w-full"
                             src="{{ asset('images/' . $raid->RAID_ILLUSTRATION) }}"
                             alt="Illustration {{ $raid->RAID_NOM }}">
                    </div>
                @endif
                @auth
                    @if(optional(auth()->user())->INS_ID && optional($raid)->INS_ID == auth()->user()->INS_ID)
                        <div class="mt-4">
                            <a href="{{ route('race.create', $raid->RAID_NUM) }}" class="inline-block rounded-md bg-[#7DC2A5] px-4 py-2 font-semibold text-black">Créer une course</a>
                        </div>
                    @endif
                @endauth
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
    /* Use min-height + height:100% so the map can fill the right column without leaving a large gap */
    #map { min-height: 320px; height: 100%; max-height: 720px; }
    @media (min-width: 1024px) { #map { min-height: 360px; height: 100%; max-height: 900px; } }
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
(function () {
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
        attribution: '&copy; OpenStreetMap'
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

    L.marker([lat, lng]).addTo(map).bindPopup(`<b>${name}</b>`);

    setTimeout(() => map.invalidateSize(), 150);
    window.addEventListener('resize', () => setTimeout(() => map.invalidateSize(), 200));
})();
</script>
@endpush
