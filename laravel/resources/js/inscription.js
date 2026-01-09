/**
 * inscription.js - petit module client pour gérer l'UI du formulaire d'inscription.
 * Objectifs :
 * - gérer l'ajout/suppression de coureurs
 * - gérer l'état du bouton d'ajout et du submit
 * - fournir un autocomplete minimal pour rechercher un inscrit
 */

let list = null;

/**
 * Renumérote les titres des coureurs affichés (Coureur 1, Coureur 2, ...).
 */
function updateRunnerNumbers() {
    const runners = list ? list.querySelectorAll('.person') : [];
    runners.forEach((runner, index) => {
        const title = runner.querySelector('h3');
        if (title) title.textContent = `Coureur ${index + 1}`;
    });
}

/**
 * Active/désactive le bouton "Ajouter un coureur" en fonction du nombre max autorisé.
 */
function updateAddButtonState() {
    const addBtn = document.getElementById('add-person');
    if (!addBtn || !list) return;
    const teamMaxAttr = addBtn.dataset.teamMax;
    const teamMax = teamMaxAttr ? parseInt(teamMaxAttr, 10) : null;
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
    addBtn.style.display = (current >= allowed) ? 'none' : '';
    addBtn.disabled = (current >= allowed);
}

/**
 * Active/désactive le bouton de submit selon la présence minimale d'information.
 */
function updateSubmitState() {
    const submit = document.getElementById('submit-form');
    if (!submit || !list) return;
    const persons = list.querySelectorAll('.person');
    const teamNameInput = document.getElementById('team_name');
    if (teamNameInput && (teamNameInput.value || '').trim() !== '') {
        submit.disabled = false;
        return;
    }
    if (persons.length === 0) {
        submit.disabled = false;
        return;
    }
    const chefCheckbox = document.getElementById('participation');
    if (chefCheckbox && chefCheckbox.checked) {
        submit.disabled = false;
        return;
    }
    let anyValid = false;
    for (const p of persons) {
        const insId = p.querySelector('.inscrit-id');
        const fn = p.querySelector('.inscrit-firstname');
        const nm = p.querySelector('.inscrit-name');
        if (insId && (insId.value || '').trim() !== '') { anyValid = true; break; }
        if (fn && nm && (fn.value || '').trim() !== '' && (nm.value || '').trim() !== '') { anyValid = true; break; }
    }
    submit.disabled = !anyValid;
}

/**
 * Affiche une erreur temporaire inline près du formulaire.
 * @param {string} msg
 */
function showTemporaryError(msg) {
    const label = document.querySelector('label[for="participation"]');
    let insertAfter = null;
    if (label) insertAfter = label.closest('.form-row');
    const err = document.createElement('div');
    err.className = 'error-box';
    err.style.marginTop = '8px';
    err.textContent = msg;
    if (insertAfter && insertAfter.parentNode) {
        insertAfter.parentNode.insertBefore(err, insertAfter.nextSibling);
    } else {
        const form = document.querySelector('form');
        if (form) form.insertBefore(err, form.firstChild);
    }
    setTimeout(()=>{ err.remove(); }, 3500);
}

/**
 * Vérifie l'état d'adhésion du chef via l'API et ajuste l'UI (PPS).
 */
