@extends('layouts.app')

@section('title', "Réinitialisation du mot de passe - L'Embuscade")

@section('content')
<div class="min-h-[70vh] flex items-center justify-center bg-[#f7f5e6] px-4">

    <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8">

        {{-- Title --}}
        <h1 class="text-2xl font-semibold text-center mb-6">
            Réinitialisation du mot de passe
        </h1>

        {{-- Description --}}
        <p class="text-sm text-gray-600 text-center mb-6">
            Entrez votre adresse email.  
            Un lien de réinitialisation vous sera envoyé.
        </p>

        {{-- Errors --}}
        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-100 border border-red-300 text-red-700 p-3 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form --}}
        <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
            @csrf

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium mb-1">
                    Email
                </label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email') }}"
                    required
                    placeholder="ex: jean@mail.fr"
                    class="w-full rounded-md bg-gray-200 border border-gray-200 px-4 py-2
                           focus:outline-none focus:ring-2 focus:ring-[#7DC2A5]"
                >
            </div>

            {{-- Submit --}}
            <button
                type="submit"
                class="w-full bg-[#7DC2A5] hover:bg-[#649C84] text-black
                       font-semibold py-3 rounded-md transition"
            >
                Réinitialiser le mot de passe
            </button>
        </form>

        {{-- Back to login --}}
        <div class="text-center mt-6">
            <a href="{{ route('login') }}"
               class="text-sm text-[#7DC2A5] hover:underline">
                ← Retour à la connexion
            </a>
        </div>

    </div>

</div>
@endsection
