#!/bin/bash
# Quick Start Commands for Tests
# Usage: Copy these commands to run tests quickly

# Run all tests
./vendor/bin/phpunit

# Run tests with coverage (slower)
./vendor/bin/phpunit --coverage-html coverage

# Run feature tests only
./vendor/bin/phpunit tests/Feature

# Run unit tests only
./vendor/bin/phpunit tests/Unit

# Run specific test file
./vendor/bin/phpunit tests/Feature/AuthenticationTest.php

# Run specific test method
./vendor/bin/phpunit tests/Feature/AuthenticationTest.php::test_user_can_view_login_page

# Generate test report
php tests/test-report.php

# Watch for changes and re-run tests (requires phpunit-watcher)
./vendor/bin/phpunit-watcher watch

# Run tests in verbose mode
./vendor/bin/phpunit -v

# Run tests with teamcity output (for CI/CD)
./vendor/bin/phpunit --printer=TeamCity

# Stop on first failure
./vendor/bin/phpunit --stop-on-failure

# Run with code coverage and stop on failure
./vendor/bin/phpunit --coverage-html coverage --stop-on-failure