async function checkChefStatus() {
    const chefCheckbox = document.getElementById('participation');
    if (!chefCheckbox || !list) return;
    const addBtn = document.getElementById('add-person');
    const teamMaxAttr = addBtn ? addBtn.dataset.teamMax : null;
    const teamMax = teamMaxAttr ? parseInt(teamMaxAttr, 10) : null;
    const current = list.querySelectorAll('.person').length;
    if (teamMax && !isNaN(teamMax) && chefCheckbox.checked) {
        const allowed = teamMax - 1;
        if (current > allowed) {
            showTemporaryError("Cocher 'Je participe' dépasserait le nombre maximal de coureurs pour cette équipe. Supprimez d'abord un coureur ou ne cochez pas la case.");
            chefCheckbox.checked = false;
            updateAddButtonState();
            updateSubmitState();
            return;
        }
    }
    updateAddButtonState();
    updateSubmitState();
    const chefPpsRow = document.querySelector('.chef-pps-row');
    if (!chefPpsRow) return;
    chefPpsRow.classList.remove('hidden');
    if (!chefCheckbox.checked) return;
    const chefEmail = chefCheckbox.dataset.chefEmail || '';
    if (!chefEmail) { chefPpsRow.classList.remove('hidden'); return; }
    try {
        const url = '/inscrits/search?q=' + encodeURIComponent(chefEmail);
        const res = await fetch(url);
        const listResp = await res.json();
        if (!Array.isArray(listResp) || listResp.length === 0) { chefPpsRow.classList.remove('hidden'); return; }
        let match = listResp.find(i => (i.INS_MAIL || '').toLowerCase() === chefEmail.toLowerCase());
        if (!match) match = listResp[0];
        chefPpsRow.classList.remove('hidden');
    } catch (err) {
        chefPpsRow.classList.remove('hidden');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    list = document.getElementById('people-list');
    const chefCheckbox = document.getElementById('participation');
    if (chefCheckbox) chefCheckbox.addEventListener('change', () => { checkChefStatus(); });
    updateAddButtonState();
    document.querySelectorAll('.person').forEach(attachAutocompleteTo);
    updateSubmitState();
    if (chefCheckbox) checkChefStatus();
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', (ev) => {
            // no-op: keep default submit behaviour so server-side validation runs
        }, {capture: true});
        const submitBtn = document.getElementById('submit-form');
        if (submitBtn) {
            submitBtn.addEventListener('click', (ev) => {
                if (submitBtn.disabled) {
                    ev.preventDefault();
                    const teamNameInput = document.getElementById('team_name');
                    const persons = list ? list.querySelectorAll('.person') : [];
                    const chef = document.getElementById('participation');
                    const chefParticipates = chef ? chef.checked : false;
                    if (!teamNameInput || (teamNameInput.value || '').trim() === '') {
                        showTemporaryError("Le nom de l'équipe est requis si aucun coureur n'est ajouté.");
                    } else if (persons.length === 0 && !chefParticipates) {
                        showTemporaryError("Ajoutez un coureur ou cochez 'Je participe' pour inclure le responsable.");
                    } else {
                        showTemporaryError("Formulaire incomplet.");
                    }
                }
            });
        }
    }
    const addBtn = document.getElementById('add-person');
    if (addBtn && list) {
        addBtn.addEventListener('click', () => {
            const index = list.querySelectorAll('.person').length;
            const div = document.createElement('div');
            div.className = 'person person-card';
            div.innerHTML = `
        <h3 class="font-bold mb-4">Coureur ${index + 1}</h3>

        <div class="form-row">
            <label>Rechercher inscrit :</label>
            <div class="flex-1 relative">
                <input
                    type="search"
                    name="people[${index}][search]"
                    placeholder="Prénom, nom ou email"
                    class="inscrit-search w-full px-3 py-2 border-2 border-black rounded bg-white"
                    data-search-url="/inscrits/search"
                />
                <div class="inscrit-suggestions absolute left-0 right-0 bg-white border border-black/10 mt-1 z-40 hidden"></div>
            </div>
        </div>

        <div class="form-row">
            <label>Prénom :</label>
            <div class="flex-1">
                <input 
                    type="text" 
                    name="people[${index}][firstname]" 
                    required
                    class="inscrit-firstname w-full px-3 py-2 border-2 border-black rounded bg-white"
                />
            </div>
        </div>

        <div class="form-row">
            <label>Nom :</label>
            <div class="flex-1">
                <input 
                    type="text" 
                    name="people[${index}][name]" 
                    required
                    class="inscrit-name w-full px-3 py-2 border-2 border-black rounded bg-white"
                />
            </div>
        </div>

    <div class="form-row pps-row">
            <label>PPS :</label>
            <div class="flex-1">
                <input
                    type="text"
                    name="people[${index}][pps]"
                    class="inscrit-pps w-full px-3 py-2 border-2 border-black rounded bg-white"
                    placeholder="Numéro PPS (obligatoire si non-adhérent)"
                />
                <div class="text-xs text-gray-600">Obligatoire si non-adhérent</div>
            </div>
        </div>

        <input type="hidden" name="people[${index}][ins_id]" class="inscrit-id" value="" />

        <div class="form-row">
            <div style="flex:1"></div>
            <div>
                <button 
                    type="button" 
                    class="remove remove-btn"
                >
                    Supprimer
                </button>
            </div>
        </div>
    `;
            list.appendChild(div);
            updateRunnerNumbers();
            updateAddButtonState();
            updateSubmitState();
            attachAutocompleteTo(div);
        });
    }

    if (list) {
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
                updateSubmitState();
            }
        });

        list.addEventListener('input', (e) => {
            if (e.target.matches('.inscrit-firstname') || e.target.matches('.inscrit-name')) {
                updateSubmitState();
            }
        });

        list.addEventListener('focusout', (e) => {
            if (!e.target.matches('.inscrit-firstname') && !e.target.matches('.inscrit-name')) return;
            setTimeout(() => {
                const container = e.target.closest('.person');
                if (!container) return;
                const fn = (container.querySelector('.inscrit-firstname') || {}).value || '';
                const nm = (container.querySelector('.inscrit-name') || {}).value || '';
                const searchInput = container.querySelector('.inscrit-search');
                const insIdInput = container.querySelector('.inscrit-id');
                if (!fn.trim() || !nm.trim()) return;
                const q = fn.trim() + ' ' + nm.trim();
                const url = (searchInput && searchInput.dataset.searchUrl) ? (searchInput.dataset.searchUrl + '?q=' + encodeURIComponent(q)) : ('/inscrits/search?q=' + encodeURIComponent(q));
                fetch(url).then(r => r.json()).then(list => {
                    if (!Array.isArray(list) || list.length === 0) {
                        if (insIdInput) insIdInput.value = '';
                        return;
                    }
                    const found = list.find(i => ((i.INS_PRENOM||'').toLowerCase() === fn.trim().toLowerCase() && (i.INS_NOM||'').toLowerCase() === nm.trim().toLowerCase()));
                    const pick = found || list[0];
                    if (pick) {
                        if (insIdInput) insIdInput.value = pick.INS_ID || '';
                        container.dataset.isAdherent = (typeof pick.is_adherent !== 'undefined' && pick.is_adherent) ? '1' : '0';
                    }
                }).catch(()=>{ if (insIdInput) insIdInput.value = ''; });
            }, 50);
        });
    }
});

