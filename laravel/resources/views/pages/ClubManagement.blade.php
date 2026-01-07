@extends('layouts.app')
@section('title', 'Gestion des clubs')
@section('content')

<div class="min-h-screen py-12 px-4">
	<div class="max-w-4xl mx-auto">
		<h1 class="text-2xl font-bold mb-6">Gestion des clubs</h1>

		@if(isset($clubs) && $clubs->count())
			<div class="grid gap-4">
				@foreach($clubs as $club)
					<div class="p-4 bg-white dark:bg-[#161615] border border-[#e3e3e0] rounded-sm shadow-sm">
						<h2 class="text-lg font-semibold">{{ $club->CLU_NOM }}</h2>
						@if($club->CLU_ADRESSE)
							<p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $club->CLU_ADRESSE }}</p>
						@endif
						<p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $club->CLU_CODE_POSTAL }} {{ $club->CLU_VILLE }}</p>
                        
                    <button@click="openEdit=true"
                    class="shrink-0 rounded-xl bg-slate-900 px-4 py-2 text-white text-sm font-semibold hover:bg-slate-800">
                    Modifier </button>
					</div>
				@endforeach
			</div>

			<div class="mt-6">
				{{ $clubs->links() }}
			</div>
		@else
			<p class="text-center text-[#706f6c]">Aucun club trouvé.</p>
		@endif
	</div>
    <div class="min-h-screen py-12 px-4" x-data="{ openEdit:false }">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Gestion des clubs</h1>

        @foreach($clubs as $club)
            <div class="p-4 bg-white dark:bg-[#161615] border border-[#e3e3e0] rounded-sm shadow-sm">
                <h2 class="text-lg font-semibold">{{ $club->CLU_NOM }}</h2>

                <button
                    @click="openEdit = true"
                    class="mt-3 rounded-xl bg-slate-900 px-4 py-2 text-white text-sm font-semibold hover:bg-slate-800">
                    Modifier
                </button>
            </div>
        @endforeach
    </div>

    <!-- MODAL -->
    <div
        x-cloak
        x-show="openEdit"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
    >
        <div class="absolute inset-0 bg-black/50" @click="openEdit = false"></div>

        <div class="relative w-full max-w-xl rounded-2xl bg-white shadow-xl border p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold">Modifier le club</h3>
                <button @click="openEdit= false" class="rounded-xl px-3 py-2 text-sm font-semibold hover:bg-slate-100">
                    Fermer
                </button>
            </div>

            <div class="mt-4 text-sm text-slate-600">
                (mets ici ton formulaire d’édition du club)
            </div>
        </div>
    </div>
</div>
</div>

	@endsection

