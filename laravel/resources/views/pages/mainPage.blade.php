@extends('layouts.app')

@section('title', 'Accueil') {{--  Titre de la page --}}


@section('content')
    
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    
    

    <main class="container">
        
        <section class="intro-text">
            <h2>À propos de l'application</h2>
            <p>
                Vik'azim est un club normand de Course d'Orientation affilié à la FFCO[cite: 44]. 
                Cette application indépendante a pour but de faciliter l'inscription aux courses d'orientation et aux raids multisports organisés par le club et ses partenaires.
            </p>
            <p>
                En tant que visiteur, vous pouvez consulter les raids proposés ci-dessous. Pour vous inscrire à une course, créez un compte ou connectez-vous[cite: 66].
            </p>
        </section>

        <hr>

        <section class="raids-list">
            <h2>Les Raids à venir</h2>

            @if($raids->isEmpty())
                <p>Aucun raid n'est disponible pour le moment.</p>
            @else
                <div class="cards-grid">
                    @foreach($raids as $raid)
                        <div class="raid-card">
                            <div class="card-img">
                                @if($raid->RAID_ILLUSTRATION)
                                    <img src="{{ asset('storage/' . $raid->RAID_ILLUSTRATION) }}" alt="{{ $raid->RAID_NOM }}">
                                @else
                                    <div class="placeholder-img">Pas d'image</div>
                                @endif
                            </div>

                            <div class="card-content">
                                <h3>{{ $raid->RAID_NOM }}</h3>
                                <p class="dates">
                                    Du {{ \Carbon\Carbon::parse($raid->RAID_DATE_DEBUT)->format('d/m/Y') }} 
                                    au {{ \Carbon\Carbon::parse($raid->RAID_DATE_FIN)->format('d/m/Y') }}
                                </p>
                                <p class="contact">Contact : {{ $raid->RAID_CONTACT }}</p>
                                
                                
                                <a href="#" class="btn-details">Voir les courses</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </main>

    
    

    

@endsection
