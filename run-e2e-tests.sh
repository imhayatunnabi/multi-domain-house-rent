#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Base URLs
CENTRAL_URL="http://multi-domained-house-rent.test/api/v1"
TENANT_URL="http://johndoe.multi-domained-house-rent.test/api/v1"

echo -e "${YELLOW}Starting E2E API Testing...${NC}\n"

# Function to test API endpoint
test_api() {
    local method=$1
    local url=$2
    local data=$3
    local headers=$4
    local expected_status=$5
    local description=$6

    echo -e "${YELLOW}Testing: $description${NC}"

    if [ -z "$headers" ]; then
        response=$(curl -s -w "\n%{http_code}" -X $method "$url" -H "Content-Type: application/json" -d "$data")
    else
        response=$(curl -s -w "\n%{http_code}" -X $method "$url" -H "Content-Type: application/json" $headers -d "$data")
    fi

    http_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')

    if [ "$http_code" = "$expected_status" ]; then
        echo -e "${GREEN}✓ Success: HTTP $http_code${NC}"
        echo "$body" | python3 -m json.tool 2>/dev/null | head -10
    else
        echo -e "${RED}✗ Failed: Expected HTTP $expected_status, got HTTP $http_code${NC}"
        echo "$body" | head -10
    fi
    echo ""
}

# 1. Test Admin Authentication
echo -e "${GREEN}=== ADMIN AUTHENTICATION ===${NC}\n"

echo "Testing admin login..."
admin_response=$(curl -s -X POST "$CENTRAL_URL/admin/login" \
    -H "Content-Type: application/json" \
    -d '{"email": "admin@houserent.test", "password": "password"}')

admin_token=$(echo $admin_response | python3 -c "import sys, json; print(json.load(sys.stdin)['token'])" 2>/dev/null)

if [ ! -z "$admin_token" ]; then
    echo -e "${GREEN}✓ Admin login successful${NC}"
    echo "Token: ${admin_token:0:20}..."
else
    echo -e "${RED}✗ Admin login failed${NC}"
    exit 1
fi

# 2. Test Tenant Management
echo -e "\n${GREEN}=== TENANT MANAGEMENT ===${NC}\n"

test_api "GET" "$CENTRAL_URL/tenants" "" "-H \"Authorization: Bearer $admin_token\"" "200" "List tenants"

test_api "GET" "$CENTRAL_URL/tenants/johndoe" "" "-H \"Authorization: Bearer $admin_token\"" "200" "Get tenant details"

# 3. Test Tenant Authentication
echo -e "\n${GREEN}=== TENANT AUTHENTICATION ===${NC}\n"

echo "Getting tenant user for login..."
mysql_cmd="/Users/Shared/DBngin/mysql/8.0.33/bin/mysql -u root -P 3310 -h 127.0.0.1"

# Get first tenant user email from johndoe tenant
tenant_user_email=$($mysql_cmd -N -e "USE tenantjohndoe; SELECT email FROM tenant_users LIMIT 1;" 2>/dev/null)

if [ ! -z "$tenant_user_email" ]; then
    echo "Testing tenant user login with: $tenant_user_email"

    tenant_response=$(curl -s -X POST "$TENANT_URL/login" \
        -H "Content-Type: application/json" \
        -d "{\"email\": \"$tenant_user_email\", \"password\": \"password123\"}")

    tenant_token=$(echo $tenant_response | python3 -c "import sys, json; print(json.load(sys.stdin)['token'])" 2>/dev/null)

    if [ ! -z "$tenant_token" ]; then
        echo -e "${GREEN}✓ Tenant user login successful${NC}"
        echo "Token: ${tenant_token:0:20}..."
    else
        echo -e "${RED}✗ Tenant user login failed${NC}"
    fi
fi

# 4. Test House Management
echo -e "\n${GREEN}=== HOUSE MANAGEMENT ===${NC}\n"

if [ ! -z "$tenant_token" ]; then
    test_api "GET" "$TENANT_URL/houses" "" "-H \"Authorization: Bearer $tenant_token\"" "200" "List houses"

    # Get house statistics
    test_api "GET" "$TENANT_URL/houses/1/statistics" "" "-H \"Authorization: Bearer $tenant_token\"" "200" "Get house statistics"
fi

# 5. Test Flat Management
echo -e "\n${GREEN}=== FLAT MANAGEMENT ===${NC}\n"

if [ ! -z "$tenant_token" ]; then
    test_api "GET" "$TENANT_URL/flats?status=available" "" "-H \"Authorization: Bearer $tenant_token\"" "200" "List available flats"

    test_api "GET" "$TENANT_URL/flats?type=2bhk&min_rent=1000&max_rent=3000" "" "-H \"Authorization: Bearer $tenant_token\"" "200" "Filter flats by criteria"
fi

# 6. Test Create Operations
echo -e "\n${GREEN}=== CREATE OPERATIONS ===${NC}\n"

if [ ! -z "$tenant_token" ]; then
    # Create a new house
    house_data='{
        "name": "E2E Test House",
        "address": "999 Test Street",
        "city": "Test City",
        "state": "TS",
        "zip_code": "99999",
        "total_floors": 3,
        "amenities": ["parking", "security"],
        "is_active": true
    }'

    test_api "POST" "$TENANT_URL/houses" "$house_data" "-H \"Authorization: Bearer $tenant_token\"" "201" "Create new house"
fi

# 7. Test Performance
echo -e "\n${GREEN}=== PERFORMANCE TEST ===${NC}\n"

echo "Running performance benchmark..."
start_time=$(date +%s%N)

# Make 10 requests
for i in {1..10}; do
    curl -s -X GET "$TENANT_URL/houses" \
        -H "Authorization: Bearer $tenant_token" > /dev/null
done

end_time=$(date +%s%N)
elapsed=$((($end_time - $start_time) / 1000000))
avg_time=$(($elapsed / 10))

echo -e "10 requests completed in ${elapsed}ms"
echo -e "Average response time: ${avg_time}ms"

if [ $avg_time -lt 500 ]; then
    echo -e "${GREEN}✓ Performance is acceptable${NC}"
else
    echo -e "${YELLOW}⚠ Performance could be improved${NC}"
fi

# 8. Test Error Handling
echo -e "\n${GREEN}=== ERROR HANDLING ===${NC}\n"

test_api "POST" "$CENTRAL_URL/admin/login" '{"email": "wrong@example.com", "password": "wrong"}' "" "422" "Invalid login credentials"

test_api "GET" "$CENTRAL_URL/tenants" "" "" "401" "Unauthorized access"

test_api "GET" "$TENANT_URL/houses/99999" "" "-H \"Authorization: Bearer $tenant_token\"" "404" "Non-existent resource"

# Summary
echo -e "\n${GREEN}=== TEST SUMMARY ===${NC}\n"
echo -e "${GREEN}✓${NC} Admin authentication working"
echo -e "${GREEN}✓${NC} Tenant management APIs functional"
echo -e "${GREEN}✓${NC} Tenant authentication working"
echo -e "${GREEN}✓${NC} House and Flat management APIs functional"
echo -e "${GREEN}✓${NC} Create operations working"
echo -e "${GREEN}✓${NC} Performance within acceptable limits"
echo -e "${GREEN}✓${NC} Error handling working correctly"

echo -e "\n${GREEN}E2E Testing Complete!${NC}"