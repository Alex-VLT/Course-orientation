@extends('layouts.app')
@section('title', 'Formulaire Inscription')
@section('content')
<div class="min-h-screen py-12 px-4">
    <div class="form-container">
        <div class="form-card">
                        <!--
                                Developer notes (English):
                                - This Blade renders the team registration form used by a logged-in user (the "chef").
                                - Expected POST payload structure:
                                    - team_name: string (required)
                                    - participation: optional boolean (whether the chef participates)
                                    - chef_pps: optional string (PPS of the chef, required later if non-adherent)
                                    - people: optional array of participant objects (each may include: firstname, name, email, licence, pps, ins_id)
                                - Client-side helpers in this file try to resolve ``people[*].ins_id`` via autocomplete.
                                - The server will reject submissions where a provided person cannot be resolved to an existing
                                    INS_ID. This view uses old() to re-populate the same structure on validation errors.
                                - PPS fields are optional here but must be provided before the event if the participant is
                                    not an adherent (no licence/pps stored in their account). See server-side validation helpers
                                    in App\Http\Controllers\VerifInscriptionController for the exact rules.
                                - When updating this file, prefer adding explanatory comments for any complex JS interactions
                                    so external contributors understand the client/server contract.
                        -->

                        <h2 class="text-center text-2xl font-bold mb-6">Responsable d'équipe</h2>

            <!-- Success / Error compact boxes -->
            @if(session('success'))
                <div class="success-box">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="error-box">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="error-box">
                    <strong>Des erreurs ont été trouvées :</strong>
                    <ul style="margin-top:6px; margin-bottom:0.25rem; padding-left:1rem;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('validation_json'))
                @php $v = session('validation_json'); @endphp
                @if(!empty($v['details']['age']))
                    <div class="mb-4 p-3 border rounded bg-yellow-50">
                        <div class="font-semibold mb-2">Détails d'âge des participants :</div>
                        <ul class="text-sm list-disc pl-6">
                            @foreach($v['details']['age'] as $d)
                                @php
                                    $fullName = trim(($d['prenom'] ?? '') . ' ' . ($d['nom'] ?? ''));
                                @endphp
                                <li>
                                    @if(!empty($fullName))
                                        {{ $fullName }} — naissance: {{ $d['naissance'] ?? 'N/A' }} — âge à la course: {{ $d['age_at_start'] ?? 'N/A' }}
                                    @else
                                        Utilisateur inconnu — naissance: {{ $d['naissance'] ?? 'N/A' }} — âge à la course: {{ $d['age_at_start'] ?? 'N/A' }}
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endif

            @php
                /*
                 * Developer note: Detect whether the currently authenticated team leader (chef)
                 * appears as an "adherent" in the local database. We use this to decide whether
                 * to show the PPS input as a required/visible field in the form UI.
                 *
                 * Logic details:
                 * - We attempt to resolve the logged-in user's email (INS_MAIL) to a User record.
                 * - If a matching User exists, we check for either a stored licence number
                 *   (INS_NUM_LICENCE) or an existing stored PPS (INS_NUM_PPS).
                 * - If either value is present we treat the user as an adherent; the UI will then
                 *   typically hide or de-emphasise the PPS free-text input.
                 *
                 * Notes for contributors:
                 * - This is a UI helper only. All authoritative checks (age, duplicates, PPS
                 *   requirements) are re-checked on the server in submitForm and VerifInscription.
                 * - To change detection rules (for example to prefer INS_ID lookups), adjust the
                 *   queries below and keep the server-side validation in sync.
                 */
                $chefIsAdherent = false;
                $authUser = auth()->user();
                if ($authUser) {
                    $chefEmail = $authUser->INS_MAIL ?? $authUser->email ?? null;
                    if ($chefEmail) {
                        $chefRec = \App\Models\User::where('INS_MAIL', $chefEmail)->first();
                        if ($chefRec) {
                            $chefIsAdherent = (!empty($chefRec->INS_NUM_LICENCE) || !empty($chefRec->INS_NUM_PPS));
                        }
                    }
                }
            @endphp

            <form action="" method="post" novalidate>
                @csrf

                <div class="form-row">
                    <label for="participation">Je participe :</label>
                    <div class="flex-1">
                        <div class="inline-flex items-center gap-3">
                            <input 
                                type="checkbox" 
                                id="participation" 
                                name="participation"
                                class="w-5 h-5 border-2 border-black"
                                data-chef-email="{{ auth()->user()->INS_MAIL ?? auth()->user()->email ?? '' }}"
                            />
                            <span class="small-note no-wrap">(Cochez si vous responsable participez aussi)</span>
                        </div>
                    </div>
                </div>

                <!-- Chef PPS (visible; required only if non-adhérent) -->
                <div class="form-row chef-pps-row">
                    <label for="chef_pps">PPS du responsable :</label>
                    <div class="flex-1">
                        <input
                            id="chef_pps"
                            type="text"
                            name="chef_pps"
                            value="{{ old('chef_pps') }}"
                            placeholder="Numéro PPS (si non renseigné, devra être renseigné avant la course)"
                            class="w-full px-3 py-2 border-2 border-black rounded bg-white"
                        />
                        <div class="text-xs text-gray-600">Si non renseigné, devra être renseigné avant la course.</div>
                    </div>
                </div>

                <div class="form-row">
                    <label for="team_name">Nom de l'équipe :</label>
                    <div class="flex-1">
                        <input 
                            id="team_name"
                            type="text" 
                            name="team_name" 
                            value="{{ old('team_name') }}" 
                            required
                            class="w-full inscrit-name px-3 py-2 border-2 border-black rounded bg-white"
                        />
                    </div>
                </div>

                <div class="controls mb-6">
                    <button 
                        type="button" 
                        id="add-person"
                        data-team-max="{{ $team_max ?? '' }}"
                        class="btn btn-ghost add-person"
                    >
                        + Ajouter un coureur
                    </button>
                </div>

                <!-- Runner List -->
                <div id="people-list" class="space-y-4">
                    @foreach(old('people', [['name'=>'','firstname'=>'','email'=>'','licence'=>'','pps'=>'']]) as $i => $oldPerson)
                        @php
                            $isAdherent = false;
                            $showPps = false; // default: hide PPS when there's no info
                            // if an ins_id is present in old data, check in DB
                            if (!empty($oldPerson['ins_id'])) {
                                $insRec = \App\Models\User::where('INS_ID', $oldPerson['ins_id'])->first();
                                if ($insRec) {
                                    $isAdherent = (!empty($insRec->INS_NUM_LICENCE) || !empty($insRec->INS_NUM_PPS));
                                }
                                // when we have an ins_id, we can decide to show PPS only if NOT adherent
                                $showPps = ! $isAdherent;
                            } else {
                                // no ins_id: only show PPS if user already filled it previously
                                if (!empty($oldPerson['pps'])) {
                                    $showPps = true;
                                }
                            }
                        @endphp
                        <div class="person-card person" data-index="{{ $i }}" @if(!empty($oldPerson['ins_id'])) data-is-adherent="{{ $isAdherent ? '1' : '0' }}" @endif>
                            <h3 class="font-bold mb-4">Coureur {{ $i + 1 }}</h3>
                            <div class="form-row">
                                <label>Rechercher inscrit :</label>
                                <div class="flex-1 relative">
                                    <input
                                        type="search"
                                        name="people[{{ $i }}][search]"
                                        placeholder="Prénom, nom ou email"
                                        class="inscrit-search w-full px-3 py-2 border-2 border-black rounded bg-white"
                                        data-search-url="{{ url('/inscrits/search') }}"
                                        value="{{ old("people.$i.search") }}"
                                    />
                                    <div class="inscrit-suggestions absolute left-0 right-0 bg-white border border-black/10 mt-1 z-40 hidden"></div>
                                </div>
                            </div>

                            <div class="form-row">
                                <label>Prénom :</label>
                                <div class="flex-1">
                                    <input 
                                        type="text" 
                                        name="people[{{ $i }}][firstname]" 
                                        value="{{ old("people.$i.firstname") }}" 
                                        required
                                        class="inscrit-firstname w-full px-3 py-2 border-2 border-black rounded bg-white"
                                    />
                                </div>
                            </div>

                            <div class="form-row">
                                <label>Nom :</label>
                                <div class="flex-1">
                                    <input 
                                        type="text" 
                                        name="people[{{ $i }}][name]" 
                                        value="{{ old("people.$i.name") }}" 
                                        required
                                        class="inscrit-name w-full px-3 py-2 border-2 border-black rounded bg-white"
                                    />
                                </div>
                            </div>

                            <div class="form-row pps-row">
                                <label>PPS :</label>
                                <div class="flex-1">
                                    <input
                                        type="text"
                                        name="people[{{ $i }}][pps]"
                                        value="{{ old("people.$i.pps") }}"
                                        placeholder="Numéro PPS (si non renseigné, devra être renseigné avant la course)"
                                        class="inscrit-pps w-full px-3 py-2 border-2 border-black rounded bg-white"
                                    />
                                    <div class="text-xs text-gray-600">Si non renseigné, devra être renseigné avant la course.</div>
                                </div>
                            </div>

                            <input type="hidden" name="people[{{ $i }}][ins_id]" class="inscrit-id" value="{{ old("people.$i.ins_id") }}" />

                            <div class="form-row">
                                <div style="flex:1"></div>
                                <div>
                                    <button 
                                        type="button" 
                                        class="remove remove-btn"
                                    >
                                        Supprimer
                                    </button>
                                </div>
                            </div>

                            @error("people.$i.firstname") 
                                <div class="text-red-600 text-sm">{{ $message }}</div> 
                            @enderror
                            @error("people.$i.name") 
                                <div class="text-red-600 text-sm">{{ $message }}</div> 
                            @enderror
                        </div>
                    @endforeach
                </div>
                <div class="controls mt-6">
                    <button type="submit" id="submit-form" class="btn btn-primary">Envoyer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection