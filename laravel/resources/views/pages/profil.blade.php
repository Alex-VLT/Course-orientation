@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8"
     x-data="{
        openEdit: {{ $errors->profileUpdate->any() ? 'true' : 'false' }},
        openResult: null
     }">

    @if (session('success'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 rounded-2xl border bg-white shadow-sm p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">
                        {{ $user->INS_PRENOM }} {{ $user->INS_NOM }}
                    </h1>
                    <p class="text-slate-600 mt-1">{{ $user->INS_MAIL }}</p>

                    <div class="mt-4 space-y-1 text-sm text-slate-700">
                        <div><span class="font-semibold">Téléphone :</span> {{ $user->INS_TEL }}</div>
                        <div><span class="font-semibold">Adresse :</span> {{ $user->INS_ADRESSE }}, {{ $user->INS_CODE_PO }} {{ $user->INS_VILLE }}</div>
                        <div><span class="font-semibold">Naissance :</span> {{ \Carbon\Carbon::parse($user->INS_NAISSANCE)->format('d/m/Y') }}</div>
                        <div>
                            <span class="font-semibold">Licence :</span>
                            {{ $user->INS_NUM_LICENCE ? $user->INS_NUM_LICENCE : '—' }}
                        </div>
                        {{-- AJOUT AFFICHAGE CLUB --}}
                        <div>
                            <span class="font-semibold">Club :</span>
                            {{ $user->CLU_NOM ?? 'Aucun club' }}
                        </div>
                    </div>
                </div>

                <button
                    type="button"
                    @click="openEdit = true"
                    class="shrink-0 rounded-xl bg-slate-900 px-4 py-2 text-white text-sm font-semibold hover:bg-slate-800">
                    Modifier
                </button>
            </div>
        </div>

        <div class="rounded-2xl border bg-white shadow-sm p-6">
            <h2 class="text-lg font-bold">Statistiques</h2>

            <div class="mt-4 space-y-3">
                <div class="rounded-xl bg-slate-50 p-4">
                    <div class="text-sm text-slate-600">Courses réalisées / inscrites</div>
                    <div class="text-2xl font-bold">{{ $stats['nbCourses'] }}</div>
                </div>

                <div class="rounded-xl bg-slate-50 p-4">
                    <div class="text-sm text-slate-600">Résultats</div>
                    <div class="text-xl font-bold">
                        Podium : {{ $stats['nbPodiums'] }} / Victoire : {{ $stats['nbVictoires'] }}
                    </div>
                </div>

                <div class="rounded-xl bg-slate-50 p-4">
                    <div class="text-sm text-slate-600">Points accumulés</div>
                    <div class="text-2xl font-bold">{{ $stats['points'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-8 space-y-8">

        <div class="rounded-2xl border bg-white shadow-sm p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold">Mes courses (à venir / en cours)</h2>
                <span class="text-sm text-slate-600">{{ $coursesAVenir->count() }} course(s)</span>
            </div>

            <div class="mt-4 divide-y">
                @forelse ($coursesAVenir as $c)
                    <div class="py-4 flex items-start justify-between gap-4">
                        <div>
                            <div class="font-semibold text-slate-900">
                                {{ $c->COU_NOM }}
                            </div>
                            <div class="text-sm text-slate-600 mt-1">
                                {{ \Carbon\Carbon::parse($c->COU_DATE_DEPART)->format('d/m/Y H:i') }}
                                → {{ \Carbon\Carbon::parse($c->COU_DATE_FIN)->format('d/m/Y H:i') }}
                            </div>
                            <div class="text-xs text-slate-600 mt-2">
                                Type : {{ $c->TYP_LABEL ?? '—' }} • Durée : {{ $c->COU_DUREE }} min • Difficulté : {{ $c->COU_DIFFICULTE }}
                            </div>
                        </div>

                        <span class="shrink-0 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1 text-xs font-semibold">
                            Inscrit
                        </span>
                    </div>
                @empty
                    <div class="py-6 text-slate-600">Aucune course à venir.</div>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border bg-white shadow-sm p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold">Mes courses (passées)</h2>
                <span class="text-sm text-slate-600">{{ $coursesPassees->count() }} course(s)</span>
            </div>

            <div class="mt-4 divide-y">
                @forelse ($coursesPassees as $c)
                    @php
                        $teamKey = $c->COU_NUM . '-' . $c->EQU_NUM;
                        $members = $membersByTeam[$teamKey] ?? [];
                    @endphp

                    <div class="py-4 flex items-start justify-between gap-4 opacity-60">
                        <div>
                            <div class="font-semibold text-slate-900">
                                {{ $c->COU_NOM }}
                            </div>
                            <div class="text-sm text-slate-600 mt-1">
                                {{ \Carbon\Carbon::parse($c->COU_DATE_DEPART)->format('d/m/Y H:i') }}
                                → {{ \Carbon\Carbon::parse($c->COU_DATE_FIN)->format('d/m/Y H:i') }}
                            </div>
                            <div class="text-xs text-slate-600 mt-2">
                                Type : {{ $c->TYP_LABEL ?? '—' }} • Durée : {{ $c->COU_DUREE }} min • Difficulté : {{ $c->COU_DIFFICULTE }}
                            </div>
                        </div>

                        <div class="shrink-0 flex flex-col items-end gap-2">
                            <button
                                type="button"
                                @click="openResult = {{ $c->COU_NUM }}"
                                class="rounded-xl border px-3 py-2 text-xs font-semibold hover:bg-slate-50">
                                Résultats
                            </button>

                            <a
                                href="{{ route('race.show', $c->COU_NUM) }}"
                                class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                                Détails
                            </a>

                            <span class="rounded-full bg-slate-100 text-slate-700 border border-slate-200 px-3 py-1 text-xs font-semibold">
                                Terminée
                            </span>
                        </div>
                    </div>

                    <div
                        x-cloak
                        x-show="openResult == {{ $c->COU_NUM }}"
                        x-transition.opacity
                        class="fixed inset-0 z-50 flex items-center justify-center p-4"
                        role="dialog"
                        aria-modal="true"
                        @keydown.escape.window="openResult = null"
                    >
                        <div class="absolute inset-0 bg-black/50" @click="openResult = null"></div>

                        <div class="relative w-full max-w-xl rounded-2xl bg-white shadow-xl border p-6 opacity-100">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-lg font-bold">Résultats - {{ $c->COU_NOM }}</h3>
                                    <p class="text-sm text-slate-600 mt-1">Équipe n° {{ $c->EQU_NUM }}</p>
                                </div>

                                <button
                                    type="button"
                                    @click="openResult = null"
                                    class="rounded-xl px-3 py-2 text-sm font-semibold hover:bg-slate-100">
                                    Fermer
                                </button>
                            </div>

                            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="rounded-xl bg-slate-50 p-4">
                                    <div class="text-sm text-slate-600">Points gagnés</div>
                                    <div class="text-2xl font-bold">{{ $c->EQU_POINTS ?? 0 }}</div>
                                </div>

                                <div class="rounded-xl bg-slate-50 p-4">
                                    <div class="text-sm text-slate-600">Place de l’équipe</div>
                                    <div class="text-2xl font-bold">{{ $c->EQU_ORDRE_ARRIVEE ?? '—' }}</div>
                                </div>
                            </div>

                            <div class="mt-5">
                                <div class="font-semibold text-slate-900">Membres de l’équipe</div>

                                @if (count($members) === 0)
                                    <p class="text-sm text-slate-600 mt-2">Aucun membre trouvé.</p>
                                @else
                                    <ul class="mt-2 space-y-2">
                                        @foreach ($members as $m)
                                            <li class="rounded-xl border px-4 py-2 text-sm">
                                                {{ $m['prenom'] }} {{ $m['nom'] }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="mt-6 flex items-center justify-end gap-3">
                                <button
                                    type="button"
                                    @click="openResult = null"
                                    class="rounded-xl border px-4 py-2 text-sm font-semibold hover:bg-slate-50">
                                    Fermer
                                </button>

                                <a
                                    href="{{ route('race.show', $c->COU_NUM) }}"
                                    class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                                    Détails de la course
                                </a>
                            </div>
                        </div>
                    </div>

                @empty
                    <div class="py-6 text-slate-600">Aucune course passée.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div
        x-cloak
        x-show="openEdit"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        aria-modal="true"
        role="dialog"
        @keydown.escape.window="openEdit = false"
    >
        <div class="absolute inset-0 bg-black/50" @click="openEdit = false"></div>

        <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-xl border p-6 overflow-y-auto max-h-[90vh]">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold">Modifier mes informations</h3>
                    <p class="text-sm text-slate-600 mt-1">Tu peux modifier toutes tes infos personnelles.</p>
                </div>

                <button
                    type="button"
                    @click="openEdit = false"
                    class="rounded-xl px-3 py-2 text-sm font-semibold hover:bg-slate-100">
                    Fermer
                </button>
            </div>

            {{-- Erreurs du formulaire profil (dans le modal, et le modal reste ouvert) --}}
            @if ($errors->profileUpdate->any())
                <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                    <div class="font-semibold mb-2">Le profil n’a pas été enregistré :</div>
                    <ul class="list-disc pl-5 space-y-1 text-sm">
                        @foreach ($errors->profileUpdate->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="mt-6 space-y-4" method="POST" action="{{ route('profil.update') }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Nom</label>
                        <input
                            name="INS_NOM"
                            value="{{ old('INS_NOM', $user->INS_NOM) }}"
                            class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-400"
                        />
                        @error('INS_NOM', 'profileUpdate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Prénom</label>
                        <input
                            name="INS_PRENOM"
                            value="{{ old('INS_PRENOM', $user->INS_PRENOM) }}"
                            class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-400"
                        />
                        @error('INS_PRENOM', 'profileUpdate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-sm font-semibold text-slate-700">Email</label>
                        <input
                            type="email"
                            name="INS_MAIL"
                            value="{{ old('INS_MAIL', $user->INS_MAIL) }}"
                            class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-400"
                        />
                        @error('INS_MAIL', 'profileUpdate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Téléphone</label>
                        <input
                            name="INS_TEL"
                            value="{{ old('INS_TEL', $user->INS_TEL) }}"
                            inputmode="numeric"
                            maxlength="10"
                            class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-400"
                        />
                        @error('INS_TEL', 'profileUpdate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Date de naissance</label>
                        <input
                            type="date"
                            name="INS_NAISSANCE"
                            value="{{ old('INS_NAISSANCE', $user->INS_NAISSANCE) }}"
                            class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-400"
                        />
                        @error('INS_NAISSANCE', 'profileUpdate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-sm font-semibold text-slate-700">Adresse</label>
                        <input
                            name="INS_ADRESSE"
                            value="{{ old('INS_ADRESSE', $user->INS_ADRESSE) }}"
                            class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-400"
                        />
                        @error('INS_ADRESSE', 'profileUpdate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Ville</label>
                        <input
                            name="INS_VILLE"
                            value="{{ old('INS_VILLE', $user->INS_VILLE) }}"
                            class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-400"
                        />
                        @error('INS_VILLE', 'profileUpdate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Code postal</label>
                        <input
                            name="INS_CODE_PO"
                            value="{{ old('INS_CODE_PO', $user->INS_CODE_PO) }}"
                            inputmode="numeric"
                            maxlength="5"
                            class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-400"
                        />
                        @error('INS_CODE_PO', 'profileUpdate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- SECTION LICENCE & CLUB AJOUTEE --}}
                    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">N° Licence (optionnel)</label>
                            <input
                                name="INS_NUM_LICENCE"
                                value="{{ old('INS_NUM_LICENCE', $user->INS_NUM_LICENCE) }}"
                                class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-400"
                            />
                            @error('INS_NUM_LICENCE', 'profileUpdate')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-slate-700">Club</label>
                            <select
                                name="club_id"
                                class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-400 bg-white"
                            >
                                <option value="">-- Aucun club --</option>
                                @if(isset($clubs))
                                    @foreach($clubs as $club)
                                        <option
                                            value="{{ $club->CLU_NUM }}"
                                            {{ old('club_id', $user->CLU_NUM) == $club->CLU_NUM ? 'selected' : '' }}
                                        >
                                            {{ $club->CLU_NOM }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('club_id', 'profileUpdate')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="openEdit = false"
                        class="rounded-xl border px-4 py-2 text-sm font-semibold hover:bg-slate-50">
                        Annuler
                    </button>
                    <button
                        type="submit"
                        class="rounded-xl bg-slate-900 px-4 py-2 text-white text-sm font-semibold hover:bg-slate-800">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection