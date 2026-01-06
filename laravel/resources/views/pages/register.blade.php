
@extends('layouts.app')

@section('title', 'Accueil') {{--  Titre de la page --}}


    @section('content')
        @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('register') }}" method="POST">
        @csrf <div>
            <label>Nom :</label>
            <input type="text" name="nom" value="{{ old('nom') }}" required>
        </div>

        <div>
            <label>Prénom :</label>
            <input type="text" name="prenom" value="{{ old('prenom') }}" required>
        </div>

        <div>
            <label>Date de naissance :</label>
            <input type="date" name="naissance" value="{{ old('naissance') }}" required>
        </div>

        <div>
            <label>Email :</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
        </div>

        <div>
            <label>Téléphone :</label>
            <input type="text" name="tel" value="{{ old('tel') }}" required>
        </div>

        <div>
            <label>Adresse :</label>
            <input type="text" name="adresse" value="{{ old('adresse') }}" required>
        </div>

        <div>
            <label>Code Postal :</label>
            <input type="number" name="cp" value="{{ old('cp') }}" required>
        </div>

        <div>
            <label>Ville :</label>
            <input type="text" name="ville" value="{{ old('ville') }}" required>
        </div>

        <hr>
        <div>
            <label>Mot de passe :</label>
            <input type="password" name="password" required>
        </div>

        <div>
            <label>Confirmer le mot de passe :</label>
            <input type="password" name="password_confirmation" required>
        </div>

        <br>
        <button type="submit">S'inscrire</button>
    </form>

    <p>Déjà un compte ? <a href="{{ route('login') }}">Se connecter</a></p>
@endsection

