# JavaScript Test Suite for inscription.js

This document explains the test suite for the `inscription.js` module, which handles the dynamic form management for the team registration form.

## Overview

The test file `resources/js/__tests__/inscription.test.js` contains comprehensive tests for all key functions and user interactions in the inscription form module.

## Test Categories

### 1. **updateRunnerNumbers()** Tests
Tests the function that renumbers visible runner titles when runners are added or removed.

- **test_should_update_runner_titles_when_persons_are_added**: Verifies titles update from "Coureur 1", "Coureur 2", etc.
- **test_should_handle_empty_runner_list**: Ensures no errors when no runners exist

### 2. **updateAddButtonState()** Tests
Tests the logic that enables/disables the "Add Runner" button based on team size limits.

- **test_should_disable_add_button_when_team_is_full**: Team size reached → button disabled
- **test_should_enable_add_button_when_team_has_space**: Under limit → button enabled
- **test_should_reserve_space_for_chef_if_participating**: Chef counts toward team limit
- **test_should_handle_missing_team_max_attribute**: Graceful handling of missing data attribute

### 3. **updateSubmitState()** Tests
Tests the logic that enables/disables the submit button based on form validity.

- **test_should_enable_submit_when_team_name_is_provided**: Team name alone allows submission
- **test_should_enable_submit_when_chef_participates**: Chef participation allows submission
- **test_should_enable_submit_when_runner_has_firstname_and_name**: Complete runner info allows submission
- **test_should_disable_submit_when_form_is_empty**: Empty form → disabled
- **test_should_check_for_inscrit_id_when_name_firstname_empty**: Hidden inscrit_id can enable submit

### 4. **showTemporaryError()** Tests
Tests the inline error display mechanism.

- **test_should_create_and_display_error_message**: Error div created with text
- **test_should_remove_error_after_timeout**: Error removed after 3.5 seconds
- **test_should_insert_error_after_participation_label_if_present**: Proper DOM placement

### 5. **checkChefStatus()** Tests
Tests the async chef participation validation and API integration.

- **test_should_show_error_if_chef_participation_exceeds_team_max**: Exceeding limit → error + uncheck
- **test_should_fetch_chef_status_from_api_when_checked**: API call to validate chef
- **test_should_show_pps_row_if_chef_not_checked**: PPS row visibility logic
- **test_should_handle_fetch_error_gracefully**: Network errors don't crash

### 6. **attachAutocompleteTo()** Tests
Tests the autocomplete/search functionality for finding existing registered runners.

- **test_should_fetch_suggestions_when_search_input_has_2_plus_characters**: Minimum 2 chars to trigger search
- **test_should_not_fetch_suggestions_with_less_than_2_characters**: <2 chars → no fetch
- **test_should_populate_fields_when_suggestion_is_clicked**: Click suggestion → fill firstname/name/id
- **test_should_clear_search_results_when_clicking_outside_suggestions**: Click outside → hide suggestions

### 7. **Form Initialization** Tests
Tests the DOMContentLoaded setup.

- **test_should_initialize_form_on_domcontentloaded**: Init doesn't throw
- **test_should_attach_chef_checkbox_listener**: Chef checkbox has change listener
- **test_should_have_submit_button_listener**: Submit button has click listener

### 8. **Dynamic Runner Management** Tests
Tests adding and removing runners.

- **test_should_add_new_runner_when_add_button_is_clicked**: Click add → new runner appears
- **test_should_remove_runner_when_remove_button_is_clicked**: Click remove → runner removed
- **test_should_renumber_runners_after_removal**: Titles renumber after removal
- **test_should_reindex_input_names_after_runner_removal**: Input names reindex (people[0], people[1], etc.)

### 9. **Form Submission Validation** Tests
Tests submit button error handling.

- **test_should_show_error_when_submitting_empty_form**: Invalid form shows error
- **test_should_not_submit_when_disabled**: Disabled submit button prevents submission

### 10. **Input Listeners** Tests
Tests real-time form validation and searching.

- **test_should_update_submit_state_when_firstname_name_changes**: Live validation
- **test_should_search_for_runner_on_focusout_from_name_field**: Auto-search after leaving name field

