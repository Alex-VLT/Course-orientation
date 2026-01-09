# JavaScript Test Examples & Scenarios

This document shows concrete examples of test scenarios and how they validate the inscription.js module.

## Scenario 1: Team Size Management

### Business Rule
A team has a maximum size (e.g., 5 members). The chef counts as one member if participating.

### Test Cases

**Case 1A: Add button enabled when team has space**
```javascript
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
    
    // Expected: Button should be enabled
    expect(addBtn.disabled).toBe(false);
});
```

**Case 1B: Add button disabled when team is full**
```javascript
test('should disable add button when team is full', () => {
    document.dispatchEvent(new Event('DOMContentLoaded'));
    const addBtn = document.getElementById('add-person');
    const list = document.getElementById('people-list');

    // Add 5 runners (at max)
    for (let i = 0; i < 5; i++) {
        const div = document.createElement('div');
        div.className = 'person';
        list.appendChild(div);
    }

    window.updateAddButtonState();
    
    // Expected: Button should be disabled and hidden
    expect(addBtn.disabled).toBe(true);
    expect(addBtn.style.display).toBe('none');
});
```

**Case 1C: Chef participation reserves slot**
```javascript
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
    
    // Expected: Button disabled because 1 (chef) + 4 = 5 (max)
    expect(addBtn.disabled).toBe(true);
});
```

**Case 1D: Error shown if chef overflows**
```javascript
test('should show error if chef participation exceeds team max', async () => {
    document.dispatchEvent(new Event('DOMContentLoaded'));
    const chefCheckbox = document.getElementById('participation');
    const list = document.getElementById('people-list');

    // Add 5 runners (at max)
    for (let i = 0; i < 5; i++) {
        const div = document.createElement('div');
        div.className = 'person';
        list.appendChild(div);
    }

    // Try to check chef participation (would exceed max)
    chefCheckbox.checked = true;
    await window.checkChefStatus();

    // Expected: Error shown and chef unchecked
    const errorBox = document.querySelector('.error-box');
    expect(errorBox).not.toBeNull();
    expect(chefCheckbox.checked).toBe(false);
});
```

## Scenario 2: Form Validation

### Business Rule
Submit button is enabled only if:
- Team has a name, OR
- Chef participates, OR
- At least one runner is complete (firstname + name, or inscrit_id)

### Test Cases

**Case 2A: Submit enabled with team name**
```javascript
test('should enable submit when team name is provided', () => {
    document.dispatchEvent(new Event('DOMContentLoaded'));
    const submit = document.getElementById('submit-form');
    const teamNameInput = document.getElementById('team_name');

    // Only team name is filled
    teamNameInput.value = 'My Team';
    window.updateSubmitState();
    
    // Expected: Submit button enabled
    expect(submit.disabled).toBe(false);
});
```

**Case 2B: Submit enabled with chef participation**
```javascript
test('should enable submit when chef participates', () => {
    document.dispatchEvent(new Event('DOMContentLoaded'));
    const submit = document.getElementById('submit-form');
    const chefCheckbox = document.getElementById('participation');

    // Only chef participation checked
    chefCheckbox.checked = true;
    window.updateSubmitState();
    
    // Expected: Submit button enabled
    expect(submit.disabled).toBe(false);
});
```

**Case 2C: Submit enabled with complete runner info**
```javascript
test('should enable submit when runner has firstname and name', () => {
    document.dispatchEvent(new Event('DOMContentLoaded'));
    const submit = document.getElementById('submit-form');
    const list = document.getElementById('people-list');

    // Add runner with firstname and name
    const div = document.createElement('div');
    div.className = 'person';
    div.innerHTML = `
        <input type="text" class="inscrit-firstname" value="John" />
        <input type="text" class="inscrit-name" value="Doe" />
    `;
    list.appendChild(div);

    window.updateSubmitState();
    
    // Expected: Submit button enabled
    expect(submit.disabled).toBe(false);
});
```

