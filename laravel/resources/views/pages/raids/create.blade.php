@extends('layouts.app')

@section('title', 'Créer un Raid')

@section('content')
    <div class="max-w-3xl mx-auto py-8">
        <h1 class="text-2xl font-bold">Créer un raid</h1>

        <form action="{{ route('raids.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="space-y-4 bg-white p-6 rounded-md shadow-sm">
                <div>
                    <label class="block text-sm font-semibold">Nom du raid <span class="text-red-600">*</span></label>
                    <input name="RAID_NOM" value="{{ old('RAID_NOM') }}" class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                    @error('RAID_NOM') <div class="text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold">Date début <span class="text-red-600">*</span></label>
                        <input name="RAID_DATE_DEBUT" type="date" value="{{ old('RAID_DATE_DEBUT') }}" class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                        @error('RAID_DATE_DEBUT') <div class="text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold">Date fin <span class="text-red-600">*</span></label>
                        <input name="RAID_DATE_FIN" type="date" value="{{ old('RAID_DATE_FIN') }}" class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                        @error('RAID_DATE_FIN') <div class="text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold">Club organisateur <span class="text-red-600">*</span></label>
                    <select name="CLU_NUM" id="CLU_NUM" class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2">
                        @foreach($clubs as $club)
                            <option value="{{ $club->CLU_NUM }}" {{ old('CLU_NUM') == $club->CLU_NUM ? 'selected' : '' }}>{{ $club->CLU_NOM }}</option>
                        @endforeach
                    </select>
                    @error('CLU_NUM') <div class="text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold">Date début des inscriptions <span class="text-red-600">*</span></label>
                        <input name="RAID_DATE_DEBUT_INSCRI" type="date" value="{{ old('RAID_DATE_DEBUT_INSCRI') }}" class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                        @error('RAID_DATE_DEBUT_INSCRI') <div class="text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold">Date fin des inscriptions <span class="text-red-600">*</span></label>
                        <input name="RAID_DATE_FIN_INSCRI" type="date" value="{{ old('RAID_DATE_FIN_INSCRI') }}" class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                        @error('RAID_DATE_FIN_INSCRI') <div class="text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold">Responsable du raid (membre du club) <span class="text-red-600">*</span></label>
                    <select name="INS_ID" id="INS_ID" class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2">
                        @foreach ($members as $m)
                            <option value="{{ $m->INS_ID }}" data-club="{{ $m->CLU_NUM }}" {{ old('INS_ID') == $m->INS_ID ? 'selected' : '' }}>{{ $m->INS_PRENOM }} {{ $m->INS_NOM }} @if($m->INS_NUM_LICENCE) (Licence: {{ $m->INS_NUM_LICENCE }}) @endif</option>
                        @endforeach
                    </select>
                    <small class="text-gray-600">Choisissez un responsable qui est membre du club sélectionné.</small>
                    @error('INS_ID') <div class="text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold">Site web du raid <small class="text-gray-500">(optionnel)</small></label>
                    <input name="RAID_LIEN_SITE_WEB" value="{{ old('RAID_LIEN_SITE_WEB') }}" placeholder="https://..." class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                    @error('RAID_LIEN_SITE_WEB') <div class="text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                <label class="block text-sm font-semibold">Contact (email ou téléphone) <span class="text-red-600">*</span></label>
                <input type="text" 
                    name="RAID_CONTACT" 
                    id="RAID_CONTACT" 
                    value="{{ old('RAID_CONTACT') }}" 
                    placeholder="email@exemple.com ou 06 12 34 56 78" 
                    class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2 @error('RAID_CONTACT') border-2 border-red-500 @enderror">
                    <div class="text-sm text-gray-600 mt-1">Indiquez un email ou un numéro de téléphone.</div>
                    @error('RAID_CONTACT') 
                    <div class="text-red-600 mt-1">{{ $message }}</div> 
                     @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold">Illustration (image, max 2MB) <small class="text-gray-500">(optionnel)</small></label>
                    <div class="mt-1 flex items-center gap-3">
                        <input type="file" name="RAID_ILLUSTRATION" id="RAID_ILLUSTRATION" accept="image/*" class="hidden">
                        <button type="button" id="choose-illustration" class="rounded-md border px-3 py-2 bg-white hover:bg-gray-50">Parcourir...</button>
                        <span id="illustration-filename" class="text-sm text-gray-600">{{ old('RAID_ILLUSTRATION') }}</span>
                    </div>
                    @error('RAID_ILLUSTRATION') <div class="text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold">Emplacement du raid <span class="text-red-600">*</span></label>
                    <div class="mt-2 flex gap-2 items-center">
                        <button type="button" id="use-location" class="rounded-md bg-blue-600 text-white px-3 py-2">Utiliser ma position</button>
                    </div>
                    <div id="addr-error" class="text-sm text-red-600 mt-1" style="display:none"></div>
                    <div id="map" data-lat="{{ old('RAID_LATITUDE', '') }}" data-lng="{{ old('RAID_LONGITUDE', '') }}" class="mt-2 rounded-md overflow-hidden" style="height:300px"></div>
                    <div class="mt-2 text-sm text-gray-600">Cliquez sur la carte pour placer un marqueur, ou déplacez le marqueur pour affiner la position.</div>
                    <div class="mt-2 grid grid-cols-2 gap-3">
                        <input type="hidden" name="RAID_LATITUDE" id="RAID_LATITUDE" value="{{ old('RAID_LATITUDE') }}">
                        <input type="hidden" name="RAID_LONGITUDE" id="RAID_LONGITUDE" value="{{ old('RAID_LONGITUDE') }}">
                        <div class="text-sm">Latitude: <span id="lat-display">{{ old('RAID_LATITUDE', '') }}</span></div>
                        <div class="text-sm">Longitude: <span id="lng-display">{{ old('RAID_LONGITUDE', '') }}</span></div>
                    </div>
                    @error('RAID_LATITUDE') <div class="text-red-600 mt-1">{{ $message }}</div> @enderror
                    @error('RAID_LONGITUDE') <div class="text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="flex justify-end">
                    <button class="rounded-md bg-black px-4 py-2 text-white">Créer</button>
                </div>
            </div>
        </form>
    </div>
    
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>

