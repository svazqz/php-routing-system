#!/bin/bash

# E2E Test Runner for PHP Routing System
# This script sets up Docker containers and runs comprehensive E2E tests

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
COMPOSE_FILE="infra/test/docker-compose.test.yml"
TEST_CONTAINER="php-routing-test"
WEB_CONTAINER="php-routing-web-test"
DB_CONTAINER="mysql-test"
MAX_WAIT_TIME=120  # Maximum time to wait for services (seconds)

echo -e "${BLUE}=== PHP Routing System E2E Test Suite ===${NC}"
echo -e "${BLUE}Starting comprehensive end-to-end testing with real database...${NC}"
echo ""

# Function to check if a service is ready
check_service_health() {
    local service_name=$1
    local max_attempts=$2
    local attempt=1
    
    echo -e "${YELLOW}Waiting for $service_name to be ready...${NC}"
    
    while [ $attempt -le $max_attempts ]; do
        if docker-compose -f $COMPOSE_FILE exec -T $service_name echo "Service is ready" > /dev/null 2>&1; then
            echo -e "${GREEN}✓ $service_name is ready${NC}"
            return 0
        fi
        
        echo -e "${YELLOW}Attempt $attempt/$max_attempts: $service_name not ready yet...${NC}"
        sleep 2
        attempt=$((attempt + 1))
    done
    
    echo -e "${RED}✗ $service_name failed to start within expected time${NC}"
    return 1
}

# Function to check database connectivity
check_database_connection() {
    echo -e "${YELLOW}Checking database connection...${NC}"
    
    local max_attempts=30
    local attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        if docker-compose -f $COMPOSE_FILE exec -T $DB_CONTAINER mysql -u test_user -ptest_password -e "SELECT 1" test_routing_db > /dev/null 2>&1; then
            echo -e "${GREEN}✓ Database connection established${NC}"
            return 0
        fi
        
        echo -e "${YELLOW}Attempt $attempt/$max_attempts: Database not ready yet...${NC}"
        sleep 2
        attempt=$((attempt + 1))
    done
    
    echo -e "${RED}✗ Database connection failed${NC}"
    return 1
}

# Function to check web server
check_web_server() {
    echo -e "${YELLOW}Checking web server...${NC}"
    
    local max_attempts=20
    local attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        if curl -s -o /dev/null -w "%{http_code}" http://localhost:8080 | grep -q "200\|404\|500"; then
            echo -e "${GREEN}✓ Web server is responding${NC}"
            return 0
        fi
        
        echo -e "${YELLOW}Attempt $attempt/$max_attempts: Web server not ready yet...${NC}"
        sleep 3
        attempt=$((attempt + 1))
    done
    
    echo -e "${RED}✗ Web server failed to respond${NC}"
    return 1
}

# Function to cleanup
cleanup() {
    echo -e "${YELLOW}Cleaning up containers...${NC}"
    docker-compose -f $COMPOSE_FILE down -v --remove-orphans
    echo -e "${GREEN}✓ Cleanup completed${NC}"
}

# Trap to ensure cleanup on exit
trap cleanup EXIT

# Step 1: Clean up any existing containers
echo -e "${YELLOW}Step 1: Cleaning up existing containers...${NC}"
docker-compose -f $COMPOSE_FILE down -v --remove-orphans > /dev/null 2>&1 || true

# Step 2: Build and start services
echo -e "${YELLOW}Step 2: Building and starting services...${NC}"
docker-compose -f $COMPOSE_FILE up -d --build

if [ $? -ne 0 ]; then
    echo -e "${RED}✗ Failed to start services${NC}"
    exit 1
fi

# Step 3: Wait for services to be ready
echo -e "${YELLOW}Step 3: Waiting for services to be ready...${NC}"

# Check if containers are running
if ! check_service_health $DB_CONTAINER 30; then
    echo -e "${RED}✗ Database container failed to start${NC}"
    docker-compose -f $COMPOSE_FILE logs $DB_CONTAINER
    exit 1
fi

if ! check_service_health $TEST_CONTAINER 20; then
    echo -e "${RED}✗ Test container failed to start${NC}"
    docker-compose -f $COMPOSE_FILE logs $TEST_CONTAINER
    exit 1
fi

if ! check_service_health $WEB_CONTAINER 20; then
    echo -e "${RED}✗ Web container failed to start${NC}"
    docker-compose -f $COMPOSE_FILE logs $WEB_CONTAINER
    exit 1
fi

# Step 4: Check database connection
echo -e "${YELLOW}Step 4: Verifying database connection...${NC}"
if ! check_database_connection; then
    echo -e "${RED}✗ Database connection check failed${NC}"
    docker-compose -f $COMPOSE_FILE logs $DB_CONTAINER
    exit 1
fi

# Step 5: Check web server
echo -e "${YELLOW}Step 5: Verifying web server...${NC}"
if ! check_web_server; then
    echo -e "${RED}✗ Web server check failed${NC}"
    docker-compose -f $COMPOSE_FILE logs $WEB_CONTAINER
    exit 1
fi

