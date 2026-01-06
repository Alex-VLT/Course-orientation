
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

    <form action="{{ route('login') }}" method="POST">
        @csrf <div>
            <label for="email">Email :</label>
            <input type="email" name="email" id="email" required value="{{ old('email') }}">
        </div>

        <div>
            <label for="password">Mot de passe :</label>
            <input type="password" name="password" id="password" required>
        </div>

        <button type="submit">Se connecter</button>
    </form>
@endsection

