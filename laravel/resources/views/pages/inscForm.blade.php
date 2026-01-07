@extends('layouts.app')
@section('title', 'Formulaire Inscription')
@section('content')
<div class="min-h-screen py-12 px-4">
    <div class="form-container">
        <div class="form-card">
            <h2 class="text-center text-2xl font-bold mb-6">Responsable d'équipe</h2>

            {{-- Success / Error compact boxes --}}
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
                            />
                            <span class="small-note no-wrap">(Cochez si vous responsable participez aussi)</span>
                        </div>
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
                        <div class="person-card person" data-index="{{ $i }}">
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