<style>
    #map { width: 100%; height: 300px; }
    #map .leaflet-tile, #map img.leaflet-tile { display:block; image-rendering:auto; }
</style>
@endpush

@push('scripts')
<script>
        (function(){
            const clubSelect = document.getElementById('CLU_NUM');
            const responsibleSelect = document.getElementById('INS_ID');

            function filter() {
                const club = clubSelect.value;
                for (const opt of responsibleSelect.options) {
                    const belongs = opt.getAttribute('data-club') === club;
                    opt.style.display = belongs ? '' : 'none';
                }
                if (responsibleSelect.selectedOptions.length) {
                    const sel = responsibleSelect.selectedOptions[0];
                    if (sel.style.display === 'none') {
                        responsibleSelect.selectedIndex = 0;
                    }
                }
            }

            clubSelect.addEventListener('change', filter);
            document.addEventListener('DOMContentLoaded', filter);
        })();
    </script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('map');
    if (!el) return;

    const defaultLat = 46.5;
    const defaultLng = 2.0;

    const lat = parseFloat(String(el.dataset.lat || '').replace(',', '.').trim());
    const lng = parseFloat(String(el.dataset.lng || '').replace(',', '.').trim());

    const initialLat = (Number.isFinite(lat) ? lat : defaultLat);
    const initialLng = (Number.isFinite(lng) ? lng : defaultLng);

    const map = L.map('map', { scrollWheelZoom: false, minZoom: 2, maxZoom: 19 }).setView([initialLat, initialLng], 6);

    const tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        noWrap: true,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let marker = null;
    function updateMarker(lat, lng) {
        if (!marker) {
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
            marker.on('dragend', function () {
                const p = marker.getLatLng();
                setCoords(p.lat, p.lng);
            });
        } else {
            marker.setLatLng([lat, lng]);
        }
        setCoords(lat, lng);
    }

    function setCoords(lat, lng) {
        document.getElementById('RAID_LATITUDE').value = String(lat);
        document.getElementById('RAID_LONGITUDE').value = String(lng);
        document.getElementById('lat-display').textContent = String(lat);
        document.getElementById('lng-display').textContent = String(lng);
    }

    if (Number.isFinite(lat) && Number.isFinite(lng)) {
        updateMarker(lat, lng);
        map.setView([lat, lng], 13);
    }

    map.on('click', function(e) {
        updateMarker(e.latlng.lat, e.latlng.lng);
    });

    const addrError = document.getElementById('addr-error');

    const useBtn = document.getElementById('use-location');
    if (useBtn) {
        useBtn.addEventListener('click', function () {
            addrError.style.display = 'none';
            if (!navigator.geolocation) {
                addrError.textContent = 'Géolocalisation non supportée par ce navigateur.';
                addrError.style.display = 'block';
                return;
            }
            useBtn.disabled = true;
            navigator.geolocation.getCurrentPosition(function(pos) {
                const lat = pos.coords.latitude;
                const lon = pos.coords.longitude;
                updateMarker(lat, lon);
                map.setView([lat, lon], 14);
                useBtn.disabled = false;
            }, function(err) {
                addrError.textContent = 'Impossible d\'obtenir votre position: ' + (err.message || 'erreur');
                addrError.style.display = 'block';
                useBtn.disabled = false;
            });
        });
    }

    setTimeout(() => map.invalidateSize(), 150);
    window.addEventListener('resize', () => setTimeout(() => map.invalidateSize(), 200));
});
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fileInput = document.getElementById('RAID_ILLUSTRATION');
        const btn = document.getElementById('choose-illustration');
        const label = document.getElementById('illustration-filename');

        if (!fileInput || !btn) return;

        btn.addEventListener('click', function () {
            fileInput.click();
        });

        fileInput.addEventListener('change', function (ev) {
            const f = ev.target.files && ev.target.files[0];
            label.textContent = f ? f.name : '';
        });
    });
</script>
@endpush
@endsection