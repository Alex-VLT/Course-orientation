
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