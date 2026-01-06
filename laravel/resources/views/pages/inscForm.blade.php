@extends('layouts.app')

@section('title', 'Formulaire Inscription') {{--  Titre de la page --}}


@section('content')
    <form action=""method="post">
  @csrf
  <div id ="team-name">
    <label>Nom de l'équipe
      <input type="text" name="team_name" value="{{ old('team_name') }}" required>
    </label>
    @error('team_name') <div class="text-red">{{ $message }}</div> @enderror
    </div>
  <div id="people-list">
    @foreach(old('people', [['name'=>'','firstname'=>'']]) as $i => $oldPerson)
      <div class="person">
        <label>Nom
          <input type="text" name="people[{{ $i }}][name]" value="{{ old("people.$i.name") }}" required>
        </label>
        <label>Prénom
          <input type="text" name="people[{{ $i }}][firstname]" value="{{ old("people.$i.firstname") }}" required>
        </label>
        @error("people.$i.name") <div class="text-red">{{ $message }}</div> @enderror
        @error("people.$i.firstname") <div class="text-red">{{ $message }}</div> @enderror
        <button type="button" class="remove">Supprimer</button>
      </div>
    @endforeach
  </div>

  <button type="button" id="add-person">Ajouter une personne</button>
  <button type="submit">Envoyer</button>
</form>
<script>
    
  const list = document.getElementById('people-list');

  document.getElementById('add-person').addEventListener('click', () => {
    const index = list.querySelectorAll('.person').length;
    const div = document.createElement('div');
    div.className = 'person';
    div.innerHTML = `
      <label>Nom <input type="text" name="people[${index}][name]" required></label>
      <label>Prénom <input type="text" name="people[${index}][firstname]" required></label>
      <button type="button" class="remove">Supprimer</button>
    `;
    list.appendChild(div);
  });

  list.addEventListener('click', (e) => {
    if (e.target.matches('.remove')) {
      e.target.closest('.person').remove();
      // ré-indexer les names pour éviter les trous
      Array.from(list.querySelectorAll('.person')).forEach((el, i) => {
        el.querySelectorAll('input,select,textarea').forEach(inp => {
          inp.name = inp.name.replace(/\[\d+\]/, `[${i}]`);
        });
      });
    }
  });
</script>
@endsection
