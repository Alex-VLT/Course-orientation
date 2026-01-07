@extends('layouts.app')

@section('title', 'Mon espace — Gérer mes raids et courses')

@section('content')
<div class="min-h-screen w-full px-4 py-10 lg:py-14">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-extrabold text-black">Gestion de mes courses</h1>

        <div class="mt-6 space-y-3">
            @if($courses->isEmpty())
                <div class="text-sm text-black/60">Vous ne gérez aucune course pour le moment.</div>
            @else
                <div class="grid grid-cols-1 gap-3">
                    @foreach($courses as $course)
                        <div class="flex items-center justify-between gap-4 rounded-md border border-black/10 bg-white/40 px-4 py-3">
                            <div class="min-w-0">
                                <div class="truncate font-semibold text-black">{{ $course->COU_NOM }}</div>
                                <div class="mt-1 text-sm text-black/60">
                                    {{ optional($course->COU_DATE_DEPART)->format('d/m/Y H:i') }} → {{ optional($course->COU_DATE_FIN)->format('d/m/Y H:i') }}
                                    • Durée: {{ $course->COU_DUREE }} min • Difficulté: {{ $course->COU_DIFFICULTE }}
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('race.show', $course->COU_NUM) }}" class="shrink-0 rounded-md bg-black px-4 py-2 text-sm font-semibold text-white hover:bg-black/90">Voir</a>
                                <a href="#" class="shrink-0 rounded-md border px-3 py-2 text-sm">Modifier</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <hr class="my-10 border-black/5">

        <div class="flex items-center justify-between">
            <h2 class="text-3xl font-extrabold text-black">Gérer mes raids</h2>
            @if($managesClub)
                <a href="{{ route('raids.create') }}" class="rounded-md bg-[#7DC2A5] px-4 py-2 text-sm font-semibold">Créer un raid</a>
            @endif
        </div>
        <div class="mt-6 space-y-3">
            @if(!$managesClub)
                <div class="text-sm text-black/60">Vous ne pouvez pas encore gérer de raid car vous n'êtes gérant d'aucun club.</div>
                <div class="mt-3 text-sm text-black/70">Pour pouvoir gérer un raid, vous devez être gérant d'au moins un club. Si vous pensez devoir avoir ce rôle, contactez l'administrateur ou mettez à jour votre profil.</div>
            @else
                @if($raids->isEmpty())
                    <div class="text-sm text-black/60">Vous ne gérez aucun raid pour le moment.</div>
                @else
                    <div class="grid grid-cols-1 gap-3">
                        @foreach($raids as $raid)
                            <div class="flex items-center justify-between gap-4 rounded-md border border-black/10 bg-white/40 px-4 py-3">
                                <div class="min-w-0">
                                    <div class="truncate font-semibold text-black">{{ $raid->RAID_NOM }}</div>
                                    <div class="mt-1 text-sm text-black/60">
                                        {{ optional($raid->RAID_DATE_DEBUT)->format('d/m/Y') }} → {{ optional($raid->RAID_DATE_FIN)->format('d/m/Y') }}
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <a href="{{ route('raid.show', $raid->RAID_NUM) }}" class="shrink-0 rounded-md bg-black px-4 py-2 text-sm font-semibold text-white hover:bg-black/90">Voir</a>
                                    <a href="#" class="shrink-0 rounded-md border px-3 py-2 text-sm">Modifier</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
