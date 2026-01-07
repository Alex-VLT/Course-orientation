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

function updateAddButtonState() {
    const addBtn = document.getElementById('add-person');
    if (!addBtn) return;
    const teamMaxAttr = addBtn.dataset.teamMax;
    const teamMax = teamMaxAttr ? parseInt(teamMaxAttr, 10) : null;
    // if no limit provided, keep button enabled
    if (!teamMax || isNaN(teamMax) || teamMax <= 0) {
        addBtn.style.display = '';
        addBtn.disabled = false;
        return;
    }

    const chefCheckbox = document.getElementById('participation');
    const chefParticipates = chefCheckbox ? chefCheckbox.checked : false;
    const chefCount = chefParticipates ? 1 : 0;

    const current = list.querySelectorAll('.person').length;
    const allowed = teamMax - chefCount;

    if (current >= allowed) {
        addBtn.style.display = 'none';
        addBtn.disabled = true;
    } else {
        addBtn.style.display = '';
        addBtn.disabled = false;
    }
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
    updateAddButtonState();
    attachAutocompleteTo(div);
    
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
        updateAddButtonState();
    }
});

// react to chef participation checkbox changes
const chefCheckbox = document.getElementById('participation');
if (chefCheckbox) {
    chefCheckbox.addEventListener('change', () => {
        updateAddButtonState();
    });
}

// initial state on load
document.addEventListener('DOMContentLoaded', () => {
    updateAddButtonState();
    // attach autocomplete to existing person elements
    document.querySelectorAll('.person').forEach(attachAutocompleteTo);
});

// Autocomplete helpers
function attachAutocompleteTo(container) {
    if (!container) return;
    const search = container.querySelector('.inscrit-search');
    const suggestions = container.querySelector('.inscrit-suggestions');
    const firstname = container.querySelector('.inscrit-firstname');
    const name = container.querySelector('.inscrit-name');
    const insIdInput = container.querySelector('.inscrit-id');
    if (!search || !suggestions) return;

    let timeout = null;
    search.addEventListener('input', (e) => {
        const q = (e.target.value || '').trim();
        insIdInput && (insIdInput.value = '');
        firstname && (firstname.value = '');
        name && (name.value = '');
        if (timeout) clearTimeout(timeout);
        if (q.length < 2) { suggestions.classList.add('hidden'); return; }
        timeout = setTimeout(()=>{
            const url = search.dataset.searchUrl + '?q=' + encodeURIComponent(q);
            fetch(url).then(r => r.json()).then(list => {
                suggestions.innerHTML = '';
                if (!Array.isArray(list) || list.length === 0) { suggestions.classList.add('hidden'); return; }
                list.forEach(item => {
                    const row = document.createElement('div');
                    row.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer';
                    // show only name in suggestions (do not display email)
                    row.textContent = (item.INS_PRENOM||'') + ' ' + (item.INS_NOM||'');
                    row.addEventListener('click', ()=>{
                        // populate fields
                        if (firstname) firstname.value = item.INS_PRENOM || '';
                        if (name) name.value = item.INS_NOM || '';
                        if (insIdInput) insIdInput.value = item.INS_ID || '';
                        search.value = (item.INS_PRENOM||'') + ' ' + (item.INS_NOM||'');
                        suggestions.classList.add('hidden');
                        updateAddButtonState();
                    });
                    suggestions.appendChild(row);
                });
                suggestions.classList.remove('hidden');
            }).catch(()=>{ suggestions.classList.add('hidden'); });
        }, 250);
    });

    // hide suggestions when clicking outside
    document.addEventListener('click', (ev)=>{
        if (!container.contains(ev.target)) {
            suggestions.classList.add('hidden');
        }
    });
}

