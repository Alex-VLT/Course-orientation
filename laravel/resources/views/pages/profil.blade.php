@extends('layouts.app')

@section('title', 'Profil')

@section('content')
<div class="min-h-screen bg-[#F4F4E3] py-10">
    <h1 class="mb-8 text-center text-2xl font-extrabold text-black">
        Informations de profil
    </h1>

    @if(session('success'))
        <div class="mx-auto mb-4 w-full max-w-md rounded-md border border-green-600/30 bg-green-100 px-4 py-2 text-green-900">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mx-auto mb-4 w-full max-w-md rounded-md border border-red-600/30 bg-red-100 px-4 py-2 text-red-900">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('profil.update') }}" class="mx-auto w-full max-w-md bg-white p-6 shadow-sm">
        @csrf

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold underline underline-offset-4">Email :</label>
                <input name="email" type="email"
                       class="mt-1 w-full rounded-md bg-gray-100 px-4 py-2 outline-none ring-0 focus:bg-white focus:ring-2 focus:ring-black/20"
                       value="{{ old('email', $user->INS_MAIL) }}">
            </div>

            <div>
                <label class="block text-sm font-semibold underline underline-offset-4">Nom :</label>
                <input name="nom" type="text"
                       class="mt-1 w-full rounded-md bg-gray-100 px-4 py-2 outline-none focus:bg-white focus:ring-2 focus:ring-black/20"
                       value="{{ old('nom', $user->INS_NOM) }}">
            </div>

            <div>
                <label class="block text-sm font-semibold underline underline-offset-4">Prénom :</label>
                <input name="prenom" type="text"
                       class="mt-1 w-full rounded-md bg-gray-100 px-4 py-2 outline-none focus:bg-white focus:ring-2 focus:ring-black/20"
                       value="{{ old('prenom', $user->INS_PRENOM) }}">
            </div>

            <div>
                <label class="block text-sm font-semibold underline underline-offset-4">Date de naissance :</label>
               <input name="naissance" type="date"
                    class="mt-1 w-full rounded-md bg-gray-100 px-4 py-2 outline-none focus:bg-white focus:ring-2 focus:ring-black/20"
                    value="{{ old('naissance', $user->INS_NAISSANCE ? \Carbon\Carbon::parse($user->INS_NAISSANCE)->format('Y-m-d') : '') }}">
            </div>

            <div>
                <label class="block text-sm font-semibold underline underline-offset-4">Adresse :</label>
                <input name="adresse" type="text"
                       class="mt-1 w-full rounded-md bg-gray-100 px-4 py-2 outline-none focus:bg-white focus:ring-2 focus:ring-black/20"
                       value="{{ old('adresse', $user->INS_ADRESSE) }}">
            </div>

            <div>
                <label class="block text-sm font-semibold underline underline-offset-4">Ville :</label>
                <input name="ville" type="text"
                       class="mt-1 w-full rounded-md bg-gray-100 px-4 py-2 outline-none focus:bg-white focus:ring-2 focus:ring-black/20"
                       value="{{ old('ville', $user->INS_VILLE) }}">
            </div>

            <div>
                <label class="block text-sm font-semibold underline underline-offset-4">Code postal :</label>
                <input name="cp" type="number"
                       class="mt-1 w-full rounded-md bg-gray-100 px-4 py-2 outline-none focus:bg-white focus:ring-2 focus:ring-black/20"
                       value="{{ old('cp', $user->INS_CODE_PO) }}">
            </div>

            <div>
                <label class="block text-sm font-semibold underline underline-offset-4">Numéro de téléphone :</label>
                <input name="tel" type="text"
                       class="mt-1 w-full rounded-md bg-gray-100 px-4 py-2 outline-none focus:bg-white focus:ring-2 focus:ring-black/20"
                       value="{{ old('tel', $user->INS_TEL) }}">
            </div>

            <div>
                <label class="block text-sm font-semibold underline underline-offset-4">Numéro de licence :</label>
                <input name="licence" type="text"
                       class="mt-1 w-full rounded-md bg-gray-100 px-4 py-2 outline-none focus:bg-white focus:ring-2 focus:ring-black/20"
                       value="{{ old('licence', $user->INS_NUM_LICENCE) }}">
            </div>
        </div>

                <button type="submit"
                class="mt-6 w-full rounded-md bg-[#7FC6A4] py-3 text-center font-bold text-black">
            Modifier
        </button>
    </form>

    <div class="mx-auto mt-6 w-full max-w-md">
        <form method="POST"
              action="{{ route('profil.delete') }}"
              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.');">
            @csrf
            @method('DELETE')

            <button type="submit"
                    class="w-full rounded-md bg-[#D66A6A] py-3 font-bold text-black">
                Supprimer le compte
            </button>
        </form>
    </div>

    </form>

   

</div>
@endsection
