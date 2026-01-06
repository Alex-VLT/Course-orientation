@extends('layouts.app')
@section('title', 'Formulaire Inscription')
@section('content')
<div class="min-h-screen py-12 px-4">
    <div class="max-w-xl mx-auto">
        <h2 class="text-center text-2xl font-bold mb-8">Responsable de paiement</h2>
        
        <form action="" method="post">
            @csrf
            
            <!-- Checkbox participation -->
            <div class="mb-6 text-center">
                <label class="inline-flex items-center gap-2 font-semibold">
                    <span>Je participe à la course</span>
                    <input 
                        type="checkbox" 
                        id="participation" 
                        name="participation"
                        class="w-5 h-5 border-2 border-black"
                    />
                </label>
            </div>

            <!-- Team name -->
            <div class="mb-6 flex items-center justify-center gap-4">
                <label class="font-semibold">Nom de l'équipe :</label>
                <input 
                    type="text" 
                    name="team_name" 
                    value="{{ old('team_name') }}" 
                    required
                    class="w-64 px-3 py-2 border-2 border-black rounded bg-white"
                />
            </div>
            @error('team_name') 
                <div class="text-red-600 text-sm text-center mb-4">{{ $message }}</div> 
            @enderror

            <!-- Button to add a runner -->
            <div class="mb-8 flex justify-center gap-6">
                <button 
                    type="button" 
                    id="add-person"
                    class="px-6 py-2 border-2 border-black rounded-full bg-white hover:bg-gray-100 font-semibold"
                >
                    + Ajouter un coureur
                </button>
                <button 
                    type="submit"
                    class="px-16 py-2 border-2 border-black rounded-full bg-white hover:bg-gray-800 hover:text-white font-semibold transition-colors">
                    Envoyer
                </button>
            </div>

            <!-- Runner List -->
            <div id="people-list" class="space-y-8">
                @foreach(old('people', [['name'=>'','firstname'=>'','email'=>'','licence'=>'','pps'=>'']]) as $i => $oldPerson)
                <div class="person">
                    <h3 class="font-bold mb-4">Coureur {{ $i + 1 }}</h3>

                    <div class="space-y-3">
                        <!-- FirstName  -->
                        <div class="flex items-center gap-4">
                            <label class="font-semibold w-52 text-right">Prénom :</label>
                            <input 
                                type="text" 
                                name="people[{{ $i }}][firstname]" 
                                value="{{ old("people.$i.firstname") }}" 
                                required
                                class="flex-1 px-3 py-2 border-2 border-black rounded bg-white"
                            />
                        </div>

                        <!-- Name -->
                        <div class="flex items-center gap-4">
                            <label class="font-semibold w-52 text-right">Nom :</label>
                            <input 
                                type="text" 
                                name="people[{{ $i }}][name]" 
                                value="{{ old("people.$i.name") }}" 
                                required
                                class="flex-1 px-3 py-2 border-2 border-black rounded bg-white"
                            />
                        </div>

                        <!-- Numéro PPS -->
                        <div class="flex items-center gap-4 hidden pps-field">
                            <label class="font-semibold w-52 text-right">Numéro PPS ( optionnel ) :</label>
                            <input 
                                type="text" 
                                name="people[{{ $i }}][pps]" 
                                value="{{ old("people.$i.pps") }}"
                                class="flex-1 px-3 py-2 border-2 border-black rounded bg-white"
                            />
                        </div>
                        <div class="mt-4 text-right">
            <button 
                type="button" 
                class="remove px-4 py-1 text-red-600 hover:text-red-800 font-semibold"
            >
                Supprimer ce coureur
            </button>
        </div>
                    @error("people.$i.firstname") 
                        <div class="text-red-600 text-sm">{{ $message }}</div> 
                    @enderror
                    @error("people.$i.name") 
                        <div class="text-red-600 text-sm">{{ $message }}</div> 
                    @enderror
                    @error("people.$i.email") 
                        <div class="text-red-600 text-sm">{{ $message }}</div> 
                    @enderror
                    @error("people.$i.licence") 
                        <div class="text-red-600 text-sm">{{ $message }}</div> 
                    @enderror
                </div>
                @endforeach
            </div>
        </form>
    </div>
</div>
@endsection