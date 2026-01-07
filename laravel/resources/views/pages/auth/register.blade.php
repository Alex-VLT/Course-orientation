
@extends('layouts.app')

@section('title', "Inscription - L'Embuscade")


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

                
                <form action="{{ route('register') }}" method="POST" class="space-y-4 mt-4 block font-medium mb-1">
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
                    <div class="my-4 p-4 border border-gray-100 rounded-lg bg-gray-50">
                        <div class="flex items-center mb-4">
                            <input type="checkbox" id="is_club" name="is_club" value="1" {{ old('is_club') ? 'checked' : '' }} class="w-5 h-5 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500">
                            <label for="is_club" class="ml-2 text-sm font-medium text-gray-900">Êtes-vous inscrit dans un club ?</label>
                        </div>

                        <div id="club_inputs" class="hidden space-y-4">
                            <div>
                                <label>Numéro de licence :</label>
                                <input type="text" name="licence" value="{{ old('licence') }}" class="w-full rounded-md bg-white border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-400">
                            </div>
                        
                        </div>

                        <div id="pps_input" class="block">
                            <div>
                                <label>Numéro PPS :</label>
                                <input type="text" name="pps" value="{{ old('pps') }}" class="w-full rounded-md bg-white border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-400">
                            </div>
                        </div>
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
                <p class="mt-4 flex justify-center mx-auto">Déjà un compte ? ‎<a href="{{ route('login') }}" class="underline">Se connecter</a></p>
            </div>
        </div>
    </div>
    <br>
@vite('resources/js/register.js')
@endsection

