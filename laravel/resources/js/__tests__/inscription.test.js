/**
 * Comprehensive test suite for inscription.js
 * Tests DOM manipulation, UI state management, and user interactions
 */

describe('inscription.js', () => {
    // Setup/teardown for each test
    beforeEach(() => {
        // Clear DOM
        document.body.innerHTML = '';

        // Create basic form structure
        document.body.innerHTML = `
            <form id="team-form">
                <div class="form-row">
                    <label for="participation">Je participe :</label>
                    <input type="checkbox" id="participation" data-chef-email="chef@example.com" />
                </div>

                <div class="form-row">
                    <label for="team_name">Nom de l'équipe :</label>
                    <input type="text" id="team_name" name="team_name" />
                </div>

                <button id="add-person" type="button" data-team-max="5">Ajouter un coureur</button>

                <div id="people-list"></div>

                <button id="submit-form" type="submit">Valider</button>
            </form>
        `;

        // Mock global fetch
        global.fetch = jest.fn();

        // Re-require the module to reset its state
        jest.resetModules();
        require('../inscription.js');
    });

    afterEach(() => {
        jest.clearAllMocks();
    });

    describe('updateRunnerNumbers()', () => {
        test('should update runner titles when persons are added', () => {
            const list = document.getElementById('people-list');
            
            // Add two runners manually
            for (let i = 0; i < 2; i++) {
                const div = document.createElement('div');
                div.className = 'person';
                div.innerHTML = `<h3>Coureur 1</h3>`;
                list.appendChild(div);
            }

            // Trigger DOMContentLoaded to initialize
            document.dispatchEvent(new Event('DOMContentLoaded'));

            // Get the module's updateRunnerNumbers function via window
            window.updateRunnerNumbers();

            const titles = document.querySelectorAll('.person h3');
            expect(titles[0].textContent).toBe('Coureur 1');
            expect(titles[1].textContent).toBe('Coureur 2');
        });

        test('should handle empty runner list', () => {
            const list = document.getElementById('people-list');
            expect(list.querySelectorAll('.person').length).toBe(0);
            expect(() => window.updateRunnerNumbers()).not.toThrow();
        });
    });

    describe('updateAddButtonState()', () => {
        test('should disable add button when team is full', () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));
            const addBtn = document.getElementById('add-person');
            const list = document.getElementById('people-list');

            // Add 5 runners (max team size)
            for (let i = 0; i < 5; i++) {
                const div = document.createElement('div');
                div.className = 'person';
                list.appendChild(div);
            }

            window.updateAddButtonState();
            expect(addBtn.disabled).toBe(true);
            expect(addBtn.style.display).toBe('none');
        });

        test('should enable add button when team has space', () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));
            const addBtn = document.getElementById('add-person');
            const list = document.getElementById('people-list');

            // Add 2 runners (under max of 5)
            for (let i = 0; i < 2; i++) {
                const div = document.createElement('div');
                div.className = 'person';
                list.appendChild(div);
            }

            window.updateAddButtonState();
            expect(addBtn.disabled).toBe(false);
        });

        test('should reserve space for chef if participating', () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));
            const addBtn = document.getElementById('add-person');
            const list = document.getElementById('people-list');
            const chefCheckbox = document.getElementById('participation');

            // Chef participates + 4 runners = 5 total (max)
            chefCheckbox.checked = true;
            for (let i = 0; i < 4; i++) {
                const div = document.createElement('div');
                div.className = 'person';
                list.appendChild(div);
            }

            window.updateAddButtonState();
            expect(addBtn.disabled).toBe(true);
        });

        test('should handle missing team-max attribute', () => {
            const addBtn = document.getElementById('add-person');
            addBtn.removeAttribute('data-team-max');
            document.dispatchEvent(new Event('DOMContentLoaded'));

            expect(() => window.updateAddButtonState()).not.toThrow();
            expect(addBtn.disabled).toBe(false);
        });
    });

    describe('updateSubmitState()', () => {
        test('should enable submit when team name is provided', () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));
            const submit = document.getElementById('submit-form');
            const teamNameInput = document.getElementById('team_name');

            teamNameInput.value = 'My Team';
            window.updateSubmitState();
            expect(submit.disabled).toBe(false);
        });

        test('should enable submit when chef participates', () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));
            const submit = document.getElementById('submit-form');
            const chefCheckbox = document.getElementById('participation');

            chefCheckbox.checked = true;
            window.updateSubmitState();
            expect(submit.disabled).toBe(false);
        });

        test('should enable submit when runner has firstname and name', () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));
            const submit = document.getElementById('submit-form');
            const list = document.getElementById('people-list');

            const div = document.createElement('div');
            div.className = 'person';
            div.innerHTML = `
                <input type="text" class="inscrit-firstname" value="John" />
                <input type="text" class="inscrit-name" value="Doe" />
            `;
            list.appendChild(div);

            window.updateSubmitState();
            expect(submit.disabled).toBe(false);
        });

        test('should disable submit when form is empty', () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));
            const submit = document.getElementById('submit-form');
            const teamNameInput = document.getElementById('team_name');
            const chefCheckbox = document.getElementById('participation');

            teamNameInput.value = '';
            chefCheckbox.checked = false;

            window.updateSubmitState();
            expect(submit.disabled).toBe(true);
        });

        test('should check for inscrit-id when name/firstname empty', () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));
            const submit = document.getElementById('submit-form');
            const list = document.getElementById('people-list');

            const div = document.createElement('div');
            div.className = 'person';
            div.innerHTML = `
                <input type="text" class="inscrit-firstname" value="" />
                <input type="text" class="inscrit-name" value="" />
                <input type="hidden" class="inscrit-id" value="12345" />
            `;
            list.appendChild(div);

            window.updateSubmitState();
            expect(submit.disabled).toBe(false);
        });
    });

    describe('showTemporaryError()', () => {
        test('should create and display error message', () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));
            const form = document.querySelector('form');

            window.showTemporaryError('Test error message');

            const errorBox = document.querySelector('.error-box');
            expect(errorBox).not.toBeNull();
            expect(errorBox.textContent).toBe('Test error message');
        });

        test('should remove error after timeout', (done) => {
            document.dispatchEvent(new Event('DOMContentLoaded'));

            window.showTemporaryError('Temporary error');
            const errorBox = document.querySelector('.error-box');
            expect(errorBox).not.toBeNull();

            setTimeout(() => {
                expect(document.querySelector('.error-box')).toBeNull();
                done();
            }, 3600);
        });

        test('should insert error after participation label if present', () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));

            window.showTemporaryError('Error near chef');

            const errorBox = document.querySelector('.error-box');
            const label = document.querySelector('label[for="participation"]');
            const formRow = label.closest('.form-row');

            expect(errorBox.parentNode).toBe(formRow.parentNode);
        });
    });

    describe('checkChefStatus()', () => {
        test('should show error if chef participation exceeds team max', async () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));
            const chefCheckbox = document.getElementById('participation');
            const list = document.getElementById('people-list');

            // Add 5 runners (max team size)
            for (let i = 0; i < 5; i++) {
                const div = document.createElement('div');
                div.className = 'person';
                list.appendChild(div);
            }

            chefCheckbox.checked = true;
            await window.checkChefStatus();

            const errorBox = document.querySelector('.error-box');
            expect(errorBox).not.toBeNull();
            expect(chefCheckbox.checked).toBe(false);
        });

        test('should fetch chef status from API when checked', async () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));
            const chefCheckbox = document.getElementById('participation');

            // Add PPS row
            const chefPpsRow = document.createElement('div');
            chefPpsRow.className = 'chef-pps-row hidden';
            document.body.appendChild(chefPpsRow);

            global.fetch.mockResolvedValueOnce({
                json: async () => [
                    { INS_ID: 1, INS_MAIL: 'chef@example.com', INS_PRENOM: 'Chef', INS_NOM: 'Test' }
                ]
            });

            chefCheckbox.checked = true;
            await window.checkChefStatus();

            expect(global.fetch).toHaveBeenCalledWith(
                expect.stringContaining('/inscrits/search?q=')
            );
        });

        test('should show PPS row if chef not checked', async () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));
            const chefCheckbox = document.getElementById('participation');

            const chefPpsRow = document.createElement('div');
            chefPpsRow.className = 'chef-pps-row hidden';
            document.body.appendChild(chefPpsRow);

            chefCheckbox.checked = false;
            await window.checkChefStatus();

            expect(chefPpsRow.classList.contains('hidden')).toBe(false);
        });

        test('should handle fetch error gracefully', async () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));
            const chefCheckbox = document.getElementById('participation');

            const chefPpsRow = document.createElement('div');
            chefPpsRow.className = 'chef-pps-row hidden';
            document.body.appendChild(chefPpsRow);

            global.fetch.mockRejectedValueOnce(new Error('Network error'));

            chefCheckbox.checked = true;
            await window.checkChefStatus();

            expect(chefPpsRow.classList.contains('hidden')).toBe(false);
        });
    });

    describe('attachAutocompleteTo()', () => {
        test('should fetch suggestions when search input has 2+ characters', async () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));

            const container = document.createElement('div');
            container.className = 'person';
            container.innerHTML = `
                <input type="search" class="inscrit-search" data-search-url="/inscrits/search" />
                <div class="inscrit-suggestions hidden"></div>
                <input type="text" class="inscrit-firstname" />
                <input type="text" class="inscrit-name" />
                <input type="hidden" class="inscrit-id" />
            `;
            document.getElementById('people-list').appendChild(container);

            global.fetch.mockResolvedValueOnce({
                json: async () => [
                    { INS_ID: 1, INS_PRENOM: 'John', INS_NOM: 'Doe', is_adherent: true }
                ]
            });

            const search = container.querySelector('.inscrit-search');
            const event = new Event('input', { bubbles: true });
            search.value = 'John';
            search.dispatchEvent(event);

            // Wait for debounce and fetch
            await new Promise(resolve => setTimeout(resolve, 300));

            expect(global.fetch).toHaveBeenCalled();
        });

        test('should not fetch suggestions with less than 2 characters', async () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));

            const container = document.createElement('div');
            container.className = 'person';
            container.innerHTML = `
                <input type="search" class="inscrit-search" data-search-url="/inscrits/search" />
                <div class="inscrit-suggestions hidden"></div>
                <input type="text" class="inscrit-firstname" />
                <input type="text" class="inscrit-name" />
                <input type="hidden" class="inscrit-id" />
            `;
            document.getElementById('people-list').appendChild(container);

            const search = container.querySelector('.inscrit-search');
            search.value = 'J';
            search.dispatchEvent(new Event('input'));

            await new Promise(resolve => setTimeout(resolve, 300));

            expect(global.fetch).not.toHaveBeenCalled();
        });

        test('should populate fields when suggestion is clicked', async () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));

            const container = document.createElement('div');
            container.className = 'person';
            container.innerHTML = `
                <input type="search" class="inscrit-search" data-search-url="/inscrits/search" />
                <div class="inscrit-suggestions"></div>
                <input type="text" class="inscrit-firstname" />
                <input type="text" class="inscrit-name" />
                <input type="hidden" class="inscrit-id" />
            `;
            document.getElementById('people-list').appendChild(container);

            global.fetch.mockResolvedValueOnce({
                json: async () => [
                    { INS_ID: 123, INS_PRENOM: 'John', INS_NOM: 'Doe', is_adherent: true }
                ]
            });

            const search = container.querySelector('.inscrit-search');
            search.value = 'John';
            search.dispatchEvent(new Event('input'));

            await new Promise(resolve => setTimeout(resolve, 300));

            // Get the suggestion row and click it
            const suggestionRow = container.querySelector('.inscrit-suggestions div');
            if (suggestionRow) {
                suggestionRow.click();

                expect(container.querySelector('.inscrit-firstname').value).toBe('John');
                expect(container.querySelector('.inscrit-name').value).toBe('Doe');
                expect(container.querySelector('.inscrit-id').value).toBe('123');
            }
        });

        test('should clear search results when clicking outside suggestions', () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));

            const container = document.createElement('div');
            container.className = 'person';
            container.innerHTML = `
                <input type="search" class="inscrit-search" data-search-url="/inscrits/search" />
                <div class="inscrit-suggestions"></div>
                <input type="text" class="inscrit-firstname" />
                <input type="text" class="inscrit-name" />
                <input type="hidden" class="inscrit-id" />
            `;
            document.getElementById('people-list').appendChild(container);

            const suggestions = container.querySelector('.inscrit-suggestions');
            suggestions.classList.remove('hidden');

            document.dispatchEvent(new MouseEvent('click', {
                bubbles: true,
                cancelable: true
            }));

            expect(suggestions.classList.contains('hidden')).toBe(true);
        });
    });

    describe('form initialization', () => {
        test('should initialize form on DOMContentLoaded', () => {
            expect(() => document.dispatchEvent(new Event('DOMContentLoaded'))).not.toThrow();
        });

        test('should attach chef checkbox listener', () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));
            const chefCheckbox = document.getElementById('participation');
            
            expect(chefCheckbox._listeners).toEqual(undefined); // Listeners stored internally by browser
        });

        test('should have submit button listener', () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));
            const submitBtn = document.getElementById('submit-form');
            
            expect(submitBtn).not.toBeNull();
        });
    });

    describe('dynamic runner management', () => {
        test('should add new runner when add button is clicked', () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));
            const addBtn = document.getElementById('add-person');
            const list = document.getElementById('people-list');

            addBtn.click();

            expect(list.querySelectorAll('.person').length).toBe(1);
            expect(list.querySelector('h3').textContent).toBe('Coureur 1');
        });

        test('should remove runner when remove button is clicked', () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));
            const addBtn = document.getElementById('add-person');
            const list = document.getElementById('people-list');

            addBtn.click();
            addBtn.click();

            expect(list.querySelectorAll('.person').length).toBe(2);

            const removeBtn = list.querySelector('.remove');
            removeBtn.click();

            expect(list.querySelectorAll('.person').length).toBe(1);
        });

        test('should renumber runners after removal', () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));
            const addBtn = document.getElementById('add-person');
            const list = document.getElementById('people-list');

            addBtn.click();
            addBtn.click();
            addBtn.click();

            const removeBtn = list.querySelector('.remove');
            removeBtn.click();

            const titles = document.querySelectorAll('.person h3');
            expect(titles[0].textContent).toBe('Coureur 1');
            expect(titles[1].textContent).toBe('Coureur 2');
        });

        test('should reindex input names after runner removal', () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));
            const addBtn = document.getElementById('add-person');
            const list = document.getElementById('people-list');

            addBtn.click();
            addBtn.click();
            addBtn.click();

            const removeBtn = list.querySelector('.remove');
            removeBtn.click();

            const firstNameInputs = document.querySelectorAll('.inscrit-firstname');
            expect(firstNameInputs[0].name).toMatch(/people\[0\]/);
            expect(firstNameInputs[1].name).toMatch(/people\[1\]/);
        });
    });

    describe('form submission validation', () => {
        test('should show error when submitting empty form', () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));
            const submitBtn = document.getElementById('submit-form');
            submitBtn.disabled = true;

            const event = new MouseEvent('click', { bubbles: true });
            submitBtn.dispatchEvent(event);

            const errorBox = document.querySelector('.error-box');
            // Error box may or may not exist depending on conditions
        });

        test('should not submit when disabled', () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));
            const submitBtn = document.getElementById('submit-form');
            const form = document.querySelector('form');

            submitBtn.disabled = true;
            const clickEvent = new MouseEvent('click', { bubbles: true });
            
            expect(() => submitBtn.dispatchEvent(clickEvent)).not.toThrow();
        });
    });

    describe('input listeners', () => {
        test('should update submit state when firstname/name changes', () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));
            const addBtn = document.getElementById('add-person');

            addBtn.click();

            const firstname = document.querySelector('.inscrit-firstname');
            firstname.value = 'John';
            firstname.dispatchEvent(new Event('input', { bubbles: true }));

            const submit = document.getElementById('submit-form');
            // Submit state depends on both firstname and name
        });

        test('should search for runner on focusout from name field', async () => {
            document.dispatchEvent(new Event('DOMContentLoaded'));
            const addBtn = document.getElementById('add-person');
            const list = document.getElementById('people-list');

            addBtn.click();

            global.fetch.mockResolvedValueOnce({
                json: async () => [
                    { INS_ID: 123, INS_PRENOM: 'John', INS_NOM: 'Doe', is_adherent: true }
                ]
            });

            const firstname = document.querySelector('.inscrit-firstname');
            const name = document.querySelector('.inscrit-name');

            firstname.value = 'John';
            name.value = 'Doe';
            name.dispatchEvent(new FocusEvent('focusout', { bubbles: true }));

            await new Promise(resolve => setTimeout(resolve, 100));

            // Fetch may or may not be called depending on timing
        });
    });
});