# Step 6: Install/update dependencies in test container
echo -e "${YELLOW}Step 6: Installing dependencies...${NC}"
docker-compose -f $COMPOSE_FILE exec -T $TEST_CONTAINER composer install --optimize-autoloader

if [ $? -ne 0 ]; then
    echo -e "${RED}✗ Failed to install dependencies${NC}"
    exit 1
fi

# Step 7: Run database migrations/setup
echo -e "${YELLOW}Step 7: Setting up database schema...${NC}"
docker-compose -f $COMPOSE_FILE exec -T $TEST_CONTAINER php migrate.php

if [ $? -ne 0 ]; then
    echo -e "${YELLOW}⚠ Migration script not found or failed, continuing with existing schema...${NC}"
fi

# Step 8: Run E2E tests
echo -e "${YELLOW}Step 8: Running E2E tests...${NC}"
echo ""

# Run Eloquent database tests
echo -e "${BLUE}=== Running Eloquent Database E2E Tests ===${NC}"
docker-compose -f $COMPOSE_FILE exec -T $TEST_CONTAINER ./vendor/bin/phpunit tests/E2E/EloquentDatabaseTest.php --verbose
DB_TEST_RESULT=$?

echo ""

# Run web endpoints tests
echo -e "${BLUE}=== Running Web Endpoints E2E Tests ===${NC}"
docker-compose -f $COMPOSE_FILE exec -T $TEST_CONTAINER ./vendor/bin/phpunit tests/E2E/WebEndpointsTest.php --verbose
WEB_TEST_RESULT=$?

echo ""

# Run all E2E tests together
echo -e "${BLUE}=== Running All E2E Tests ===${NC}"
docker-compose -f $COMPOSE_FILE exec -T $TEST_CONTAINER ./vendor/bin/phpunit tests/E2E/ --verbose
ALL_E2E_RESULT=$?

echo ""

# Step 9: Run regular test suite in E2E environment
echo -e "${BLUE}=== Running Regular Test Suite in E2E Environment ===${NC}"
docker-compose -f $COMPOSE_FILE exec -T $TEST_CONTAINER ./run-tests.sh
REGULAR_TESTS_RESULT=$?

echo ""

# Step 10: Performance and load testing
echo -e "${BLUE}=== Running Performance Tests ===${NC}"
echo -e "${YELLOW}Testing API performance with multiple concurrent requests...${NC}"

# Simple load test using curl
for i in {1..10}; do
    curl -s -o /dev/null -w "Request $i: %{time_total}s\n" http://localhost:8080/ &
done
wait

echo ""

# Step 11: Generate test report
echo -e "${BLUE}=== Test Results Summary ===${NC}"
echo ""

if [ $DB_TEST_RESULT -eq 0 ]; then
    echo -e "${GREEN}✓ Eloquent Database E2E Tests: PASSED${NC}"
else
    echo -e "${RED}✗ Eloquent Database E2E Tests: FAILED${NC}"
fi

if [ $WEB_TEST_RESULT -eq 0 ]; then
    echo -e "${GREEN}✓ Web Endpoints E2E Tests: PASSED${NC}"
else
    echo -e "${RED}✗ Web Endpoints E2E Tests: FAILED${NC}"
fi

if [ $ALL_E2E_RESULT -eq 0 ]; then
    echo -e "${GREEN}✓ All E2E Tests: PASSED${NC}"
else
    echo -e "${RED}✗ All E2E Tests: FAILED${NC}"
fi

if [ $REGULAR_TESTS_RESULT -eq 0 ]; then
    echo -e "${GREEN}✓ Regular Test Suite in E2E Environment: PASSED${NC}"
else
    echo -e "${RED}✗ Regular Test Suite in E2E Environment: FAILED${NC}"
fi

echo ""

# Step 12: Show container logs if there were failures
if [ $DB_TEST_RESULT -ne 0 ] || [ $WEB_TEST_RESULT -ne 0 ] || [ $ALL_E2E_RESULT -ne 0 ] || [ $REGULAR_TESTS_RESULT -ne 0 ]; then
    echo -e "${YELLOW}=== Container Logs (Last 50 lines) ===${NC}"
    echo -e "${YELLOW}Database logs:${NC}"
    docker-compose -f $COMPOSE_FILE logs --tail=50 $DB_CONTAINER
    echo ""
    echo -e "${YELLOW}Web server logs:${NC}"
    docker-compose -f $COMPOSE_FILE logs --tail=50 $WEB_CONTAINER
    echo ""
    echo -e "${YELLOW}Test container logs:${NC}"
    docker-compose -f $COMPOSE_FILE logs --tail=50 $TEST_CONTAINER
fi

echo ""
echo -e "${BLUE}=== E2E Test Suite Completed ===${NC}"

# Determine overall exit code
if [ $DB_TEST_RESULT -eq 0 ] && [ $WEB_TEST_RESULT -eq 0 ] && [ $ALL_E2E_RESULT -eq 0 ] && [ $REGULAR_TESTS_RESULT -eq 0 ]; then
    echo -e "${GREEN}🎉 All tests passed successfully!${NC}"
    exit 0
else
    echo -e "${RED}❌ Some tests failed. Please check the logs above.${NC}"
    exit 1
fi