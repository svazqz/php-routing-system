#!/bin/bash

# PHP Routing System Test Runner
# This script runs the complete test suite

echo "=== PHP Routing System Test Suite ==="
echo ""

# Check if composer dependencies are installed
if [ ! -d "vendor" ]; then
    echo "Installing dependencies..."
    php composer.phar install
    echo ""
fi

# Run all tests
echo "Running all tests..."
./vendor/bin/phpunit --no-coverage
echo ""

# Run specific test suites
echo "=== Unit Tests ==="
./vendor/bin/phpunit --testsuite Unit --no-coverage
echo ""

echo "=== Integration Tests ==="
./vendor/bin/phpunit --testsuite Integration --no-coverage
echo ""

echo "=== Feature Tests ==="
./vendor/bin/phpunit --testsuite Feature --no-coverage
echo ""

echo "=== Test Coverage Report ==="
echo "To generate coverage report, run:"
echo "./vendor/bin/phpunit --coverage-html coverage-html"
echo ""

echo "=== Individual Test Examples ==="
echo "Run specific test file:"
echo "./vendor/bin/phpunit tests/Unit/Core/Classes/ContainerTest.php"
echo ""
echo "Run specific test method:"
echo "./vendor/bin/phpunit --filter testCanSetAndResolveBinding"
echo ""

echo "Testing framework setup complete!"