**Case 2D: Submit disabled when form is empty**
```javascript
test('should disable submit when form is empty', () => {
    document.dispatchEvent(new Event('DOMContentLoaded'));
    const submit = document.getElementById('submit-form');
    const teamNameInput = document.getElementById('team_name');
    const chefCheckbox = document.getElementById('participation');

    // All fields empty
    teamNameInput.value = '';
    chefCheckbox.checked = false;

    window.updateSubmitState();
    
    // Expected: Submit button disabled
    expect(submit.disabled).toBe(true);
});
```

**Case 2E: Submit enabled with inscrit_id (searched result)**
```javascript
test('should check for inscrit-id when name/firstname empty', () => {
    document.dispatchEvent(new Event('DOMContentLoaded'));
    const submit = document.getElementById('submit-form');
    const list = document.getElementById('people-list');

    // Add runner with inscrit_id (from search results)
    const div = document.createElement('div');
    div.className = 'person';
    div.innerHTML = `
        <input type="text" class="inscrit-firstname" value="" />
        <input type="text" class="inscrit-name" value="" />
        <input type="hidden" class="inscrit-id" value="12345" />
    `;
    list.appendChild(div);

    window.updateSubmitState();
    
    // Expected: Submit button enabled (user searched and found existing runner)
    expect(submit.disabled).toBe(false);
});
```

## Scenario 3: Autocomplete Search

### Business Rule
When user types in the search field:
- Fetch only triggers with 2+ characters
- Results limited to 5 entries
- 250ms debounce prevents excessive requests
- Clicking suggestion populates firstname, name, and inscrit_id

### Test Cases

**Case 3A: Search triggers with 2+ characters**
```javascript
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

    // Mock API
    global.fetch.mockResolvedValueOnce({
        json: async () => [
            { INS_ID: 1, INS_PRENOM: 'John', INS_NOM: 'Doe', is_adherent: true }
        ]
    });

    // Type "Jo" (2 characters)
    const search = container.querySelector('.inscrit-search');
    search.value = 'Jo';
    search.dispatchEvent(new Event('input'));

    // Wait for debounce (250ms)
    await new Promise(resolve => setTimeout(resolve, 300));

    // Expected: API was called
    expect(global.fetch).toHaveBeenCalled();
});
```

**Case 3B: Search doesn't trigger with less than 2 characters**
```javascript
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

    // Type "J" (1 character)
    const search = container.querySelector('.inscrit-search');
    search.value = 'J';
    search.dispatchEvent(new Event('input'));

    // Wait for debounce (250ms)
    await new Promise(resolve => setTimeout(resolve, 300));

    // Expected: API was NOT called
    expect(global.fetch).not.toHaveBeenCalled();
});
```

**Case 3C: Suggestion click populates fields**
```javascript
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

    // Mock API returning one result
    global.fetch.mockResolvedValueOnce({
        json: async () => [
            { INS_ID: 123, INS_PRENOM: 'John', INS_NOM: 'Doe', is_adherent: true }
        ]
    });

    // Type and search
    const search = container.querySelector('.inscrit-search');
    search.value = 'John';
    search.dispatchEvent(new Event('input'));

    // Wait for debounce
    await new Promise(resolve => setTimeout(resolve, 300));

    // Click the suggestion
    const suggestionRow = container.querySelector('.inscrit-suggestions div');
    suggestionRow.click();

    // Expected: Fields populated from search result
    expect(container.querySelector('.inscrit-firstname').value).toBe('John');
    expect(container.querySelector('.inscrit-name').value).toBe('Doe');
    expect(container.querySelector('.inscrit-id').value).toBe('123');
});
```

## Scenario 4: Dynamic Runner Management

### Business Rule
Users can add and remove runners dynamically. When removed, remaining runners are renumbered and input names are reindexed.

### Test Cases

