@extends('layouts.app')

@section('title', 'Accueil') {{--  Titre de la page --}}


@section('content')
    
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    
    <form action="{{ route('logout') }}" method="POST">
        @csrf <button type="submit" style="background-color: red; color: white; padding: 10px; border: none; cursor: pointer;">
            Se déconnecter
        </button>
    </form>
    

    

@endsection
