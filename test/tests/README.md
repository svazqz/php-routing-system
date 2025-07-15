# Testing Framework for PHP Routing System

This directory contains comprehensive tests for the PHP Routing System using PHPUnit as the primary testing framework.

## Setup

### 1. Install Dependencies

```bash
composer install
```

### 2. Run Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit --testsuite Unit
./vendor/bin/phpunit --testsuite Integration
./vendor/bin/phpunit --testsuite Feature

# Run tests with coverage
./vendor/bin/phpunit --coverage-html coverage-html

# Run specific test file
./vendor/bin/phpunit tests/Unit/Core/Classes/ContainerTest.php
```

## Test Structure

### Unit Tests (`tests/Unit/`)
Test individual components in isolation with mocked dependencies:

- **Core Classes**: Container, Controller, APIController, TemplateEngine
- **Core Drivers**: Config, DB, Auth, Session, View, Input
- **App Components**: Controllers, Services, Models, Views
- **Utilities**: Helper functions and utilities

### Integration Tests (`tests/Integration/`)
Test component interactions and system integration:

- **Routing**: URL parsing and controller resolution
- **Database**: Database operations with test database
- **Services**: External API integration
- **Controllers**: Full request/response cycle

### Feature Tests (`tests/Feature/`)
End-to-end testing of complete user workflows:

- **Home Page**: Complete page rendering flow
- **API Endpoints**: Full API request/response testing

## Test Configuration

### PHPUnit Configuration (`phpunit.xml`)
- Defines test suites and coverage settings
- Configures bootstrap file and source directories
- Sets up coverage reporting

### Test Bootstrap (`tests/bootstrap.php`)
- Initializes test environment
- Sets up autoloading and error handling
- Creates test configuration
- Provides test helper functions

### Base Test Case (`tests/TestCase.php`)
- Provides common setup and teardown
- Includes utility methods for testing
- Handles mock creation and cleanup
- Manages test environment state

## Writing Tests

### Unit Test Example

```php
class ExampleTest extends TestCase
{
    public function testSomething(): void
    {
        // Arrange
        $service = new SomeService();
        
        // Act
        $result = $service->doSomething();
        
        // Assert
        $this->assertEquals('expected', $result);
    }
}
```

### Integration Test Example

```php
class ExampleIntegrationTest extends TestCase
{
    public function testControllerIntegration(): void
    {
        // Arrange
        $container = new Core\Container();
        $_SERVER['REQUEST_URI'] = '/Home';
        
        // Act
        $runnableData = parseURIAndComponents();
        $controller = $container->build($runnableData->namespace . $runnableData->controller);
        
        // Assert
        $this->assertInstanceOf('Controllers\\Home', $controller);
    }
}
```

### Mocking External Dependencies

```php
// Mock HTTP client for service tests
$mockHandler = new MockHandler();
$handlerStack = HandlerStack::create($mockHandler);
$mockClient = new Client(['handler' => $handlerStack]);

// Mock database for unit tests
$mockPdo = $this->createMock(PDO::class);
```

## Test Data and Fixtures

### Test Configuration (`config.test.ini`)
- SQLite in-memory database for fast testing
- Debug mode enabled
- Test-specific settings

### Mock Data
- Use Faker library for generating test data
- Create fixtures for consistent test data
- Mock external API responses

## Best Practices

### 1. Test Naming
- Use descriptive test method names
- Follow pattern: `testMethodName_StateUnderTest_ExpectedBehavior`
- Example: `testGetPosts_WhenServiceUnavailable_ReturnsErrorArray`

### 2. Test Structure
- Follow Arrange-Act-Assert pattern
- Keep tests focused on single behavior
- Use meaningful assertions

### 3. Mocking
- Mock external dependencies in unit tests
- Use real objects in integration tests when appropriate
- Verify mock interactions when testing behavior

### 4. Test Data
- Use factories or builders for complex test data
- Keep test data minimal and focused
- Clean up test data after each test

### 5. Performance
- Keep unit tests fast (< 100ms each)
- Use in-memory database for database tests
- Mock expensive operations

## Coverage Goals

- **Unit Tests**: 90%+ code coverage
- **Integration Tests**: Cover critical user paths
- **Feature Tests**: Cover main application workflows

## Continuous Integration

Add to your CI pipeline:

```yaml
# Example GitHub Actions workflow
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
      - run: composer install
      - run: ./vendor/bin/phpunit --coverage-clover coverage.xml
```

## Troubleshooting

### Common Issues

1. **Class not found errors**: Ensure autoloading is properly configured
2. **Database connection errors**: Check test database configuration
3. **Template rendering errors**: Mock view components in unit tests
4. **HTTP client errors**: Use mock handlers for external API calls

### Debug Tests

```bash
# Run with verbose output
./vendor/bin/phpunit --verbose

# Run with debug information
./vendor/bin/phpunit --debug

# Stop on first failure
./vendor/bin/phpunit --stop-on-failure
```