
@extends('layouts.app')

@section('title', 'Accueil') {{--  Titre de la page --}}


    @section('content')
    <div class="min-h-[70vh] flex items-center justify-center bg-[#f7f5e6] ">
        <div class="w-4/6 max-w-7/10 bg-white rounded-lg shadow-md p-12 mt-10">
                <h1 class="text-2xl font-semibold text-center mb-8">
                    Inscription
                </h1>
                @if ($errors->any())
                <div style="color: red;">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                
                <form action="{{ route('register') }}" method="POST" class="mt-4 block font-medium mb-1">
                @csrf <div>
                    <label>Nom :</label>
                    <input type="text" name="nom" value="{{ old('nom') }}" class="w-full rounded-md bg-gray-200 border border-gray-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" required>
                    </div>
                    <div>
                        <label>Prénom :</label>
                        <input type="text" name="prenom" value="{{ old('prenom') }}" class="w-full rounded-md bg-gray-200 border border-gray-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" required>
                    </div>

                    <div>
                        <label>Date de naissance :</label>
                        <input type="date" name="naissance" value="{{ old('naissance') }}" class="w-full rounded-md bg-gray-200 border border-gray-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" required>
                    </div>

                    <div>
                        <label>Email :</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-md bg-gray-200 border border-gray-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" required>
                    </div>

                    <div>
                        <label>Téléphone :</label>
                        <input type="text" name="tel" value="{{ old('tel') }}" class="w-full rounded-md bg-gray-200 border border-gray-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" required>
                    </div>

                    <div>
                        <label>Adresse :</label>
                        <input type="text" name="adresse" value="{{ old('adresse') }}" class="w-full rounded-md bg-gray-200 border border-gray-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" required>
                    </div>

                    <div>
                        <label>Code Postal :</label>
                        <input type="number" name="cp" value="{{ old('cp') }}" class="w-full rounded-md bg-gray-200 border border-gray-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" required>
                    </div>

                    <div>
                        <label>Ville :</label>
                        <input type="text" name="ville" value="{{ old('ville') }}" class="w-full rounded-md bg-gray-200 border border-gray-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" required>
                    </div>
                    <br><hr><br>
                    <div>
                        <label>Mot de passe :</label>
                        <input type="password" name="password" class="w-full rounded-md bg-gray-200 border border-gray-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" required>
                    </div>

                    <div>
                        <label>Confirmer le mot de passe :</label>
                        <input type="password" name="password_confirmation" class="w-full rounded-md bg-gray-200 border border-gray-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" required>
                    </div>        
                    <button 
                    class=" w-1/2 mx-auto flex justify-center py-3 px-4 border-transparent rounded-md shadow-sm text-sm font-medium text-black bg-[#7dc2a5] hover:brightness-90 mt-5" 
                    type="submit">S'inscrire</button>
                </form>
                <p class="underline flex justify-center mx-auto">Déjà un compte ? <a href="{{ route('login') }}">‎ Se connecter</a></p>
            </div>
        </div>
        <br>

@endsection

