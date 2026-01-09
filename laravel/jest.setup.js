/**
 * Jest setup file - runs before all tests
 * Configures global mocks and test environment
 */

// Mock window.location
delete window.location;
window.location = {
    href: '',
    pathname: '/',
    search: '',
    hash: ''
};

// Suppress console output during tests (optional)
global.console = {
    ...console,
    error: jest.fn(),
    warn: jest.fn(),
    // Keep log, info for debugging if needed
    log: jest.fn(),
    debug: jest.fn()
};
