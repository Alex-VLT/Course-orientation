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

function updateSubmitState() {
    const submit = document.getElementById('submit-form');
    if (!submit) return;
    const persons = list.querySelectorAll('.person');
    if (persons.length === 0) {
        // no participants -> disable submit
        submit.disabled = true;
        return;
    }
    // require every person to have firstname and name
    for (const p of persons) {
        const fn = p.querySelector('.inscrit-firstname');
        const nm = p.querySelector('.inscrit-name');
        if (!fn || !nm) { submit.disabled = true; return; }
        if ((fn.value || '').trim() === '' || (nm.value || '').trim() === '') { submit.disabled = true; return; }
    }
    submit.disabled = false;
}

document.getElementById('add-person').addEventListener('click', () => {
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

// react to chef participation checkbox changes
const chefCheckbox = document.getElementById('participation');
if (chefCheckbox) {
    function showTemporaryError(msg) {
        // try to insert after participation row, fallback to top of form
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

    // reusable function: check chef adherent status and show/hide chef PPS accordingly
    async function checkChefStatus() {
        const addBtn = document.getElementById('add-person');
        const teamMaxAttr = addBtn ? addBtn.dataset.teamMax : null;
        const teamMax = teamMaxAttr ? parseInt(teamMaxAttr, 10) : null;
        const current = list.querySelectorAll('.person').length;
        if (teamMax && !isNaN(teamMax) && chefCheckbox.checked) {
            const allowed = teamMax - 1; // chef will occupy one slot
            if (current > allowed) {
                showTemporaryError("Cocher 'Je participe' dépasserait le nombre maximal de coureurs pour cette équipe. Supprimez d'abord un coureur ou ne cochez pas la case.");
                // revert the check
                chefCheckbox.checked = false;
                updateAddButtonState();
                updateSubmitState();
                return;
            }
        }
        updateAddButtonState();
        updateSubmitState();
        // perform an AJAX check for the chef adherent status (by email) so we can decide whether to show/require chef PPS
        const chefPpsRow = document.querySelector('.chef-pps-row');
        const chefPpsInput = document.getElementById('chef_pps');
        const chefEmail = chefCheckbox.dataset.chefEmail || '';
        if (!chefPpsRow) return;
        // show chef PPS always; required state will depend on participation + adherent status
        chefPpsRow.classList.remove('hidden');
        if (!chefCheckbox.checked) {
            // not participating -> do not require PPS (clear requirement)
            if (chefPpsInput) chefPpsInput.removeAttribute('required');
            return;
        }
        // if no email available, assume non-adherent and require the PPS input
        if (!chefEmail) {
            chefPpsRow.classList.remove('hidden');
            if (chefPpsInput) chefPpsInput.setAttribute('required', 'required');
            return;
        }
        try {
            const url = '/inscrits/search?q=' + encodeURIComponent(chefEmail);
            const res = await fetch(url);
            const listResp = await res.json();
                if (!Array.isArray(listResp) || listResp.length === 0) {
                // no match -> require PPS
                chefPpsRow.classList.remove('hidden');
                if (chefPpsInput) chefPpsInput.setAttribute('required', 'required');
                return;
            }
            // prefer exact email match
            let match = listResp.find(i => (i.INS_MAIL || '').toLowerCase() === chefEmail.toLowerCase());
            if (!match) match = listResp[0];
                if (match && typeof match.is_adherent !== 'undefined' && match.is_adherent) {
                    // adherent -> not required
                    chefPpsRow.classList.remove('hidden');
                    if (chefPpsInput) chefPpsInput.removeAttribute('required');
                } else {
                    // not adherent -> require PPS
                    chefPpsRow.classList.remove('hidden');
                    if (chefPpsInput) chefPpsInput.setAttribute('required', 'required');
                }
        } catch (err) {
            // on error, show PPS but do not force requirement (server will validate if needed)
            chefPpsRow.classList.remove('hidden');
            if (chefPpsInput) chefPpsInput.removeAttribute('required');
        }
    }

    chefCheckbox.addEventListener('change', () => {
        checkChefStatus();
    });
}

// initial state on load
document.addEventListener('DOMContentLoaded', () => {
    updateAddButtonState();
    // attach autocomplete to existing person elements
    document.querySelectorAll('.person').forEach(attachAutocompleteTo);
    // Initialize PPS 'required' attribute for server-rendered person rows based on data-is-adherent
    document.querySelectorAll('.person').forEach(container => {
        const isAdh = container.dataset.isAdherent;
        const ppsInput = container.querySelector('.inscrit-pps');
        if (!ppsInput) return;
        if (typeof isAdh !== 'undefined') {
            // when is_adherent=1 -> NOT required; otherwise required
            if (isAdh === '1') {
                ppsInput.removeAttribute('required');
            } else {
                ppsInput.setAttribute('required', 'required');
            }
        } else {
            // unknown: don't require by default (frontend only) — server will enforce if needed
            ppsInput.removeAttribute('required');
        }
    });
    updateSubmitState();
    // initialize chef PPS visibility based on current checkbox state and DB
    if (chefCheckbox) {
        checkChefStatus();
    }
});

// react to firstname/name inputs to enable/disable the submit button
list.addEventListener('input', (e) => {
    if (e.target.matches('.inscrit-firstname') || e.target.matches('.inscrit-name')) {
        updateSubmitState();
    }
});

// after user enters firstname+name (focusout), try to lookup the inscrit in DB and toggle PPS visibility accordingly
list.addEventListener('focusout', (e) => {
    if (!e.target.matches('.inscrit-firstname') && !e.target.matches('.inscrit-name')) return;
    // small delay to allow focus to move between the two inputs
    setTimeout(() => {
        const container = e.target.closest('.person');
        if (!container) return;
        const fn = (container.querySelector('.inscrit-firstname') || {}).value || '';
        const nm = (container.querySelector('.inscrit-name') || {}).value || '';
        const searchInput = container.querySelector('.inscrit-search');
        const insIdInput = container.querySelector('.inscrit-id');
        const ppsRow = container.querySelector('.pps-row');
        const ppsInput = container.querySelector('.inscrit-pps');
        if (!fn.trim() || !nm.trim()) return;
        // query by "prenom nom"
        const q = fn.trim() + ' ' + nm.trim();
        const url = (searchInput && searchInput.dataset.searchUrl) ? (searchInput.dataset.searchUrl + '?q=' + encodeURIComponent(q)) : ('/inscrits/search?q=' + encodeURIComponent(q));
        fetch(url).then(r => r.json()).then(list => {
            if (!Array.isArray(list) || list.length === 0) {
                // no match -> clear ins_id and do not force requirement (unknown)
                if (insIdInput) insIdInput.value = '';
                if (ppsInput) ppsInput.removeAttribute('required');
                return;
            }
            // try to find exact match on both names (case-insensitive)
            const found = list.find(i => ((i.INS_PRENOM||'').toLowerCase() === fn.trim().toLowerCase() && (i.INS_NOM||'').toLowerCase() === nm.trim().toLowerCase()));
            const pick = found || list[0];
            if (pick) {
                if (insIdInput) insIdInput.value = pick.INS_ID || '';
                container.dataset.isAdherent = (typeof pick.is_adherent !== 'undefined' && pick.is_adherent) ? '1' : '0';
                // set PPS required attribute based on adherent status
                if (typeof pick.is_adherent !== 'undefined' && pick.is_adherent) {
                    if (ppsInput) ppsInput.removeAttribute('required');
                } else {
                    if (ppsInput) ppsInput.setAttribute('required', 'required');
                }
            }
        }).catch(()=>{
            if (insIdInput) insIdInput.value = '';
            // on error, leave PPS visible but do not force required (server will validate as needed)
            if (ppsInput) ppsInput.removeAttribute('required');
        });
    }, 50);
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
        // when user types, assume unknown adherent status and show PPS field
    // do NOT reveal PPS while the user is typing; wait for a lookup or autocomplete selection
        if (timeout) clearTimeout(timeout);
        if (q.length < 2) { suggestions.classList.add('hidden'); return; }
        timeout = setTimeout(()=>{
            const url = search.dataset.searchUrl + '?q=' + encodeURIComponent(q);
            fetch(url).then(r => r.json()).then(list => {
                suggestions.innerHTML = '';
                if (!Array.isArray(list) || list.length === 0) { suggestions.classList.add('hidden'); return; }
                // show only first 5 suggestions (make it a bit more generous)
                list.slice(0,5).forEach(item => {
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
                                // set adherent flag on the container (returned by server)
                                if (typeof item.is_adherent !== 'undefined') {
                                    container.dataset.isAdherent = item.is_adherent ? '1' : '0';
                                } else {
                                    delete container.dataset.isAdherent;
                                }

                                // set PPS required attribute based on is_adherent: required when NOT adherent
                                const ppsInput = container.querySelector('.inscrit-pps');
                                if (ppsInput) {
                                    if (typeof item.is_adherent !== 'undefined' && item.is_adherent) {
                                        ppsInput.removeAttribute('required');
                                    } else {
                                        ppsInput.setAttribute('required', 'required');
                                    }
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

    // hide suggestions when clicking outside
    document.addEventListener('click', (ev)=>{
        if (!container.contains(ev.target)) {
            suggestions.classList.add('hidden');
        }
    });
}

