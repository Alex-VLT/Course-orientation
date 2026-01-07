@extends('layouts.app')

@section('title', 'Créer une course')

@section('content')
<div class="min-h-screen py-10">
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-md shadow-sm">
        <h1 class="text-2xl font-bold mb-4">Créer une course pour le raid « {{ $raid->RAID_NOM }} »</h1>

        @if($errors->any())
            <div class="mb-4 rounded-md border border-red-200 bg-red-50 p-3 text-red-900">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('race.store', $raid->RAID_NUM) }}">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold">Nom de la course</label>
                    <input name="COU_NOM" value="{{ old('COU_NOM') }}" class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold">Date départ</label>
                        <input name="COU_DATE_DEPART" type="datetime-local" value="{{ old('COU_DATE_DEPART') }}" class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold">Date fin</label>
                        <input name="COU_DATE_FIN" type="datetime-local" value="{{ old('COU_DATE_FIN') }}" class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold">Responsable de la course (adhérent)</label>
                    <select name="INS_ID" class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2">
                        @foreach($responsibles as $r)
                            <option value="{{ $r->INS_ID }}">{{ $r->INS_PRENOM }} {{ $r->INS_NOM }} — {{ $r->INS_NUM_LICENCE ?? '' }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end">
                    <button class="rounded-md bg-black px-4 py-2 text-white">Créer la course</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
