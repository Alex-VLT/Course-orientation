@extends('layouts.app')

@section('title', "Nouveau mot de passe - L'Embuscade")

@section('content')
<div class="min-h-[70vh] flex items-center justify-center bg-[#f7f5e6] px-4">
    <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8">

        <h1 class="text-2xl font-semibold text-center mb-6">
            Nouveau mot de passe
        </h1>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div>
                <label class="block mb-1 text-sm">Mot de passe</label>
                <input type="password" name="password"
                       class="w-full rounded-md bg-gray-200 px-4 py-2" required>
            </div>

            <div>
                <label class="block mb-1 text-sm">Confirmation</label>
                <input type="password" name="password_confirmation"
                       class="w-full rounded-md bg-gray-200 px-4 py-2" required>
            </div>

            <button class="w-full bg-[#7DC2A5] py-3 rounded-md font-semibold">
                Réinitialiser
            </button>
        </form>

    </div>
</div>
@endsection
