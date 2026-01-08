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

                if(res.ok) location.reload();
                else this.flash = { type: 'error', message: json.message || 'Erreur lors de la sauvegarde.' };

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

                if(res.ok) location.reload();
                else this.flash = { type: 'error', message: json.message || 'Erreur lors de la création.' };

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

    <!-- MODAL EDIT (comme profil) -->
    <div
        x-cloak
        x-show="openEdit"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        @keydown.escape.window="openEdit = false"
    >
        <div class="absolute inset-0 bg-black/50" @click="openEdit = false"></div>

        <div class="relative w-full max-w-xl rounded-2xl bg-white shadow-xl border p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold">Modifier le club</h3>
                    <p class="text-sm text-slate-600 mt-1">Modifie les informations puis enregistre.</p>
                </div>

                <button
                    type="button"
                    @click="openEdit = false"
                    class="rounded-xl px-3 py-2 text-sm font-semibold hover:bg-slate-100 hover:cursor-pointer"
                >
                    Fermer
                </button>
            </div>

            <template x-if="editingClub">
                <form class="mt-6 space-y-4" @submit.prevent="submitClub()">
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Nom</label>
                        <input x-model="editingClub.CLU_NOM" class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-400" />
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Adresse</label>
                        <input x-model="editingClub.CLU_ADRESSE" class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-400" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            {{-- limited in 5 chars and only digits --}}
                            <label class="text-sm font-semibold text-slate-700">Code postal</label>
                            <input x-model="editingClub.CLU_CODE_POSTAL" maxlength="5" minlength="5" pattern="[0-9]{5}" inputmode="numeric" title="Veuillez entrer 5 chiffres" oninput="this.value = this.value.replace(/\D/g, '').slice(0,5)" class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-400"/>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-slate-700">Ville</label>
                            <input x-model="editingClub.CLU_VILLE" class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-400" />
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Responsable (licencié)</label>
                        <select x-model="editingClub.INS_ID" class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-400 bg-white">
                            <option value="">— Aucun —</option>
                            @foreach($licensed as $u)
                                <option value="{{ $u->INS_ID }}">
                                    {{ $u->INS_NOM }} {{ $u->INS_PRENOM }} ({{ $u->INS_NUM_LICENCE }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button
                            type="button"
                            @click="openEdit = false"
                            class="rounded-xl border px-4 py-2 text-sm font-semibold hover:bg-slate-50 hover:cursor-pointer"
                        >
                            Annuler
                        </button>

                        <button
                            type="submit"
                            class="rounded-xl bg-slate-900 px-4 py-2 text-white text-sm font-semibold hover:bg-slate-800 hover:cursor-pointer"
                        >
                            Enregistrer
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
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        @keydown.escape.window="openCreate = false"
    >
        <div class="absolute inset-0 bg-black/50" @click="openCreate = false"></div>

        <div class="relative w-full max-w-xl rounded-2xl bg-white shadow-xl border p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold">Créer un club</h3>
                    <p class="text-sm text-slate-600 mt-1">Renseigne les infos puis valide.</p>
                </div>

                <button
                    type="button"
                    @click="openCreate = false"
                        class="rounded-xl px-3 py-2 text-sm font-semibold hover:bg-slate-100 hover:cursor-pointer"
                >
                    Fermer
                </button>
            </div>

            <form class="mt-6 space-y-4" @submit.prevent="submitNewClub()">
                <div>
                    <label class="text-sm font-semibold text-slate-700">Nom</label>
                    <input x-model="newClub.CLU_NOM" class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-400" />
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Adresse</label>
                    <input x-model="newClub.CLU_ADRESSE" class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-400" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Code postal</label>
                        <input x-model="newClub.CLU_CODE_POSTAL" maxlength="5" minlength="5" pattern="[0-9]{5}" inputmode="numeric" title="Veuillez entrer 5 chiffres" oninput="this.value = this.value.replace(/\D/g, '').slice(0,5)" class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-400" />
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Ville</label>
                        <input x-model="newClub.CLU_VILLE" class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-400" />
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Responsable (licencié)</label>
                    <select x-model="newClub.INS_ID" class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-400 bg-white">
                        <option value="">— Aucun —</option>
                        @foreach($licensed as $u)
                            <option value="{{ $u->INS_ID }}">
                                {{ $u->INS_NOM }} {{ $u->INS_PRENOM }} ({{ $u->INS_NUM_LICENCE }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                            @click="openCreate = false"
                            class="rounded-xl border px-4 py-2 text-sm font-semibold hover:bg-slate-50 hover:cursor-pointer"
                    >
                        Annuler
                    </button>

                    <button
                        type="submit"
                            class="rounded-xl bg-green-600 px-4 py-2 text-white text-sm font-semibold hover:bg-green-500 hover:cursor-pointer"
                    >
                        Créer
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
