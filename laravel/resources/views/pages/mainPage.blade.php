@extends('layouts.app')

@section('title', 'Accueil')

@section('content')
    
    {{-- On inclut le CSS ici pour s'assurer qu'il est chargé --}}
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">

    {{-- Layout Global : 1/4 (Filtres/Vide) | 2/4 (Contenu) | 1/4 (Vide) --}}
    <div class="page-global-layout">
        
        {{-- ========================
             COLONNE GAUCHE (1/4)
             ======================== --}}
        
        @auth
            {{-- OPTION 1 : UTILISATEUR CONNECTÉ (Affiche les filtres) --}}
            <aside class="sidebar-left">
                <h3>🔍 Filtrer les Raids</h3>
                
                <form action="{{ route('home') }}" method="GET">
                    
                    {{-- Recherche --}}
                    <div class="filter-group">
                        <label for="search">Recherche</label>
                        <input type="text" name="search" id="search" 
                               value="{{ request('search') }}" 
                               placeholder="Ex: Viking, Normand...">
                    </div>
                    
                    {{-- Select Club --}}
                    <div class="filter-group">
                        <label for="club">Organisateur</label>
                        <select name="club" id="club">
                            <option value="">-- Tous les clubs --</option>
                            @foreach($clubs as $club)
                                <option value="{{ $club->CLU_NUM }}" {{ request('club') == $club->CLU_NUM ? 'selected' : '' }}>
                                    {{ $club->CLU_NOM }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dates (Radio) --}}
                    <div class="filter-group">
                        <label>Période</label>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="date_filter" value="" {{ request('date_filter') == '' ? 'checked' : '' }}> 
                                Tout afficher
                            </label>
                            <label>
                                <input type="radio" name="date_filter" value="future" {{ request('date_filter') == 'future' ? 'checked' : '' }}> 
                                À venir
                            </label>
                            <label>
                                <input type="radio" name="date_filter" value="past" {{ request('date_filter') == 'past' ? 'checked' : '' }}> 
                                Terminés
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn-filter">Appliquer</button>
                    
                    @if(request()->hasAny(['search', 'club', 'date_filter']))
                        <a href="{{ route('home') }}" class="reset-link">Réinitialiser les filtres</a>
                    @endif
                </form>
            </aside>
        @else
            {{-- OPTION 2 : VISITEUR (Espace vide pour garder le centrage) --}}
            <div class="sidebar-placeholder">
                {{-- On laisse ce div vide pour occuper la 1ère colonne de la grille 
                     et garder le contenu principal au centre. --}}
            </div>
        @endauth
        
        {{-- ========================
             COLONNE CENTRALE (2/4)
             ======================== --}}
        <main class="main-content-area">
            
            {{-- Bloc Intro --}}
            <section class="intro-text">
    <h2>Prêt pour l'aventure en Normandie ?</h2>
    <p>
        Découvrez les prochains Raids et courses d'orientation organisés par Vik'Azim et ses clubs partenaires. Que ce soit pour un parcours compétitif ou Rando/Loisirs, trouvez le défi qui vous correspond.
    </p>
    <p>
        <strong>Nouveau :</strong> Gérez vos inscriptions, composez vos équipes et suivez vos résultats directement depuis cette application. Créez un compte dès maintenant !
    </p>
</section>

            {{-- Liste des Cartes --}}
            <section class="raids-list">
                <h2 class="raids-title">Les événements disponibles</h2>
                
                @if($raids->isEmpty())
                    <div style="text-align:center; padding:40px; background:white; border-radius:12px;">
                        <p style="font-size:1.2rem; color:#6b7280;">Aucun raid ne correspond à votre recherche.</p>
                    </div>
                @else
                    {{-- La grille qui fait 3 colonnes --}}
                    <div class="cards-grid">
                        @foreach($raids as $raid)
                            <div class="raid-card">
                                
                                {{-- Image --}}
                                <div class="card-img">
                                    @if($raid->RAID_ILLUSTRATION)
                                        <img src="{{ asset('images/' . $raid->RAID_ILLUSTRATION) }}" alt="{{ $raid->RAID_NOM }}">
                                    @else
                                        <div style="height:100%; display:flex; align-items:center; justify-content:center; color:#9ca3af; font-weight:bold;">
                                            Pas d'image
                                        </div>
                                    @endif
                                </div>

                                {{-- Contenu Texte --}}
                                <div class="card-content">
                                    <h3>{{ $raid->RAID_NOM }}</h3>
                                    
                                    <div class="dates">
                                        📅 {{ \Carbon\Carbon::parse($raid->RAID_DATE_DEBUT)->format('d/m/Y') }} 
                                        au {{ \Carbon\Carbon::parse($raid->RAID_DATE_FIN)->format('d/m/Y') }}
                                    </div>
                                    
                                    <p class="contact">
                                        📧 {{ $raid->RAID_CONTACT }}
                                    </p>
                                    
                                    <a href="{{ route('raid.show', $raid->RAID_NUM) }}" class="btn-details">Voir les details →</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </main>

        {{-- ========================
             COLONNE DROITE (1/4)
             ======================== --}}
        <aside class="sidebar-right">
            &nbsp; 
        </aside>

    </div>

@endsection