**Case 4A: Add new runner**
```javascript
test('should add new runner when add button is clicked', () => {
    document.dispatchEvent(new Event('DOMContentLoaded'));
    const addBtn = document.getElementById('add-person');
    const list = document.getElementById('people-list');

    // Initially no runners
    expect(list.querySelectorAll('.person').length).toBe(0);

    // Click add button
    addBtn.click();

    // Expected: One runner added
    expect(list.querySelectorAll('.person').length).toBe(1);
    expect(list.querySelector('h3').textContent).toBe('Coureur 1');
});
```

**Case 4B: Remove runner**
```javascript
test('should remove runner when remove button is clicked', () => {
    document.dispatchEvent(new Event('DOMContentLoaded'));
    const addBtn = document.getElementById('add-person');
    const list = document.getElementById('people-list');

    // Add 2 runners
    addBtn.click();
    addBtn.click();
    expect(list.querySelectorAll('.person').length).toBe(2);

    // Click first remove button
    const removeBtn = list.querySelector('.remove');
    removeBtn.click();

    // Expected: One runner removed
    expect(list.querySelectorAll('.person').length).toBe(1);
});
```

**Case 4C: Renumber after removal**
```javascript
test('should renumber runners after removal', () => {
    document.dispatchEvent(new Event('DOMContentLoaded'));
    const addBtn = document.getElementById('add-person');
    const list = document.getElementById('people-list');

    // Add 3 runners
    addBtn.click();
    addBtn.click();
    addBtn.click();

    // Remove first runner
    const removeBtn = list.querySelector('.remove');
    removeBtn.click();

    // Get remaining titles
    const titles = document.querySelectorAll('.person h3');
    
    // Expected: Renumbered from 1, 2 (instead of 2, 3)
    expect(titles[0].textContent).toBe('Coureur 1');
    expect(titles[1].textContent).toBe('Coureur 2');
});
```

**Case 4D: Reindex input names**
```javascript
test('should reindex input names after runner removal', () => {
    document.dispatchEvent(new Event('DOMContentLoaded'));
    const addBtn = document.getElementById('add-person');
    const list = document.getElementById('people-list');

    // Add 3 runners
    addBtn.click();
    addBtn.click();
    addBtn.click();

    // Remove first runner
    const removeBtn = list.querySelector('.remove');
    removeBtn.click();

    // Get input names of remaining runners
    const firstNameInputs = document.querySelectorAll('.inscrit-firstname');
    
    // Expected: Input names reindexed to [0], [1] (not [1], [2])
    expect(firstNameInputs[0].name).toMatch(/people\[0\]/);
    expect(firstNameInputs[1].name).toMatch(/people\[1\]/);
});
```

## Scenario 5: Error Handling

### Business Rule
Temporary error messages display inline and auto-remove after 3.5 seconds.

### Test Cases

**Case 5A: Error displays and removes**
```javascript
test('should remove error after timeout', (done) => {
    document.dispatchEvent(new Event('DOMContentLoaded'));

    // Show error
    window.showTemporaryError('This will disappear');
    const errorBox = document.querySelector('.error-box');
    expect(errorBox).not.toBeNull();

    // Wait for timeout (3.5 seconds)
    setTimeout(() => {
        // Expected: Error removed
        expect(document.querySelector('.error-box')).toBeNull();
        done();
    }, 3600); // Wait slightly longer than timeout
});
```

## Running These Examples

To run any of these tests:

```bash
# Run all tests
npm test

# Run specific test by name
npm test -- -t "should enable add button when team has space"

# Run tests matching pattern
npm test -- -t "Team Size"

# Run in watch mode
npm run test:watch
```

## Validation Checklist

Use these scenarios to manually validate the form:

- [ ] Add/remove runners and verify renumbering
- [ ] Add runners up to max and verify add button disables
- [ ] Check chef participation and verify team limit
- [ ] Try to submit empty form and verify error
- [ ] Type in search field and verify suggestions appear after 2 chars
- [ ] Click suggestion and verify fields populate
- [ ] Try submit with various combinations of team name/chef/runners
- [ ] Verify temporary error messages appear and disappear

