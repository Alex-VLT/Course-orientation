@extends('layouts.app')

@section('title', 'Gestion : ' . $race->COU_NOM)

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">
    
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <a href="{{ route('race.organizer_index') }}" class="text-sm text-gray-500 hover:underline">← Retour à mes courses</a>
            <h1 class="text-3xl font-bold mt-1 text-black">Gestion : {{ $race->COU_NOM }}</h1>
            <div class="text-sm text-gray-600">
                Responsable : <strong>{{ Auth::user()->INS_PRENOM }} {{ Auth::user()->INS_NOM }}</strong>
            </div>
        </div>
        
        <div class="flex flex-wrap gap-2">
            {{-- Results Management Restriction: Only if Race Started/Past --}}
            @if($race->COU_DATE_DEPART < now())
                 {{-- Export CSV Button --}}
                 <a href="{{ route('race.export', $race->COU_NUM) }}" class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition shadow-sm font-bold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Exporter Résultats CSV
                 </a>
                 
                 {{-- Import Results Button --}}
                 <form action="{{ route('race.results.upload', $race->COU_NUM) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                     @csrf
                     <label class="cursor-pointer bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition shadow-sm font-bold text-sm flex items-center gap-2">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                         Importer Résultats
                         <input type="file" name="results" class="hidden" onchange="if(confirm('Importer ce fichier ? Cela écrasera les résultats existants.')) this.form.submit()">
                     </label>
                 </form>
            @else
                <div class="flex items-center gap-2 text-gray-500 bg-gray-100 px-4 py-2 rounded text-sm italic border border-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Gestion des équipes uniquement (Course à venir)
                </div>
            @endif
        </div>
    </div>

    {{-- Rest of the view remains exactly the same as before --}}
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 shadow-sm rounded" role="alert"><p>{{ session('success') }}</p></div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 shadow-sm rounded" role="alert"><p>{{ session('error') }}</p></div>
    @endif

    {{-- Team List Container --}}
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden relative z-0">
        <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800">
                Équipes inscrites <span class="bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded-full ml-2">{{ $race->equipes->count() }}</span>
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-100 text-gray-600 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="p-4 border-b w-20 text-center">Clas.</th>
                        <th class="p-4 border-b">Équipe</th>
                        <th class="p-4 border-b w-1/2">Membres (Détails)</th>
                        <th class="p-4 border-b text-center">Paiement</th>
                        <th class="p-4 border-b text-center">Gestion</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse($race->equipes as $equipe)
                        <tr class="hover:bg-gray-50 transition-colors align-top">
                            {{-- 1. Ranking & Time --}}
                            <td class="p-4 text-center">
                                @if($equipe->EQU_ORDRE_ARRIVEE)
                                    <div class="flex flex-col items-center">
                                        <span class="text-2xl font-black text-slate-800">#{{ $equipe->EQU_ORDRE_ARRIVEE }}</span>
                                        <span class="text-[10px] uppercase font-bold text-slate-500 bg-slate-100 px-1.5 rounded">{{ $equipe->EQU_TEMPS }} min</span>
                                        <span class="text-[10px] text-green-600 font-bold">{{ $equipe->EQU_POINTS }} pts</span>
                                    </div>
                                @else
                                    <span class="text-gray-300 font-bold text-xl">-</span>
                                @endif
                            </td>
                            
                            {{-- 2. Team Name & Captain --}}
                            <td class="p-4">
                                <div class="font-bold text-gray-800 text-lg">{{ $equipe->EQU_NOM }}</div>
                                <div class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Capitaine : {{ optional($equipe->createur)->INS_PRENOM }} {{ optional($equipe->createur)->INS_NOM }}
                                </div>
                            </td>
                            
                            {{-- 3. Members List --}}
                            <td class="p-4">
                                <ul class="space-y-3">
                                    @foreach($equipe->participations as $part)
                                        @php $u = $part->user; @endphp
                                        <li class="flex flex-col bg-slate-50 p-2 rounded border border-slate-100">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <span class="font-bold text-gray-800">{{ $u->INS_PRENOM }} {{ $u->INS_NOM }}</span>
                                                    <div class="text-xs text-gray-500">{{ $u->INS_MAIL }}</div>
                                                </div>
                                                
                                                {{-- Remove Member Button --}}
                                                <form action="{{ route('race.team.remove_member', [$race->COU_NUM, $equipe->EQU_NUM, $u->INS_ID]) }}" method="POST" onsubmit="return confirm('Retirer {{ $u->INS_PRENOM }} de l\'équipe ?')">
                                                    @csrf @method('DELETE')
                                                    <button class="text-red-300 hover:text-red-600 p-1" title="Retirer de l'équipe">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </form>
                                            </div>

                                            {{-- Documents Status (License / PPS) --}}
                                            <div class="mt-2 text-xs flex items-center gap-2 flex-wrap">
                                                @if(!empty($u->INS_NUM_LICENCE))
                                                    {{-- Case 1: User has a License --}}
                                                    <span class="inline-flex items-center gap-1 text-green-700 bg-green-100 px-2 py-1 rounded border border-green-200 font-semibold">
                                                        Licence : {{ $u->INS_NUM_LICENCE }}
                                                    </span>
                                                @elseif(!empty($part->PAR_NUM_PPS))
                                                    {{-- Case 2: User has a PPS for this race --}}
                                                    <div class="flex items-center gap-1 group">
                                                        <span class="inline-flex items-center gap-1 text-blue-700 bg-blue-100 px-2 py-1 rounded border border-blue-200 font-semibold">
                                                            PPS : {{ $part->PAR_NUM_PPS }}
                                                        </span>
                                                        <button onclick="togglePpsForm('form-pps-{{ $part->id }}')" class="text-blue-400 hover:text-blue-600 ml-1">✏️</button>
                                                    </div>
                                                    {{-- Hidden Edit Form --}}
                                                    <form id="form-pps-{{ $part->id }}" action="{{ route('race.team.member.pps', [$race->COU_NUM, $equipe->EQU_NUM, $u->INS_ID]) }}" method="POST" class="hidden mt-1 flex gap-1">
                                                        @csrf
                                                        <input type="text" name="pps" value="{{ $part->PAR_NUM_PPS }}" class="text-xs border border-blue-300 rounded px-1 py-0.5 w-24 focus:ring-blue-500">
                                                        <button class="text-xs bg-blue-600 text-white px-2 rounded hover:bg-blue-700">OK</button>
                                                    </form>
                                                @else
                                                    {{-- Case 3: Missing Documents --}}
                                                    <span class="inline-flex items-center gap-1 text-red-700 bg-red-100 px-2 py-1 rounded border border-red-200 font-bold">
                                                        Dossier incomplet
                                                    </span>
                                                    {{-- Add PPS Form --}}
                                                    <form action="{{ route('race.team.member.pps', [$race->COU_NUM, $equipe->EQU_NUM, $u->INS_ID]) }}" method="POST" class="flex gap-1 ml-1">
                                                        @csrf
                                                        <input type="text" name="pps" placeholder="N° PPS" class="text-xs border border-red-300 rounded px-1 py-0.5 w-24 focus:ring-red-500" required>
                                                        <button class="text-xs bg-red-600 text-white px-2 rounded hover:bg-red-700 font-bold">OK</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                                
                                {{-- Add Member Button --}}
                                @if($equipe->participations->count() < $race->COU_PART_PAR_EQU_MAX)
                                    <button onclick="openModal('{{ $equipe->EQU_NOM }}', '{{ $equipe->EQU_NUM }}')" 
                                            class="mt-3 text-xs flex items-center gap-1 text-[#A67C52] hover:text-[#8B623D] font-bold border border-dashed border-[#A67C52] px-3 py-1.5 rounded hover:bg-[#A67C52] hover:text-white transition w-full justify-center">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Rechercher & Ajouter un membre
                                    </button>
                                @else
                                    <div class="mt-3 text-xs text-gray-400 italic text-center">Équipe complète</div>
                                @endif
                            </td>
                            
                            {{-- 4. Payment Status --}}
                            <td class="p-4 text-center">
                                @if($equipe->EQU_PAIEMENT_VALIDE)
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="px-3 py-1 text-xs font-bold text-green-800 bg-green-100 rounded-full border border-green-200">✅ PAYÉ</span>
                                        <form action="{{ route('race.team.payment', [$race->COU_NUM, $equipe->EQU_NUM]) }}" method="POST">
                                            @csrf
                                            <button class="text-[10px] text-gray-400 underline hover:text-red-500">Annuler</button>
                                        </form>
                                    </div>
                                @else
                                    <div class="flex flex-col items-center gap-2">
                                        <span class="px-3 py-1 text-xs font-bold text-yellow-800 bg-yellow-100 rounded-full border border-yellow-200">⏳ ATTENTE</span>
                                        <form action="{{ route('race.team.payment', [$race->COU_NUM, $equipe->EQU_NUM]) }}" method="POST">
                                            @csrf
                                            <button class="bg-[#7DC2A5] hover:bg-[#6ab394] text-white px-2 py-1 rounded shadow-sm text-xs font-bold transition">
                                                Valider
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                            
                            {{-- 5. Delete Team --}}
                            <td class="p-4 text-center">
                                <form action="{{ route('race.team.delete', [$race->COU_NUM, $equipe->EQU_NUM]) }}" method="POST" onsubmit="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer définitivement l\'équipe {{ $equipe->EQU_NOM }} ? Cette action est irréversible.');">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500 hover:text-white hover:bg-red-500 border border-red-200 bg-red-50 p-2 rounded-md transition shadow-sm" title="Supprimer l'équipe">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-12 text-center text-gray-500 italic">Aucune équipe inscrite pour le moment.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL: SEARCH & ADD MEMBER (Native JS) --}}
    <div id="memberModal" class="fixed inset-0 z-[999] hidden" role="dialog" aria-modal="true">
        {{-- Overlay --}}
        <div class="fixed inset-0 bg-gray-600 bg-opacity-80 transition-opacity" onclick="closeModal()"></div>

        {{-- Centering --}}
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                
                {{-- Modal Content --}}
                <div class="relative transform overflow-hidden rounded-lg bg-white shadow-2xl transition-all sm:w-full sm:max-w-lg border border-gray-200">
                    
                    {{-- Header --}}
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900">
                            Ajouter un membre à <span id="modalTeamName" class="text-[#A67C52]"></span>
                        </h3>
                        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-6">
                        {{-- Search Bar --}}
                        <div class="relative">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Rechercher un membre (Nom ou Prénom)</label>
                            <div class="flex gap-2">
                                <input type="text" id="searchInput" class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#7DC2A5] focus:ring-[#7DC2A5]" placeholder="Ex: Martin, Lucas...">
                                <button onclick="searchMembers()" class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-black transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Search Results --}}
                        <div id="searchResults" class="mt-4 max-h-60 overflow-y-auto space-y-2">
                            <p class="text-sm text-gray-500 italic text-center py-4">Entrez un nom pour rechercher...</p>
                        </div>

                        {{-- Hidden Form (submitted on click) --}}
                        <form id="addMemberForm" method="POST" class="hidden">
                            @csrf
                            <input type="hidden" name="ins_id" id="selectedInsId">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- SCRIPT (Native JS) --}}
