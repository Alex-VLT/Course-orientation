@extends('layouts.app')
@section('title', 'Gestion des clubs')

@section('content')
<div
    class="min-h-screen py-12 px-4"
    x-data="{
        active: 'clubs',

        // modals (même logique que profil)
        openEdit: false,
        openCreate: false,
        openDelete: false,

        // data
        editingClub: null,
        newClub: { CLU_NOM: '', CLU_ADRESSE: '', CLU_CODE_POSTAL: '', CLU_VILLE: '', INS_ID: '' },

        deletingInscrit: null,

        // flash
        flash: { type: '', message: '' },

        setClub(club){
            this.editingClub = JSON.parse(JSON.stringify(club));
            // Ensure postal code is always a clean string of digits
            if (this.editingClub.CLU_CODE_POSTAL) {
                this.editingClub.CLU_CODE_POSTAL = String(this.editingClub.CLU_CODE_POSTAL).replace(/\D/g, '');
            }
            this.openEdit = true;
        },

        openCreateModal(){
            this.newClub = { CLU_NOM: '', CLU_ADRESSE: '', CLU_CODE_POSTAL: '', CLU_VILLE: '', INS_ID: '' };
            this.openCreate = true;
        },

        askDelete(inscrit){
            this.deletingInscrit = JSON.parse(JSON.stringify(inscrit));
            this.openDelete = true;
        },

        async submitClub(){
            if(!this.editingClub) return;

            try{
                const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');

                // Ensure postal code is clean before sending
                const payload = { ...this.editingClub };
                if (payload.CLU_CODE_POSTAL) {
                    payload.CLU_CODE_POSTAL = String(payload.CLU_CODE_POSTAL).replace(/\D/g, '');
                }

                const res = await fetch(`/clubs/${this.editingClub.CLU_NUM}`, {
                    method: 'PUT',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const json = await res.json().catch(() => ({}));

                if(res.ok) {
                    location.reload();
                } else if (json.errors) {
                    // Validation errors from Laravel
                    const errorMessages = Object.values(json.errors).flat().join('\n');
                    this.flash = { type: 'error', message: errorMessages };
                } else {
                    this.flash = { type: 'error', message: json.message || 'Erreur lors de la sauvegarde.' };
                }

            }catch(e){
                console.error(e);
                this.flash = { type: 'error', message: 'Erreur réseau.' };
            }
        },

        async submitNewClub(){
            try{
                const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');

                // Ensure postal code is clean before sending
                const payload = { ...this.newClub };
                if (payload.CLU_CODE_POSTAL) {
                    payload.CLU_CODE_POSTAL = String(payload.CLU_CODE_POSTAL).replace(/\D/g, '');
                }

                const res = await fetch(`/clubs`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const json = await res.json().catch(() => ({}));

                if(res.ok) {
                    location.reload();
                } else if (json.errors) {
                    // Validation errors from Laravel
                    const errorMessages = Object.values(json.errors).flat().join('\n');
                    this.flash = { type: 'error', message: errorMessages };
                } else {
                    this.flash = { type: 'error', message: json.message || 'Erreur lors de la création.' };
                }

            }catch(e){
                console.error(e);
                this.flash = { type: 'error', message: 'Erreur réseau.' };
            }
        },

        async confirmDelete(){
            if(!this.deletingInscrit) return;

            try{
                const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');

                const res = await fetch(`/inscrits/${this.deletingInscrit.INS_ID}`, {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    }
                });

                const json = await res.json().catch(() => ({}));

                if(res.ok) location.reload();
                else {
                    this.openDelete = false;
                    this.flash = { type: 'error', message: json.message || 'Suppression impossible.' };
                }

            }catch(e){
                console.error(e);
                this.openDelete = false;
                this.flash = { type: 'error', message: 'Erreur réseau.' };
            }
        }
    }"
>

    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Gestion des clubs</h1>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="active='clubs'"
                    :class="active==='clubs' ? 'bg-slate-900 text-white' : 'bg-white'"
                    class="rounded-xl border px-3 py-2 text-sm font-semibold hover:bg-slate-50 hover:cursor-pointer"
                >
                    Clubs
                </button>

                <button
                    type="button"
                    @click="active='inscrits'"
                    :class="active==='inscrits' ? 'bg-slate-900 text-white' : 'bg-white'"
                    class="rounded-xl border px-3 py-2 text-sm font-semibold hover:bg-slate-50 hover:cursor-pointer"
                >
                    Inscrits
                </button>
            </div>
        </div>

        <!-- Flash -->
        <template x-if="flash.message">
            <div
                class="mb-6 rounded-lg border px-4 py-3"
                :class="flash.type==='error' ? 'border-red-200 bg-red-50 text-red-800' : 'border-green-200 bg-green-50 text-green-800'"
                x-text="flash.message"
            ></div>
        </template>

        <!-- ONGLET CLUBS -->
        <div x-show="active==='clubs'">
            <div class="flex justify-end mb-4">
                <button
                    type="button"
                    @click="openCreateModal()"
                    class="rounded-xl bg-green-600 px-4 py-2 text-white text-sm font-semibold hover:bg-green-500 hover:cursor-pointer"
                >
                    Ajouter un club
                </button>
            </div>

            @if(isset($clubs) && $clubs->count())
                <div class="grid gap-4">
                    @foreach($clubs as $club)
                        <div class="relative p-4 bg-white border border-[#e3e3e0] rounded-xl shadow-sm">
                            <h2 class="text-lg font-semibold">{{ $club->CLU_NOM }}</h2>

                            @if(!empty($club->CLU_ADRESSE))
                                <p class="text-sm text-slate-600">{{ $club->CLU_ADRESSE }}</p>
                            @endif

                            <p class="text-sm text-slate-600">
                                {{ $club->CLU_CODE_POSTAL }} {{ $club->CLU_VILLE }}
                            </p>

                            <button
                                type="button"
                                @click='setClub(@json($club))'
                                class="absolute top-3 right-3 rounded-xl bg-slate-900 px-3 py-1 text-white text-sm font-semibold hover:bg-slate-800 hover:cursor-pointer"
                            >
                                Modifier
                            </button>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $clubs->links() }}
                </div>
            @else
                <p class="text-center text-slate-600">Aucun club trouvé.</p>
            @endif
        </div>

        <!-- ONGLET INSCRITS -->
        <div x-show="active==='inscrits'">
            @if(isset($inscrits) && $inscrits->count())
                <div class="overflow-x-auto bg-white rounded-xl border">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3">Nom</th>
                                <th class="px-4 py-3">Téléphone</th>
                                <th class="px-4 py-3">Naissance</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inscrits as $inscrit)
                                <tr class="border-t">
                                    <td class="px-4 py-3">{{ $inscrit->INS_NOM }} {{ $inscrit->INS_PRENOM ?? '' }}</td>
                                    <td class="px-4 py-3">{{ $inscrit->INS_TEL }}</td>
                                    <td class="px-4 py-3">{{ $inscrit->INS_NAISSANCE ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <button
                                            type="button"
                                            @click="askDelete({ INS_ID: {{ (int)$inscrit->INS_ID }}, INS_NOM: @js($inscrit->INS_NOM), INS_PRENOM: @js($inscrit->INS_PRENOM ?? '') })"
                                            class="rounded-xl bg-red-600 px-3 py-1 text-white text-sm font-semibold hover:bg-red-700 hover:cursor-pointer"
                                        >
                                            Supprimer
                                        </button>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $inscrits->links() }}
                </div>
            @else
                <p class="text-center text-slate-600">Aucun inscrit trouvé.</p>
            @endif
        </div>
    </div>

    <!-- MODAL EDIT -->
    <div
        x-cloak
        x-show="openEdit"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
        role="dialog"
        aria-modal="true"
        @keydown.escape.window="openEdit = false"
    >
        <div class="absolute inset-0" @click="openEdit = false"></div>

        <div 
            x-show="openEdit"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl"
        >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-slate-200 bg-gradient-to-r from-slate-50 to-slate-100 px-8 py-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">📝 Modifier le club</h2>
                    <p class="text-sm text-slate-500 mt-1">Mettez à jour les informations du club</p>
                </div>
                <button
                    type="button"
                    @click="openEdit = false"
                    class="rounded-lg p-2 text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition-colors"
                    aria-label="Fermer"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <template x-if="editingClub">
                <form class="space-y-0" @submit.prevent="submitClub()">
                    <!-- Contenu -->
                    <div class="px-8 py-6 space-y-6 max-h-[60vh] overflow-y-auto">
                        <!-- Section Informations générales -->
                        <div class="space-y-4">
                            <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Informations générales</h3>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Nom du club <span class="text-red-500">*</span></label>
                                <input 
                                    x-model="editingClub.CLU_NOM" 
                                    required 
                                    type="text"
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900 focus:border-transparent bg-white transition-all" 
                                    placeholder="Ex: Club de course"
                                />
                            </div>
                        </div>

                        <!-- Section Adresse -->
                        <div class="space-y-4 border-t border-slate-200 pt-6">
                            <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">📍 Localisation</h3>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Adresse <span class="text-red-500">*</span></label>
                                <input 
                                    x-model="editingClub.CLU_ADRESSE" 
                                    required 
                                    type="text"
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900 focus:border-transparent bg-white transition-all"
                                    placeholder="Ex: 123 rue de la Paix"
                                />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Code postal <span class="text-red-500">*</span></label>
                                    <input 
                                        x-model="editingClub.CLU_CODE_POSTAL" 
                                        required 
                                        maxlength="5" 
                                        minlength="5" 
                                        pattern="[0-9]{5}" 
                                        inputmode="numeric" 
                                        oninput="this.value = this.value.replace(/\D/g, '').slice(0,5)"
                                        class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900 focus:border-transparent bg-white transition-all"
                                        placeholder="75001"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Ville <span class="text-red-500">*</span></label>
                                    <input 
                                        x-model="editingClub.CLU_VILLE" 
                                        required 
                                        type="text"
                                        class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900 focus:border-transparent bg-white transition-all"
                                        placeholder="Ex: Paris"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Section Responsable -->
                        <div class="space-y-4 border-t border-slate-200 pt-6">
                            <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">👤 Responsable</h3>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Responsable du club (licencié) <span class="text-red-500">*</span></label>
                                <select 
                                    x-model="editingClub.INS_ID" 
                                    required 
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900 focus:border-transparent bg-white transition-all"
                                >
                                    <option value="">— Sélectionner un responsable —</option>
                                    @foreach($licensed as $u)
                                        <option value="{{ $u->INS_ID }}">
                                            {{ $u->INS_NOM }} {{ $u->INS_PRENOM }} ({{ $u->INS_NUM_LICENCE }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex justify-end gap-3 border-t border-slate-200 bg-slate-50 px-8 py-4 rounded-b-2xl">
                        <button
                            type="button"
                            @click="openEdit = false"
                            class="px-6 py-2 rounded-lg border border-slate-300 text-slate-700 font-medium hover:bg-slate-100 transition-colors"
                        >
                            Annuler
                        </button>
                        <button
                            type="submit"
                            class="px-6 py-2 rounded-lg bg-slate-900 text-white font-medium hover:bg-slate-800 transition-colors"
                        >
                            Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <!-- MODAL CREATE -->
    <div
        x-cloak
        x-show="openCreate"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
        role="dialog"
        aria-modal="true"
        @keydown.escape.window="openCreate = false"
    >
        <div class="absolute inset-0" @click="openCreate = false"></div>

        <div 
            x-show="openCreate"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl"
        >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-slate-200 bg-gradient-to-r from-green-50 to-green-100 px-8 py-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">➕ Créer un nouveau club</h2>
                    <p class="text-sm text-slate-500 mt-1">Remplissez les informations du club</p>
                </div>
                <button
                    type="button"
                    @click="openCreate = false"
                    class="rounded-lg p-2 text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition-colors"
                    aria-label="Fermer"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form class="space-y-0" @submit.prevent="submitNewClub()">
                <!-- Contenu -->
                <div class="px-8 py-6 space-y-6 max-h-[60vh] overflow-y-auto">
                    <!-- Section Informations générales -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Informations générales</h3>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Nom du club <span class="text-red-500">*</span></label>
                            <input 
                                x-model="newClub.CLU_NOM" 
                                required 
                                type="text"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white transition-all" 
                                placeholder="Ex: Club de course"
                            />
                        </div>
                    </div>

                    <!-- Section Adresse -->
                    <div class="space-y-4 border-t border-slate-200 pt-6">
                        <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">📍 Localisation</h3>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Adresse <span class="text-red-500">*</span></label>
                            <input 
                                x-model="newClub.CLU_ADRESSE" 
                                required 
                                type="text"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white transition-all"
                                placeholder="Ex: 123 rue de la Paix"
                            />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Code postal <span class="text-red-500">*</span></label>
                                <input 
                                    x-model="newClub.CLU_CODE_POSTAL" 
                                    required 
                                    maxlength="5" 
                                    minlength="5" 
                                    pattern="[0-9]{5}" 
                                    inputmode="numeric" 
                                    oninput="this.value = this.value.replace(/\D/g, '').slice(0,5)"
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white transition-all"
                                    placeholder="75001"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Ville <span class="text-red-500">*</span></label>
                                <input 
                                    x-model="newClub.CLU_VILLE" 
                                    required 
                                    type="text"
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white transition-all"
                                    placeholder="Ex: Paris"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Section Responsable -->
                    <div class="space-y-4 border-t border-slate-200 pt-6">
                        <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">👤 Responsable</h3>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Responsable du club (licencié) <span class="text-red-500">*</span></label>
                            <select 
                                x-model="newClub.INS_ID" 
                                required 
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white transition-all"
                            >
                                <option value="">— Sélectionner un responsable —</option>
                                @foreach($licensed as $u)
                                    <option value="{{ $u->INS_ID }}">
                                        {{ $u->INS_NOM }} {{ $u->INS_PRENOM }} ({{ $u->INS_NUM_LICENCE }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex justify-end gap-3 border-t border-slate-200 bg-slate-50 px-8 py-4 rounded-b-2xl">
                    <button
                        type="button"
                        @click="openCreate = false"
                        class="px-6 py-2 rounded-lg border border-slate-300 text-slate-700 font-medium hover:bg-slate-100 transition-colors"
                    >
                        Annuler
                    </button>
                    <button
                        type="submit"
                        class="px-6 py-2 rounded-lg bg-green-600 text-white font-medium hover:bg-green-700 transition-colors"
                    >
                        Créer le club
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL DELETE INSCRIT -->
    <div
        x-cloak
        x-show="openDelete"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        @keydown.escape.window="openDelete = false"
    >
        <div class="absolute inset-0 bg-black/50" @click="openDelete = false"></div>

        <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-xl border p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-red-700">Supprimer un inscrit</h3>
                    <p class="text-sm text-slate-600 mt-1">Cette action est définitive.</p>
                </div>

                <button
                    type="button"
                    @click="openDelete = false"
                    class="rounded-xl px-3 py-2 text-sm font-semibold hover:bg-slate-100"
                >
                    Fermer
                </button>
            </div>

                        class="rounded-xl px-4 py-2 text-sm font-semibold hover:bg-slate-50 hover:cursor-pointer"
                <span class="text-slate-600">Confirmer la suppression de :</span>
                <div class="mt-2 rounded-xl border bg-slate-50 px-4 py-3 font-semibold">
                    <span x-text="deletingInscrit ? (deletingInscrit.INS_NOM + ' ' + (deletingInscrit.INS_PRENOM || '')) : ''"></span>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button
                    type="button"
                    @click="openDelete = false"
                    class="rounded-xl border px-4 py-2 text-sm font-semibold hover:bg-slate-50"
                >
                    Annuler
                </button>

                <button
                    type="button"
                    @click="confirmDelete()"
                        class="rounded-xl bg-red-600 px-4 py-2 text-white text-sm font-semibold hover:bg-red-700 hover:cursor-pointer"
                >
                    Oui, supprimer
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
