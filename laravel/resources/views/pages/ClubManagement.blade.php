@extends('layouts.app')
@section('title', 'Gestion des clubs')
@section('content')

<div x-data="{
        active: 'clubs',
        openClubEdit: false,
        openCreateClub: false,
        openInscritEdit: false,
        editingClub: null,
        newClub: { CLU_NOM: '', CLU_ADRESSE: '', CLU_CODE_POSTAL: '', CLU_VILLE: '', INS_ID: null },
        editingInscrit: null,
        setClub(club){ this.editingClub = JSON.parse(JSON.stringify(club)); this.openClubEdit = true; },
        setInscrit(inscrit){ this.editingInscrit = JSON.parse(JSON.stringify(inscrit)); this.openInscritEdit = true; },
        async submitClub(){
            if(!this.editingClub) return;
            try{
                const token = document.querySelector('meta[name=csrf-token]').getAttribute('content');
                const res = await fetch(`/clubs/${this.editingClub.CLU_ID}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify(this.editingClub)
                });
                if(res.ok) location.reload();
                else alert('Erreur lors de la sauvegarde.');
            }catch(e){ console.error(e); alert('Erreur réseau'); }
        },
        async submitNewClub(){
            try{
                const token = document.querySelector('meta[name=csrf-token]').getAttribute('content');
                const res = await fetch(`/clubs`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify(this.newClub)
                });
                if(res.ok) location.reload();
                else alert('Erreur lors de la création.');
            }catch(e){ console.error(e); alert('Erreur réseau'); }
        },
        async submitInscrit(){
            if(!this.editingInscrit) return;
            try{
                const token = document.querySelector('meta[name=csrf-token]').getAttribute('content');
                const res = await fetch(`/inscrits/${this.editingInscrit.INS_ID}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify(this.editingInscrit)
                });
                if(res.ok) location.reload();
                else alert('Erreur lors de la sauvegarde.');
            }catch(e){ console.error(e); alert('Erreur réseau'); }
        }
    }"
    class="min-h-screen py-12 px-4">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Gestion des clubs</h1>
            <div class="flex items-center gap-2">
                <button @click="active='clubs'" :class="{'bg-slate-900 text-white': active==='clubs'}" class="rounded px-3 py-2">Clubs</button>
                <button @click="active='inscrits'" :class="{'bg-slate-900 text-white': active==='inscrits'}" class="rounded px-3 py-2">Inscrits</button>
            </div>
        </div>

        <div x-show="active==='clubs'">
            <div class="flex justify-end mb-4">
                <button @click="openCreateClub = true" class="rounded-xl bg-green-600 px-4 py-2 text-white text-sm font-semibold hover:bg-green-500">Ajouter un club</button>
            </div>

            @if(isset($clubs) && $clubs->count())
                <div class="grid gap-4">
                    @foreach($clubs as $club)
                        <div class="relative p-4 bg-white dark:bg-[#161615] border border-[#e3e3e0] rounded-sm shadow-sm">
                            <h2 class="text-lg font-semibold">{{ $club->CLU_NOM }}</h2>
                            @if($club->CLU_ADRESSE)
                                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $club->CLU_ADRESSE }}</p>
                            @endif
                            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $club->CLU_CODE_POSTAL }} {{ $club->CLU_VILLE }}</p>

                            <button @click="setClub(@json($club))" class="absolute top-3 right-3 shrink-0 rounded-xl bg-slate-900 px-3 py-1 text-white text-sm font-semibold hover:bg-slate-800">Modifier</button>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $clubs->links() }}
                </div>
            @else
                <p class="text-center text-[#706f6c]">Aucun club trouvé.</p>
            @endif
        </div>

        <div x-show="active==='inscrits'">
            @if(isset($inscrits) && $inscrits->count())
                <div class="overflow-x-auto bg-white rounded border">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2">Nom</th>
                                <th class="px-4 py-2">Téléphone</th>
                                <th class="px-4 py-2">Naissance</th>
                                <th class="px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inscrits as $inscrit)
                                <tr class="border-t">
                                    <td class="px-4 py-2">{{ $inscrit->INS_NOM }} {{ $inscrit->INS_PRENOM ?? '' }}</td>
                                    <td class="px-4 py-2">{{ $inscrit->INS_TEL }}</td>
                                    <td class="px-4 py-2">{{ $inscrit->INS_DDN }}</td>
                                    <td class="px-4 py-2">
                                        <button @click="setInscrit(@json($inscrit))" class="rounded bg-slate-900 px-3 py-1 text-white text-sm">Supprimer</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center text-[#706f6c]">Aucun inscrit trouvé.</p>
            @endif
        </div>
    </div>
    <!-- MODAL -->
    <div
        x-cloak
        x-show="openEdit"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
    >
        <div class="absolute inset-0 bg-black/50" @click="openEdit = false"></div>

        <div class="relative w-full max-w-xl rounded-2xl bg-white shadow-xl border p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold">Modifier le club</h3>
                <button @click="openEdit= false" class="rounded-xl px-3 py-2 text-sm font-semibold hover:bg-slate-100">
                    Fermer
                </button>
            </div>

            <div class="mt-4 text-sm text-slate-600">
                (mets ici ton formulaire d’édition du club)
            </div>
        </div>
    </div>
</div>
</div>

	@endsection