<script>
    let currentTeamId = null;
    let raceNum = "{{ $race->COU_NUM }}";

    // Open Modal
    function openModal(teamName, teamId) {
        document.getElementById('modalTeamName').innerText = teamName;
        currentTeamId = teamId;
        // Reset Search
        document.getElementById('searchInput').value = '';
        document.getElementById('searchResults').innerHTML = '<p class="text-sm text-gray-500 italic text-center py-4">Type a name to search...</p>';
        document.getElementById('memberModal').classList.remove('hidden');
    }

    // Close Modal
    function closeModal() {
        document.getElementById('memberModal').classList.add('hidden');
    }
    
    // Toggle PPS Form visibility
    function togglePpsForm(id) {
        let form = document.getElementById(id);
        form.classList.toggle('hidden');
    }

    // AJAX Search Logic
    function searchMembers() {
        let query = document.getElementById('searchInput').value;
        let resultsDiv = document.getElementById('searchResults');
        
        if(query.length < 2) {
            resultsDiv.innerHTML = '<p class="text-sm text-red-500 text-center">Type at least 2 characters.</p>';
            return;
        }

        resultsDiv.innerHTML = '<p class="text-sm text-gray-500 text-center">Searching...</p>';

        // Fetch API call
        fetch(`{{ route('api.users.search') }}?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                resultsDiv.innerHTML = '';
                if(data.length === 0) {
                    resultsDiv.innerHTML = '<p class="text-sm text-gray-500 text-center">No members found.</p>';
                    return;
                }

                // Render Results
                data.forEach(user => {
                    let licInfo = user.INS_NUM_LICENCE ? `<span class="text-green-600 text-xs">License: ${user.INS_NUM_LICENCE}</span>` : '<span class="text-gray-400 text-xs">No License</span>';
                    
                    let item = document.createElement('div');
                    item.className = 'flex justify-between items-center p-3 bg-gray-50 hover:bg-green-50 border border-gray-100 rounded cursor-pointer transition';
                    item.innerHTML = `
                        <div>
                            <div class="font-bold text-gray-800">${user.INS_PRENOM} ${user.INS_NOM}</div>
                            <div class="text-xs text-gray-500">${user.INS_MAIL}</div>
                        </div>
                        <div class="text-right">
                            ${licInfo}
                            <div class="text-[#7DC2A5] font-bold text-xs mt-1">Add +</div>
                        </div>
                    `;
                    item.onclick = function() { selectUser(user.INS_ID); };
                    resultsDiv.appendChild(item);
                });
            })
            .catch(err => {
                console.error(err);
                resultsDiv.innerHTML = '<p class="text-sm text-red-500 text-center">Error during search.</p>';
            });
    }

    // Submit Selection
    function selectUser(insId) {
        if(!confirm('Add this member to the team?')) return;

        let form = document.getElementById('addMemberForm');
        let baseUrl = "{{ url('/course/') }}/" + raceNum + "/team/";
        form.action = baseUrl + currentTeamId + '/add-member';
        
        document.getElementById('selectedInsId').value = insId;
        form.submit();
    }

    // Enter Key Listener for Search
    document.getElementById('searchInput').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault(); // Prevent global form submit
            searchMembers();
        }
    });

    // Escape Key Listener for Closing Modal
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") closeModal();
    });
</script>
@endsection