## Running the Tests

### Setup
First, install dependencies:
```bash
npm install
```

### Run All Tests
```bash
npm test
```

### Run Tests in Watch Mode
```bash
npm run test:watch
```

### Run Specific Test File
```bash
npm test inscription.test.js
```

### Run Tests with Coverage
```bash
npm test -- --coverage
```

## Test Structure

Each test follows the Arrange-Act-Assert (AAA) pattern:

```javascript
test('should do something specific', () => {
    // Arrange: Set up initial state
    document.dispatchEvent(new Event('DOMContentLoaded'));
    const button = document.getElementById('add-person');
    
    // Act: Perform the action
    button.click();
    
    // Assert: Check the result
    expect(document.querySelectorAll('.person').length).toBe(1);
});
```

## Mocking

The test suite includes mocks for:

- **DOM**: Full DOM API via jsdom
- **fetch()**: Mocked to return controlled responses
- **console**: Suppressed to reduce noise (can be re-enabled)

Example fetch mock:
```javascript
global.fetch.mockResolvedValueOnce({
    json: async () => [
        { INS_ID: 1, INS_PRENOM: 'John', INS_NOM: 'Doe' }
    ]
});
```

## Key Test Scenarios

### Team Size Management
- Chef participation counts toward team limit
- Add button disabled when team is full
- Error shown if chef added when team would exceed limit

### Form Validation
- Submit button enabled only with:
  - Team name, OR
  - Chef participation, OR
  - Valid runner (firstname + name OR inscrit_id)

### Autocomplete Search
- Minimum 2 characters to trigger search
- 250ms debounce prevents excessive requests
- Suggestions limited to 5 results
- Clicking suggestion fills firstname/name/inscrit_id
- Clicking outside hides suggestions

### Error Handling
- Network errors handled gracefully
- Temporary error messages auto-remove after 3.5s
- Errors positioned near relevant form elements

## Common Issues & Solutions

### Test Failing: "Cannot read property 'querySelectorAll' of null"
- Ensure `document.dispatchEvent(new Event('DOMContentLoaded'))` is called
- The module only initializes on DOMContentLoaded

### Test Failing: "fetch is not a function"
- Jest's jsdom doesn't provide fetch; must be mocked
- Add: `global.fetch.mockResolvedValueOnce({...})`

### Async Tests Timing Out
- Increase timeout: `jest.setTimeout(15000)` in test
- Ensure `await` before assertions on async operations

## Coverage Goals

The test suite aims for:
- **Branches**: 50%+ coverage
- **Functions**: 50%+ coverage  
- **Lines**: 50%+ coverage
- **Statements**: 50%+ coverage

Current coverage can be checked with:
```bash
npm test -- --coverage
```

## Future Test Enhancements

Potential additional test areas:
1. **Integration tests**: Full form submission flow
2. **Accessibility tests**: ARIA labels, keyboard navigation
3. **Performance tests**: Debounce timing, render performance with many runners
4. **Edge cases**: Unicode in names, very long inputs, special characters
5. **Mobile tests**: Touch events, keyboard visibility

## Related Files

- **Source**: `resources/js/inscription.js`
- **Form Template**: `resources/views/pages/inscForm.blade.php`
- **API Endpoint**: `/inscrits/search` (InscritController@search)
- **Jest Config**: `jest.config.js`
- **Jest Setup**: `jest.setup.js`

## Debugging Tests

To debug a specific test:

1. Add `only` to focus on one test:
```javascript
test.only('should do something', () => {
    // Only this test will run
});
```

2. Add `skip` to skip a test:
```javascript
test.skip('broken test', () => {
    // This test won't run
});
```

3. Add `debugger` statement and run:
```bash
node --inspect-brk node_modules/.bin/jest
```

4. Print values during test:
```javascript
console.log('My value:', myVar); // Will show in test output
```

## Contributing

When adding new tests:
1. Follow existing naming conventions: `test_should_<specific_behavior>`
2. Keep tests focused and independent
3. Mock external dependencies (fetch, localStorage, etc.)
4. Update this README with new test categories
5. Aim for >80% coverage of critical paths

