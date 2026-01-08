@extends('layouts.app')

@section('title', 'Créer un Raid - L\'Embuscade')

@section('content')
{{-- Conteneur Principal : Fond Beige + Blobs + Centrage vertical strict --}}
<div class="h-screen w-full flex items-center justify-center bg-[#f7f5e6] px-4 py-4 relative overflow-hidden font-sans text-slate-800">

    {{-- Formes décoratives d'arrière-plan --}}
    <div class="absolute top-[-10%] left-[-10%] w-[40rem] h-[40rem] bg-[#7DC2A5] rounded-full mix-blend-multiply filter blur-[100px] opacity-20 animate-blob"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40rem] h-[40rem] bg-yellow-200 rounded-full mix-blend-multiply filter blur-[100px] opacity-30 animate-blob animation-delay-2000"></div>

    {{-- CARTE PRINCIPALE : Large mais compacte en hauteur --}}
    <div class="w-full max-w-6xl bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl border border-white/50 flex flex-col max-h-full overflow-hidden">
        
        {{-- EN-TÊTE FIXE --}}
        <div class="px-8 py-5 border-b border-slate-100 flex justify-between items-center bg-white/50">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 flex items-center gap-3">
                    <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-[#7DC2A5] text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    </span>
                    Nouveau Raid
                </h1>
                <p class="text-xs text-slate-500 mt-1 pl-14">Remplissez les informations pour lancer l'événement.</p>
            </div>
            
            {{-- Indicateur d'erreurs (si besoin) --}}
            <div id="date-validation-errors" class="hidden text-xs font-bold text-red-600 bg-red-50 px-3 py-2 rounded-lg border border-red-100 animate-pulse"></div>
        </div>

        {{-- CONTENU SCROLLABLE (Si l'écran est vraiment petit) --}}
        <div class="overflow-y-auto p-6 md:p-8">
            <form action="{{ route('raids.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    {{-- COLONNE GAUCHE (INPUTS) --}}
                    <div class="lg:col-span-7 space-y-5">
                        
                        {{-- Ligne 1 : Nom (Large) --}}
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase ml-1">Nom du raid <span class="text-red-500">*</span></label>
                            <input name="RAID_NOM" value="{{ old('RAID_NOM') }}" class="w-full mt-1 rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:bg-white focus:border-[#7DC2A5] focus:ring-1 focus:ring-[#7DC2A5] transition-all" placeholder="Ex: La Grande Traversée" required />
                            @error('RAID_NOM') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                        </div>

                        {{-- Ligne 2 : Dates Raid (Grid interne) --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase ml-1">Date Début <span class="text-red-500">*</span></label>
                                <input name="RAID_DATE_DEBUT" id="RAID_DATE_DEBUT" type="date" value="{{ old('RAID_DATE_DEBUT') }}" min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" required class="w-full mt-1 rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-[#7DC2A5] focus:ring-1 focus:ring-[#7DC2A5]" />
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase ml-1">Date Fin <span class="text-red-500">*</span></label>
                                <input name="RAID_DATE_FIN" id="RAID_DATE_FIN" type="date" value="{{ old('RAID_DATE_FIN') }}" required class="w-full mt-1 rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-[#7DC2A5] focus:ring-1 focus:ring-[#7DC2A5]" />
                            </div>
                        </div>

                        {{-- Ligne 3 : Organisation --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase ml-1">Club <span class="text-red-500">*</span></label>
                                <select name="CLU_NUM" id="CLU_NUM" class="w-full mt-1 rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-[#7DC2A5] focus:ring-1 focus:ring-[#7DC2A5]">
                                    @foreach($clubs as $club)
                                        <option value="{{ $club->CLU_NUM }}" {{ old('CLU_NUM') == $club->CLU_NUM ? 'selected' : '' }}>{{ $club->CLU_NOM }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase ml-1">Responsable <span class="text-red-500">*</span></label>
                                <select name="INS_ID" id="INS_ID" class="w-full mt-1 rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-[#7DC2A5] focus:ring-1 focus:ring-[#7DC2A5]">
                                    @foreach ($members as $m)
                                        <option value="{{ $m->INS_ID }}" data-club="{{ $m->CLU_NUM }}" {{ old('INS_ID') == $m->INS_ID ? 'selected' : '' }}>
                                            {{ $m->INS_PRENOM }} {{ $m->INS_NOM }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Ligne 4 : Inscriptions --}}
                        <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-100 grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase">Début Inscr.</label>
                                <input name="RAID_DATE_DEBUT_INSCRI" id="RAID_DATE_DEBUT_INSCRI" type="date" value="{{ old('RAID_DATE_DEBUT_INSCRI') }}" min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" required class="w-full mt-1 rounded-lg border-slate-200 bg-white px-2 py-1.5 text-sm focus:border-[#7DC2A5] focus:ring-1 focus:ring-[#7DC2A5]" />
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase">Fin Inscr.</label>
                                <input name="RAID_DATE_FIN_INSCRI" id="RAID_DATE_FIN_INSCRI" type="date" value="{{ old('RAID_DATE_FIN_INSCRI') }}" required class="w-full mt-1 rounded-lg border-slate-200 bg-white px-2 py-1.5 text-sm focus:border-[#7DC2A5] focus:ring-1 focus:ring-[#7DC2A5]" />
                            </div>
                        </div>

                        {{-- Ligne 5 : Contact & Web --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase ml-1">Contact <span class="text-red-500">*</span></label>
                                <input type="text" name="RAID_CONTACT" value="{{ old('RAID_CONTACT') }}" placeholder="Email ou Tél" class="w-full mt-1 rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-[#7DC2A5] focus:ring-1 focus:ring-[#7DC2A5]" required />
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase ml-1">Site Web</label>
                                <input name="RAID_LIEN_SITE_WEB" value="{{ old('RAID_LIEN_SITE_WEB') }}" placeholder="https://" class="w-full mt-1 rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-[#7DC2A5] focus:ring-1 focus:ring-[#7DC2A5]" />
                            </div>
                        </div>

                        {{-- Illustration (Compact) --}}
                        <div class="flex items-center gap-4">
                            <label class="text-xs font-bold text-slate-500 uppercase flex-shrink-0">Illustration</label>
                            <div class="flex-grow flex items-center gap-2 p-1.5 bg-slate-50 rounded-xl border border-slate-200">
                                <button type="button" id="choose-illustration" class="px-3 py-1.5 bg-white rounded-lg border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100">Parcourir</button>
                                <span id="illustration-filename" class="text-xs text-slate-400 italic truncate">{{ old('RAID_ILLUSTRATION', 'Aucun fichier choisi') }}</span>
                                <input type="file" name="RAID_ILLUSTRATION" id="RAID_ILLUSTRATION" accept="image/*" class="hidden">
                            </div>
                        </div>

                    </div>

                    {{-- COLONNE DROITE (MAP) --}}
                    <div class="lg:col-span-5 flex flex-col h-full">
                        
                        <div class="flex justify-between items-end mb-2">
                            <label class="text-xs font-bold text-slate-500 uppercase ml-1">Localisation <span class="text-red-500">*</span></label>
                            <button type="button" id="use-location" class="text-[10px] font-bold text-white bg-slate-800 px-3 py-1 rounded-full hover:bg-black transition-colors flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                Ma position
                            </button>
                        </div>

                        <div id="addr-error" class="hidden text-xs text-red-600 mb-2"></div>

                        {{-- Carte --}}
                        <div id="map" data-lat="{{ old('RAID_LATITUDE', '') }}" data-lng="{{ old('RAID_LONGITUDE', '') }}" class="flex-grow min-h-[300px] lg:min-h-[auto] w-full rounded-2xl border-2 border-white shadow-lg overflow-hidden z-0"></div>
                        
                        <div class="mt-3 grid grid-cols-2 gap-3">
                            <div class="bg-slate-50 rounded-lg p-2 text-center border border-slate-100">
                                <div class="text-[10px] text-slate-400 uppercase font-bold">Latitude</div>
                                <div id="lat-display" class="font-mono text-sm font-bold text-slate-700">{{ old('RAID_LATITUDE', '-') }}</div>
                                <input type="hidden" name="RAID_LATITUDE" id="RAID_LATITUDE" value="{{ old('RAID_LATITUDE') }}">
                            </div>
                            <div class="bg-slate-50 rounded-lg p-2 text-center border border-slate-100">
                                <div class="text-[10px] text-slate-400 uppercase font-bold">Longitude</div>
                                <div id="lng-display" class="font-mono text-sm font-bold text-slate-700">{{ old('RAID_LONGITUDE', '-') }}</div>
                                <input type="hidden" name="RAID_LONGITUDE" id="RAID_LONGITUDE" value="{{ old('RAID_LONGITUDE') }}">
                            </div>
                        </div>

                        {{-- Bouton Valider (En bas à droite) --}}
                        <button type="submit" class="mt-auto w-full bg-[#7DC2A5] hover:bg-[#68a88d] text-white font-extrabold rounded-2xl py-4 shadow-lg shadow-[#7DC2A5]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 text-sm uppercase tracking-wider mt-6">
                            Valider la création
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Leaflet Styles & Scripts --}}
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #map .leaflet-tile { display:block; image-rendering:auto; }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    // 1. Filtrage dynamique Responsable / Club
    (function(){
        const clubSelect = document.getElementById('CLU_NUM');
        const responsibleSelect = document.getElementById('INS_ID');

        function filter() {
            if(!clubSelect || !responsibleSelect) return;
            const club = clubSelect.value;
            let hasVisible = false;
            
            for (const opt of responsibleSelect.options) {
                const belongs = opt.getAttribute('data-club') === club;
                opt.style.display = belongs ? '' : 'none';
                if(belongs && !hasVisible && !responsibleSelect.value) {
                    // Preselect first if empty
                }
            }
            if (responsibleSelect.selectedOptions.length && responsibleSelect.selectedOptions[0].style.display === 'none') {
                responsibleSelect.value = ""; 
            }
        }

        if(clubSelect) clubSelect.addEventListener('change', filter);
        document.addEventListener('DOMContentLoaded', filter);
    })();

    // 2. Gestion de la Carte Leaflet
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

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
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
            document.getElementById('RAID_LATITUDE').value = lat;
            document.getElementById('RAID_LONGITUDE').value = lng;
            document.getElementById('lat-display').textContent = lat.toFixed(5);
            document.getElementById('lng-display').textContent = lng.toFixed(5);
        }

        if (Number.isFinite(lat) && Number.isFinite(lng)) {
            updateMarker(lat, lng);
            map.setView([lat, lng], 13);
        }

        map.on('click', function(e) {
            updateMarker(e.latlng.lat, e.latlng.lng);
        });

        const useBtn = document.getElementById('use-location');
        const addrError = document.getElementById('addr-error');
        
        if (useBtn) {
            useBtn.addEventListener('click', function () {
                addrError.style.display = 'none';
                if (!navigator.geolocation) {
                    addrError.textContent = 'Géolocalisation non supportée.';
                    addrError.style.display = 'block';
                    return;
                }
                useBtn.disabled = true;
                useBtn.classList.add('opacity-50');
                
                navigator.geolocation.getCurrentPosition(function(pos) {
                    const lat = pos.coords.latitude;
                    const lon = pos.coords.longitude;
                    updateMarker(lat, lon);
                    map.setView([lat, lon], 14);
                    useBtn.disabled = false;
                    useBtn.classList.remove('opacity-50');
                }, function(err) {
                    addrError.textContent = 'Erreur position.';
                    addrError.style.display = 'block';
                    useBtn.disabled = false;
                    useBtn.classList.remove('opacity-50');
                });
            });
        }

        setTimeout(() => map.invalidateSize(), 200);
    });

    // 3. Validation des Dates
    document.addEventListener('DOMContentLoaded', function () {
        const raidStart = document.getElementById('RAID_DATE_DEBUT');
        const raidEnd = document.getElementById('RAID_DATE_FIN');
        const insStart = document.getElementById('RAID_DATE_DEBUT_INSCRI');
        const insEnd = document.getElementById('RAID_DATE_FIN_INSCRI');
        const errorsEl = document.getElementById('date-validation-errors');
        const form = document.querySelector('form');

        if (!raidStart || !raidEnd || !insStart || !insEnd || !form || !errorsEl) return;

        function parseYMD(value) {
            const parts = String(value || '').split('-');
            if (parts.length !== 3) return null;
            return new Date(parts[0], parts[1] - 1, parts[2]);
        }

        function validateDates() {
            const msgs = [];
            const today = new Date();
            today.setHours(0,0,0,0);

            const rStart = parseYMD(raidStart.value);
            const rEnd = parseYMD(raidEnd.value);
            const iStart = parseYMD(insStart.value);
            const iEnd = parseYMD(insEnd.value);

            if (rStart && rStart < today) msgs.push('Le raid ne peut pas commencer dans le passé.');
            if (iStart && iStart < today) msgs.push('Les inscriptions ne peuvent pas commencer dans le passé.');
            if (iEnd && rStart && iEnd >= rStart) msgs.push('La fin des inscriptions doit être avant le début du raid.');
            if (rEnd && rStart && rEnd < rStart) msgs.push('La fin du raid doit être après le début.');

            return msgs;
        }

        function updateDependentDates() {
            if (!raidStart.value) return;
            raidEnd.min = raidStart.value; 
            
            const r = parseYMD(raidStart.value);
            if (r) {
                const prev = new Date(r.getTime());
                prev.setDate(prev.getDate() - 1);
                const y = prev.getFullYear();
                const m = String(prev.getMonth() + 1).padStart(2, '0');
                const d = String(prev.getDate()).padStart(2, '0');
                insEnd.max = `${y}-${m}-${d}`;
            }
        }

        raidStart.addEventListener('change', updateDependentDates);
        updateDependentDates();

        form.addEventListener('submit', function (ev) {
            const msgs = validateDates();
            if (msgs.length) {
                ev.preventDefault();
                errorsEl.innerHTML = msgs.join('<br>');
                errorsEl.classList.remove('hidden');
            } else {
                errorsEl.classList.add('hidden');
            }
        });
    });

    // 4. Input Fichier Custom
    document.addEventListener('DOMContentLoaded', function () {
        const fileInput = document.getElementById('RAID_ILLUSTRATION');
        const btn = document.getElementById('choose-illustration');
        const label = document.getElementById('illustration-filename');

        if (!fileInput || !btn) return;

        btn.addEventListener('click', function () { fileInput.click(); });

        fileInput.addEventListener('change', function (ev) {
            const f = ev.target.files && ev.target.files[0];
            label.textContent = f ? f.name : 'Aucun fichier choisi';
        });
    });
</script>
@endpush
@endsection