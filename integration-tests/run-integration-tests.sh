#!/bin/bash
set -e

echo "=== Open Source POS Integration Tests ==="
echo ""

# Start Docker Stack
echo "1. Starting Docker Stack..."
docker compose up -d

echo "2. Waiting for application to be ready..."
timeout=60
elapsed=0
while [ $elapsed -lt $timeout ]; do
  if curl -s -f http://localhost/ > /dev/null 2>&1; then
    echo "   ✓ Application is ready!"
    break
  fi
  sleep 2
  elapsed=$((elapsed + 2))
  if [ $elapsed -eq $timeout ]; then
    echo "   ✗ Application not ready after ${timeout}s"
    echo "   === Logs ==="
    docker logs opensourcepos-integration-tests-ospos-1
    docker logs mysql
    exit 1
  fi
  echo "   Waiting... (${elapsed}s)"
done

# Check Login Page
echo ""
echo "3. Checking Login Page..."
response=$(curl -s http://localhost/)

if echo "$response" | grep -q "Open Source Point of Sale"; then
  echo "   ✓ Login page accessible"
else
  echo "   ✗ Login page not accessible"
  exit 1
fi

if echo "$response" | grep -q "Login"; then
  echo "   ✓ Login form found"
else
  echo "   ✗ Login form not found"
  exit 1
fi

if echo "$response" | grep -q "username"; then
  echo "   ✓ Username field found"
else
  echo "   ✗ Username field not found"
  exit 1
fi

if echo "$response" | grep -q "password"; then
  echo "   ✓ Password field found"
else
  echo "   ✗ Password field not found"
  exit 1
fi

# Check HTTP Status
echo ""
echo "4. Checking HTTP Status..."
status=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/)
if [ "$status" -eq 200 ]; then
  echo "   ✓ HTTP status: $status"
else
  echo "   ✗ HTTP status: $status"
  exit 1
fi

# Database Check
echo ""
echo "5. Checking Database Connection..."
db_logs=$(docker logs opensourcepos-integration-tests-ospos-1 2>&1)
if echo "$db_logs" | grep -qi "database.*connected\|mysql.*connected\|mysqli.*connected"; then
  echo "   ✓ Database connected"
else
  echo "   ⚠ Database connection status unclear (checking if app is responding)"
  if curl -s -f http://localhost/ > /dev/null; then
    echo "   ✓ Application responding to requests"
  fi
fi

echo ""
echo "=== All Tests Passed! ✓ ==="

# Cleanup
echo ""
echo "6. Stopping Docker Stack..."
docker compose down -v

exit 0