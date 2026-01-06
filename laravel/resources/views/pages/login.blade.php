@extends('layouts.app')

@section('title', 'Connexion')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center bg-[#f7f5e6]">
    <div class="w-full max-w-7/10 bg-white rounded-lg shadow-md p-12">

        {{-- Title --}}
        <h1 class="text-2xl font-semibold text-center mb-8">
            Connexion
        </h1>

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

        {{-- Form --}}
        <form action="{{ route('login') }}" method="POST">
            @csrf

            {{-- Email --}}
            <div>
                <label for="email" class="block font-medium mb-1">
                    Email :
                </label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    required
                    value="{{ old('email') }}"
                    class="w-full rounded-md bg-gray-200 border border-gray-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-400"
                >
            </div>

            {{-- Password --}}
            <div class="mt-4">
                <label for="password" class="block font-medium mb-1">
                    Mot de passe :
                </label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    required
                    class="w-full rounded-md bg-gray-200 border border-gray-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-400"
                >

                <a href="#" class="text-sm text-green-700 underline mt-1 inline-block">
                    Mot de passe oublié
                </a>
            </div>

            {{-- Button --}}
            <div class="flex justify-center pt-4">
                <button
                    type="submit"
                    class="bg-green-400 hover:bg-green-500 text-black font-semibold px-10 py-3 rounded-md transition"
                >
                    Se connecter
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
