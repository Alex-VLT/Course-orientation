export default {
    testEnvironment: 'jsdom',
    testMatch: ['**/__tests__/**/*.test.js', '**/?(*.)+(spec|test).js'],
    moduleFileExtensions: ['js', 'json'],
    collectCoverageFrom: [
        'resources/js/**/*.js',
        '!resources/js/**/*.test.js',
        '!resources/js/__tests__/**'
    ],
    coverageThreshold: {
        global: {
            branches: 50,
            functions: 50,
            lines: 50,
            statements: 50
        }
    },
    setupFilesAfterEnv: ['<rootDir>/jest.setup.js'],
    testTimeout: 10000
};
