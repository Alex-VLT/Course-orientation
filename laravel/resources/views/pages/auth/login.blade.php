@extends('layouts.app')

@section('title', "Connexion - L'Embuscade")

@section('content')
<div class="min-h-[80vh] flex items-center justify-center bg-[#f7f5e6] px-4">
    <div class="w-full max-w-8/10 h-full bg-white rounded-xl shadow-lg overflow-hidden grid grid-cols-1 md:grid-cols-2">

        {{-- Left) --}}
        <div class="md:flex flex-col justify-center items-center bg-gradient-to-br from-[#7DC2A5] to-[#649C84] text-white p-10">
            <h2 class="text-md text-center uppercase tracking-wide opacity-80 mb-4">
                L'Embuscade
            </h2>

            <h1 class="text-5xl text-center font-bold leading-tight mb-4">
                Bon retour 👋
            </h1>

            <p class="text-white/90 max-w-md justify-center text-center">
                Connecte-toi pour gérer tes inscriptions, consulter les raids
                et suivre tes performances.
            </p>
        </div>

        {{-- Right --}}
        <div class="flex items-center justify-center p-10">
            <div class="w-full max-w-md">

                <h2 class="text-2xl font-semibold text-center mb-8">
                    Connexion
                </h2>

                {{-- Errors --}}
                @if ($errors->any())
                    <div class="mb-6 rounded bg-red-100 border border-red-300 text-red-700 p-4">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="space-y-5">
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
                            required
                            value="{{ old('email') }}"
                            placeholder="ex: jean@mail.fr"
                            class="w-full rounded-md bg-gray-100 border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#7DC2A5]"
                        >
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium mb-1">
                            Mot de passe
                        </label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            required
                            placeholder="••••••••"
                            class="w-full rounded-md bg-gray-100 border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#7DC2A5]"
                        >


                        <a href="{{ route('password.request') }}" class="text-sm text-[#649C84] underline mt-1 inline-block">
                            Mot de passe oublié ?
                        </a>
                    </div>

                    {{-- Button --}}
                    <button
                        type="submit"
                        class="cursor-pointer w-full bg-[#7DC2A5] hover:bg-[#649C84] text-black font-semibold py-3 rounded-md transition"
                    >
                        Se connecter
                    </button>
                </form>

                <p class="mt-4 flex justify-center mx-auto">Pas encore de compte ? ‎<a href="{{ route('register') }}" class="underline">Créer un compte</a></p>
            </div>
        </div>

    </div>
</div>
@endsection
