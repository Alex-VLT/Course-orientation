const list = document.getElementById('people-list');


function updateRunnerNumbers() {
    const runners = list.querySelectorAll('.person');
    runners.forEach((runner, index) => {
        const title = runner.querySelector('h3');
        if (title) {
            title.textContent = `Coureur ${index + 1}`;
        }
    });
}

document.getElementById('add-person').addEventListener('click', () => {
    const index = list.querySelectorAll('.person').length;
    const div = document.createElement('div');
    div.className = 'person';
    div.innerHTML = `
        <h3 class="font-bold mb-4">Coureur ${index + 1}</h3>

        <div class="space-y-3">
            <!-- Prénom -->
            <div class="flex items-center gap-4">
                <label class="font-semibold w-52 text-right">Prénom :</label>
                <input 
                    type="text" 
                    name="people[${index}][firstname]" 
                    required
                    class="flex-1 px-3 py-2 border-2 border-black rounded bg-white"
                />
            </div>

            <!-- Nom -->
            <div class="flex items-center gap-4">
                <label class="font-semibold w-52 text-right">Nom :</label>
                <input 
                    type="text" 
                    name="people[${index}][name]" 
                    required
                    class="flex-1 px-3 py-2 border-2 border-black rounded bg-white"
                />
            </div>

            <!-- Numéro PPS (caché par défaut) -->
            <div class="flex items-center gap-4 hidden pps-field">
                <label class="font-semibold w-52 text-right">Numéro PPS ( optionnel ) :</label>
                <input 
                    type="text" 
                    name="people[${index}][pps]"
                    class="flex-1 px-3 py-2 border-2 border-black rounded bg-white"
                />
            </div>
        </div>

        <div class="mt-4 text-right">
            <button 
                type="button" 
                class="remove px-4 py-1 text-red-600 hover:text-red-800 font-semibold"
            >
                Supprimer ce coureur
            </button>
        </div>
    `;
    list.appendChild(div);
    updateRunnerNumbers();
    
});

list.addEventListener('click', (e) => {
    if (e.target.matches('.remove')) {
        e.target.closest('.person').remove();
        Array.from(list.querySelectorAll('.person')).forEach((el, i) => {
            el.querySelectorAll('input,select,textarea').forEach(inp => {
                inp.name = inp.name.replace(/\[\d+\]/, `[${i}]`);
            });
        });
        updateRunnerNumbers();
    }
});

