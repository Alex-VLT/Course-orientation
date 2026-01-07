@extends('layouts.app')

@section('title', 'Créer un Raid')

@section('content')
<div class="min-h-screen py-10">
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-md shadow-sm">
        <h1 class="text-2xl font-bold mb-4">Créer un nouveau raid</h1>

        @if($errors->any())
            <div class="mb-4 rounded-md border border-red-200 bg-red-50 p-3 text-red-900">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('raids.store') }}">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold">Nom du raid</label>
                    <input name="RAID_NOM" value="{{ old('RAID_NOM') }}" class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold">Date début</label>
                        <input name="RAID_DATE_DEBUT" type="date" value="{{ old('RAID_DATE_DEBUT') }}" class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold">Date fin</label>
                        <input name="RAID_DATE_FIN" type="date" value="{{ old('RAID_DATE_FIN') }}" class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold">Club organisateur</label>
                    <select name="CLU_NUM" class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2">
                        @foreach($clubs as $club)
                            <option value="{{ $club->CLU_NUM }}">{{ $club->CLU_NOM }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold">Date début des inscriptions</label>
                        <input name="RAID_DATE_DEBUT_INSCRI" type="date" value="{{ old('RAID_DATE_DEBUT_INSCRI') }}" class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold">Date fin des inscriptions</label>
                        <input name="RAID_DATE_FIN_INSCRI" type="date" value="{{ old('RAID_DATE_FIN_INSCRI') }}" class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold">Responsable du raid (adhérent)</label>
                    <select name="INS_ID" class="mt-1 w-full rounded-md bg-gray-100 px-3 py-2">
                        @foreach($responsibles as $r)
                            <option value="{{ $r->INS_ID }}">{{ $r->INS_PRENOM }} {{ $r->INS_NOM }} — {{ $r->INS_NUM_LICENCE ?? '' }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end">
                    <button class="rounded-md bg-black px-4 py-2 text-white">Créer</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