/**
 * Attache l'autocomplete à un conteneur "person" nouvellement créé.
 * @param {Element} container
 */
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
        if (insIdInput) insIdInput.value = '';
        if (firstname) firstname.value = '';
        if (name) name.value = '';
        if (timeout) clearTimeout(timeout);
        if (q.length < 2) { suggestions.classList.add('hidden'); return; }
        timeout = setTimeout(()=>{
            const url = search.dataset.searchUrl + '?q=' + encodeURIComponent(q);
            fetch(url).then(r => r.json()).then(list => {
                suggestions.innerHTML = '';
                if (!Array.isArray(list) || list.length === 0) { suggestions.classList.add('hidden'); return; }
                list.slice(0,5).forEach(item => {
                    const row = document.createElement('div');
                    row.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer';
                    row.textContent = (item.INS_PRENOM||'') + ' ' + (item.INS_NOM||'');
                    row.addEventListener('click', ()=>{
                        if (firstname) firstname.value = item.INS_PRENOM || '';
                        if (name) name.value = item.INS_NOM || '';
                        if (insIdInput) insIdInput.value = item.INS_ID || '';
                        search.value = (item.INS_PRENOM||'') + ' ' + (item.INS_NOM||'');
                        if (typeof item.is_adherent !== 'undefined') {
                            container.dataset.isAdherent = item.is_adherent ? '1' : '0';
                        } else {
                            delete container.dataset.isAdherent;
                        }
                        suggestions.classList.add('hidden');
                        updateAddButtonState();
                        updateSubmitState();
                    });
                    suggestions.appendChild(row);
                });
                suggestions.classList.remove('hidden');
            }).catch(()=>{ suggestions.classList.add('hidden'); });
        }, 250);
    });

    document.addEventListener('click', (ev)=>{
        if (!container.contains(ev.target)) {
            suggestions.classList.add('hidden');
        }
    });
}

