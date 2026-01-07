@extends('layouts.app')

@section('title', 'Gestion de la course')

@section('content')
<div class="min-h-screen py-10">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-md shadow-sm">
        <h1 class="text-2xl font-bold">Gestion de la course — {{ $race->COU_NOM }}</h1>

        @if(session('success'))
            <div class="mt-4 rounded-md border border-green-200 bg-green-50 p-3 text-green-900">{{ session('success') }}</div>
        @endif

        <div class="mt-6 grid grid-cols-1 gap-6">
            <div class="rounded-md border p-4">
                <h3 class="font-semibold">Validation</h3>
                <p class="text-sm text-black/60">Statut : <strong>{{ $race->COU_VALIDE ? 'Validée' : 'Non validée' }}</strong></p>
                @if(!$race->COU_VALIDE)
                    <form method="POST" action="{{ route('race.validate', $race->COU_NUM) }}" class="mt-3">
                        @csrf
                        <button class="rounded-md bg-[#7DC2A5] px-4 py-2 font-semibold">Valider la course</button>
                    </form>
                @endif
            </div>

            <div class="rounded-md border p-4">
                <h3 class="font-semibold">Générer les dossards</h3>
                <form method="POST" action="{{ route('race.dossards', $race->COU_NUM) }}" class="mt-3 flex gap-3 items-center">
                    @csrf
                    <input name="count" type="number" value="100" min="1" class="rounded-md border px-2 py-1 w-32" />
                    <button class="rounded-md bg-black px-4 py-2 text-white">Générer</button>
                </form>
                <p class="mt-2 text-sm text-black/60">Dossards existants : {{ $race->dossards->count() }}</p>
            </div>

            <div class="rounded-md border p-4">
                <h3 class="font-semibold">Résultats (CSV)</h3>
                <form method="POST" action="{{ route('race.results.upload', $race->COU_NUM) }}" enctype="multipart/form-data" class="mt-3">
                    @csrf
                    <input type="file" name="results" accept=".csv" />
                    <div class="mt-3">
                        <button class="rounded-md bg-[#7DC2A5] px-4 py-2 font-semibold">Téléverser</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
