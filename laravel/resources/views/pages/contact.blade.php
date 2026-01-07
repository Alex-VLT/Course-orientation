@extends('layouts.app')

@section('title', "Contact - L'Embuscade")

@section('content')
<div class="min-h-[70vh] flex items-center justify-center bg-[#f7f5e6]">
    <div class="w-4/6 max-w-7/10 bg-white rounded-lg shadow-md p-12 mt-10 mb-10">
        
        <h1 class="text-2xl font-semibold text-center mb-8">
            Nous contacter
        </h1>

        {{-- Success Message --}}
        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700">
                <p class="font-bold">Succès !</p>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                <p class="font-bold">Veuillez vérifier les erreurs ci-dessous :</p>
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('contact.send') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block mb-2 font-medium">Nom complet :</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" 
                           class="w-full rounded-md bg-gray-200 border border-gray-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" 
                           placeholder="Jean Dupont" required>
                </div>

                <div>
                    <label for="email" class="block mb-2 font-medium">Adresse email :</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" 
                           class="w-full rounded-md bg-gray-200 border border-gray-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" 
                           placeholder="vous@exemple.com" required>
                </div>
            </div>

            <div>
                <label for="subject" class="block mb-2 font-medium">Sujet :</label>
                <select id="subject" name="subject" class="w-full rounded-md bg-gray-200 border border-gray-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-400">
                    <option value="Renseignement général" {{ old('subject') == 'Renseignement général' ? 'selected' : '' }}>Renseignement général</option>
                    <option value="Problème d'inscription" {{ old('subject') == "Problème d'inscription" ? 'selected' : '' }}>Problème d'inscription</option>
                    <option value="Support technique" {{ old('subject') == 'Support technique' ? 'selected' : '' }}>Support technique</option>
                    <option value="Autre" {{ old('subject') == 'Autre' ? 'selected' : '' }}>Autre</option>
                </select>
            </div>

            <div>
                <label for="message" class="block mb-2 font-medium">Message :</label>
                <textarea id="message" name="message" rows="5" 
                          class="w-full rounded-md bg-gray-200 border border-gray-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" 
                          placeholder="Comment pouvons-nous vous aider ?" required>{{ old('message') }}</textarea>
            </div>

            <button 
                class="w-1/2 mx-auto flex justify-center py-3 px-4 border-transparent rounded-md shadow-sm text-sm font-medium text-black bg-[#7dc2a5] hover:brightness-90 transition duration-150" 
                type="submit">
                Envoyer le message
            </button>
        </form>
    </div>
</div>
@endsection