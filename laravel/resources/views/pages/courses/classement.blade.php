@extends('layouts.app')

@section('title', 'Classement : ' . $race->COU_NOM)

@section('content')
<div class="min-h-screen w-full">
    <div class="mx-auto w-full max-w-4xl px-4 py-10 lg:px-6">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <h1 class="truncate text-2xl font-extrabold text-black sm:text-3xl">Classement</h1>
                <div class="mt-1 text-sm text-black/70">
                    {{ $race->COU_NOM }}
                </div>
            </div>

            <div class="shrink-0 flex items-center gap-2">
                <a href="{{ route('race.show', $race->COU_NUM) }}" class="rounded-md border border-black/10 bg-white px-4 py-2 text-sm font-semibold text-black hover:bg-gray-100">
                    Détails
                </a>
                @if(!empty($race->RAID_NUM))
                    <a href="{{ route('raid.show', $race->RAID_NUM) }}" class="rounded-md bg-black px-4 py-2 text-sm font-semibold text-white hover:bg-black/90">
                        Raid
                    </a>
                @endif
            </div>
        </div>

        <div class="mt-8 rounded-md border border-black/10 bg-white">
            @if(!$resultsPublished)
                <div class="px-6 py-5 text-sm text-black/70">
                    Les classements n'ont pas encore été publiés.
                </div>
            @else
                <div class="overflow-hidden">
                    <table class="w-full table-auto">
                        <thead class="bg-black/[0.03]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-black/70">#</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-black/70">Équipe</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-black/70">Points</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-black/70">Temps</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($equipes as $equipe)
                                <tr class="border-t border-black/10">
                                    <td class="px-6 py-4 text-sm font-semibold text-black">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-black">
                                        {{ $equipe->EQU_NOM ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm text-black">
                                        {{ $equipe->EQU_POINTS ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm text-black">
                                        @if(!empty($equipe->EQU_TEMPS) && (int) $equipe->EQU_TEMPS > 0)
                                            @php
                                                $minutes = (int) $equipe->EQU_TEMPS;
                                                $hours = intdiv($minutes, 60);
                                                $remainingMinutes = $minutes % 60;
                                            @endphp

                                            @if($hours > 0)
                                                {{ $hours }}h{{ str_pad((string) $remainingMinutes, 2, '0', STR_PAD_LEFT) }}
                                            @else
                                                {{ $minutes }} min
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
