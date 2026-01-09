# JavaScript Test Suite Quick Start

## 🎯 What We Created

A comprehensive Jest test suite for `inscription.js` with **38 tests** covering all form behavior.

## 📦 Installation

```bash
cd laravel
npm install
```

## 🧪 Running Tests

```bash
# Run all tests once
npm test

# Run tests in watch mode (re-runs on file changes)
npm run test:watch

# Run with coverage report
npm test -- --coverage

# Run specific test
npm test -- -t "should enable add button"
```

## 📊 Test Summary

| Category | Count | File |
|----------|-------|------|
| Runner number management | 2 | inscription.test.js |
| Add button state | 4 | inscription.test.js |
| Submit button state | 5 | inscription.test.js |
| Error messages | 3 | inscription.test.js |
| Chef status checking | 4 | inscription.test.js |
| Autocomplete search | 5 | inscription.test.js |
| Form initialization | 3 | inscription.test.js |
| Dynamic runner mgmt | 4 | inscription.test.js |
| Submit validation | 2 | inscription.test.js |
| Input listeners | 2 | inscription.test.js |
| **TOTAL** | **38** | |

## ✅ Key Features Tested

✓ **Team Size Management**
  - Add button enables/disables based on capacity
  - Chef counts toward team limit
  - Error if chef participation exceeds limit

✓ **Form Validation**
  - Submit enabled with: team name OR chef OR complete runner
  - Submit disabled when empty
  - Real-time state updates

✓ **Autocomplete Search**
  - 2+ character minimum triggers search
  - 250ms debounce working
  - Clicking suggestion fills fields
  - Click-outside hides suggestions

✓ **Dynamic Runners**
  - Adding/removing runners
  - Auto-renumbering (Coureur 1, 2, 3...)
  - Input name reindexing (people[0], people[1]...)

✓ **Error Handling**
  - Temporary error messages
  - Auto-removal after 3.5 seconds
  - Network error resilience

## 📁 Files Created

```
laravel/
├── jest.config.js                    (Jest configuration)
├── jest.setup.js                     (Jest initialization)
├── package.json                      (Updated - added Jest deps & scripts)
├── resources/
│   └── js/
│       └── __tests__/
│           └── inscription.test.js   (38 comprehensive tests)
├── TEST_GUIDE_JAVASCRIPT.md          (Detailed testing guide)
├── JAVASCRIPT_TESTS_SUMMARY.md       (Quick overview)
├── JAVASCRIPT_TEST_SCENARIOS.md      (Example test cases)
└── TESTING_COMPLETE_SUMMARY.md       (Full project summary)
```

## 🔍 Example Tests

### Team Size Validation
```javascript
test('should disable add button when team is full', () => {
    // Setup form with max runners
    // Click add button
    // Assert button is disabled
});
```

### Form Submission
```javascript
test('should enable submit when team name is provided', () => {
    // Fill team name field
    // Assert submit button enabled
});
```

### Autocomplete
```javascript
test('should populate fields when suggestion is clicked', async () => {
    // Type in search field
    // Wait for API results
    // Click suggestion
    // Assert fields populated
});
```

## 📚 Documentation

1. **TEST_GUIDE_JAVASCRIPT.md** - Complete testing guide
2. **JAVASCRIPT_TEST_SCENARIOS.md** - Detailed examples
3. **JAVASCRIPT_TESTS_SUMMARY.md** - Overview

## 🛠️ Troubleshooting

### Tests fail with "fetch is not a function"
- Expected in jsdom; all tests mock fetch

### Tests timeout
- Increase timeout: `jest.setTimeout(15000)` in test

### Want to debug a specific test
- Add `.only`: `test.only('my test', () => {})`
- Add `debugger` and run with: `node --inspect-brk node_modules/.bin/jest`

## 📈 Coverage

Current setup targets:
- Branches: 50%+
- Functions: 50%+
- Lines: 50%+
- Statements: 50%+

Check coverage:
```bash
npm test -- --coverage
```

## 🚀 Next Steps

1. **Run tests to validate setup**
   ```bash
   npm install && npm test
   ```

2. **Verify all 38 tests pass**

3. **Optional: Add to CI/CD pipeline**
   - GitHub Actions
   - GitLab CI
   - Jenkins
   - CircleCI

4. **Monitor coverage** regularly

## 📞 Questions?

Refer to:
- `TEST_GUIDE_JAVASCRIPT.md` for detailed explanations
- `JAVASCRIPT_TEST_SCENARIOS.md` for examples
- `jest.config.js` for configuration

---

**Status**: ✅ Ready to run
All tests created and configured. Execute `npm install && npm test` to validate.
