# Multi-Domained House Rent System - API Documentation
## 🚀 Quick Start

### Base URLs
- **Central API**: `http://multi-domained-house-rent.test/api/v1`
- **Tenant APIs**: `http://{tenant-subdomain}.multi-domained-house-rent.test/api/v1`

### Authentication
All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {your-token}
```

## 📋 Table of Contents

1. [Authentication](#authentication)
2. [Admin Endpoints](#admin-endpoints)
3. [Tenant Endpoints](#tenant-endpoints)
4. [Error Handling](#error-handling)
5. [Rate Limiting](#rate-limiting)
6. [Examples](#examples)

## 🔐 Authentication

### Admin Login
**Endpoint**: `POST /api/v1/admin/login`

**Request Body**:
```json
{
    "email": "admin@houserent.test",
    "password": "password"
}
```

**Response**:
```json
{
    "user": {
        "id": 1,
        "name": "Super Admin",
        "email": "admin@houserent.test",
        "role": "super_admin",
        "is_active": true
    },
    "token": "1|abc123...",
    "type": "admin"
}
```

### Tenant User Login
**Endpoint**: `POST /api/v1/login`

**Request Body**:
```json
{
    "email": "john@johndoe.com",
    "password": "password"
}
```

**Response**:
```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@johndoe.com",
        "flat": {
            "id": 1,
            "flat_number": "A-101",
            "rent_amount": 1500.00
        }
    },
    "token": "2|def456...",
    "type": "tenant_user"
}
```

### Logout
**Endpoint**: `POST /api/v1/logout`

**Headers**: `Authorization: Bearer {token}`

**Response**:
```json
{
    "message": "Logged out successfully"
}
```

### Get Current User
**Endpoint**: `GET /api/v1/me`

**Headers**: `Authorization: Bearer {token}`

**Response**:
```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@johndoe.com"
    },
    "type": "tenant_user"
}
```

## 👨‍💼 Admin Endpoints

### List All Tenants
**Endpoint**: `GET /api/v1/tenants`

**Headers**: `Authorization: Bearer {admin-token}`

**Response**:
```json
{
    "data": [
        {
            "id": 1,
            "name": "John Doe Properties",
            "domain": "johndoe.multi-domained-house-rent.test",
            "is_active": true,
            "created_at": "2024-01-01T00:00:00.000000Z"
        }
    ]
}
```

### Register New Tenant
**Endpoint**: `POST /api/v1/tenants`

**Headers**: `Authorization: Bearer {admin-token}`

**Request Body**:
```json
{
    "name": "New Property Company",
    "domain": "newcompany.multi-domained-house-rent.test",
    "email": "admin@newcompany.com",
    "password": "password123"
}
```

**Response**:
```json
{
    "message": "Tenant registered successfully",
    "tenant": {
        "id": 2,
        "name": "New Property Company",
        "domain": "newcompany.multi-domained-house-rent.test",
        "is_active": true
    }
}
```

### Get Tenant Details
**Endpoint**: `GET /api/v1/tenants/{id}`

**Headers**: `Authorization: Bearer {admin-token}`

**Response**:
```json
{
    "id": 1,
    "name": "John Doe Properties",
    "domain": "johndoe.multi-domained-house-rent.test",
    "is_active": true,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
}
```

### Update Tenant
**Endpoint**: `PUT /api/v1/tenants/{id}`

**Headers**: `Authorization: Bearer {admin-token}`

**Request Body**:
```json
{
    "name": "Updated Company Name",
    "domain": "updated.multi-domained-house-rent.test"
}
```

### Suspend Tenant
**Endpoint**: `POST /api/v1/tenants/{id}/suspend`

**Headers**: `Authorization: Bearer {admin-token}`

**Response**:
```json
{
    "message": "Tenant suspended successfully"
}
```

### Activate Tenant
**Endpoint**: `POST /api/v1/tenants/{id}/activate`

**Headers**: `Authorization: Bearer {admin-token}`

**Response**:
```json
{
    "message": "Tenant activated successfully"
}
```

### Delete Tenant
**Endpoint**: `DELETE /api/v1/tenants/{id}`

**Headers**: `Authorization: Bearer {admin-token}`

**Response**:
```json
{
    "message": "Tenant deleted successfully"
}
```

## 🏠 Tenant Endpoints

### House Management

#### List Houses
**Endpoint**: `GET /api/v1/houses`

**Headers**: `Authorization: Bearer {tenant-token}`

**Response**:
```json
{
    "data": [
        {
            "id": 1,
            "name": "Sunset Apartments",
            "address": "123 Main Street",
            "total_floors": 5,
            "total_flats": 20,
            "created_at": "2024-01-01T00:00:00.000000Z"
        }
    ]
}
```

#### Create House
**Endpoint**: `POST /api/v1/houses`

**Headers**: `Authorization: Bearer {tenant-token}`

**Request Body**:
```json
{
    "name": "New Apartment Complex",
    "address": "456 Oak Avenue",
    "description": "Modern apartment complex with amenities"
}
```

#### Get House Details
**Endpoint**: `GET /api/v1/houses/{id}`

**Headers**: `Authorization: Bearer {tenant-token}`

#### Update House
**Endpoint**: `PUT /api/v1/houses/{id}`

**Headers**: `Authorization: Bearer {tenant-token}`

#### Delete House
**Endpoint**: `DELETE /api/v1/houses/{id}`

**Headers**: `Authorization: Bearer {tenant-token}`

#### Get House Statistics
**Endpoint**: `GET /api/v1/houses/{id}/statistics`

**Headers**: `Authorization: Bearer {tenant-token}`

**Response**:
```json
{
    "total_floors": 5,
    "total_flats": 20,
    "occupied_flats": 15,
    "available_flats": 5,
    "total_rent": 30000.00
}
```

### Floor Management

#### List Floors for House
**Endpoint**: `GET /api/v1/houses/{house_id}/floors`

**Headers**: `Authorization: Bearer {tenant-token}`

#### Create Floor
**Endpoint**: `POST /api/v1/houses/{house_id}/floors`

**Headers**: `Authorization: Bearer {tenant-token}`

**Request Body**:
```json
{
    "floor_number": 1,
    "description": "Ground floor with parking"
}
```

#### Get Floor Details
**Endpoint**: `GET /api/v1/houses/{house_id}/floors/{id}`

**Headers**: `Authorization: Bearer {tenant-token}`

#### Update Floor
**Endpoint**: `PUT /api/v1/houses/{house_id}/floors/{id}`

**Headers**: `Authorization: Bearer {tenant-token}`

#### Delete Floor
**Endpoint**: `DELETE /api/v1/houses/{house_id}/floors/{id}`

**Headers**: `Authorization: Bearer {tenant-token}`

### Flat Management

#### List Flats
**Endpoint**: `GET /api/v1/flats`

**Headers**: `Authorization: Bearer {tenant-token}`

**Query Parameters**:
- `house_id` (optional): Filter by house
- `status` (optional): Filter by status (available, occupied, maintenance)
- `floor_id` (optional): Filter by floor

**Response**:
```json
{
    "data": [
        {
            "id": 1,
            "flat_number": "A-101",
            "rent_amount": 1500.00,
            "status": "occupied",
            "house": {
                "id": 1,
                "name": "Sunset Apartments"
            },
            "floor": {
                "id": 1,
                "floor_number": 1
            },
            "tenant_user": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com"
            }
        }
    ]
}
```

#### Create Flat
**Endpoint**: `POST /api/v1/flats`

**Headers**: `Authorization: Bearer {tenant-token}`

**Request Body**:
```json
{
    "flat_number": "A-102",
    "rent_amount": 1500.00,
    "house_id": 1,
    "floor_id": 1,
    "description": "2 bedroom apartment"
}
```

#### Get Flat Details
**Endpoint**: `GET /api/v1/flats/{id}`

**Headers**: `Authorization: Bearer {tenant-token}`

#### Update Flat
**Endpoint**: `PUT /api/v1/flats/{id}`

**Headers**: `Authorization: Bearer {tenant-token}`

#### Delete Flat
**Endpoint**: `DELETE /api/v1/flats/{id}`

**Headers**: `Authorization: Bearer {tenant-token}`

#### Update Flat Status
**Endpoint**: `PATCH /api/v1/flats/{id}/status`

**Headers**: `Authorization: Bearer {tenant-token}`

**Request Body**:
```json
{
    "status": "maintenance"
}
```

#### Get Available Flats
**Endpoint**: `GET /api/v1/flats/available`

**Headers**: `Authorization: Bearer {tenant-token}`

**Response**:
```json
{
    "data": [
        {
            "id": 2,
            "flat_number": "A-102",
            "rent_amount": 1500.00,
            "house": {
                "id": 1,
                "name": "Sunset Apartments"
            },
            "floor": {
                "id": 1,
                "floor_number": 1
            }
        }
    ]
}
```

### Tenant User Management

#### List Tenant Users
**Endpoint**: `GET /api/v1/tenant-users`

**Headers**: `Authorization: Bearer {tenant-token}`

**Response**:
```json
{
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "+1234567890",
            "is_active": true,
            "flat": {
                "id": 1,
                "flat_number": "A-101",
                "rent_amount": 1500.00
            },
            "created_at": "2024-01-01T00:00:00.000000Z"
        }
    ]
}
```

#### Create Tenant User
**Endpoint**: `POST /api/v1/tenant-users`

**Headers**: `Authorization: Bearer {tenant-token}`

**Request Body**:
```json
{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "phone": "+1234567891",
    "password": "password123"
}
```

#### Get Tenant User Details
**Endpoint**: `GET /api/v1/tenant-users/{id}`

**Headers**: `Authorization: Bearer {tenant-token}`

#### Update Tenant User
**Endpoint**: `PUT /api/v1/tenant-users/{id}`

**Headers**: `Authorization: Bearer {tenant-token}`

#### Delete Tenant User
**Endpoint**: `DELETE /api/v1/tenant-users/{id}`

**Headers**: `Authorization: Bearer {tenant-token}`

#### Assign Flat to Tenant User
**Endpoint**: `POST /api/v1/tenant-users/{id}/assign-flat`

**Headers**: `Authorization: Bearer {tenant-token}`

**Request Body**:
```json
{
    "flat_id": 1
}
```

**Response**:
```json
{
    "message": "Flat assigned successfully",
    "tenant_user": {
        "id": 1,
        "name": "John Doe",
        "flat": {
            "id": 1,
            "flat_number": "A-101",
            "rent_amount": 1500.00
        }
    }
}
```

#### Remove Tenant User from Flat
**Endpoint**: `POST /api/v1/tenant-users/{id}/remove-flat`

**Headers**: `Authorization: Bearer {tenant-token}`

**Response**:
```json
{
    "message": "Tenant user removed from flat successfully"
}
```

## 🚨 Error Handling

### Standard Error Response
```json
{
    "message": "Error description",
    "errors": {
        "field_name": ["Validation error message"]
    }
}
```

### Common HTTP Status Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

### Authentication Errors
```json
{
    "message": "Unauthenticated."
}
```

### Validation Errors
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

## ⚡ Rate Limiting

- **Default**: 60 requests per minute per IP
- **Authentication**: 5 login attempts per minute per IP
- **Headers**: Rate limit information is included in response headers:
  ```
  X-RateLimit-Limit: 60
  X-RateLimit-Remaining: 59
  X-RateLimit-Reset: 1640995200
  ```

## 🔍 Health Checks

### Central API Health Check
**Endpoint**: `GET /api/health`

**Response**:
```json
{
    "status": "ok",
    "environment": "local",
    "timestamp": "2024-01-01T12:00:00.000000Z"
}
```

### Tenant API Health Check
**Endpoint**: `GET /api/v1/health`

**Response**:
```json
{
    "status": "ok",
    "tenant": 1,
    "domain": "johndoe.multi-domained-house-rent.test",
    "timestamp": "2024-01-01T12:00:00.000000Z"
}
```

## 📝 Examples

### Complete Workflow Example

#### 1. Admin Login
```bash
curl -X POST http://multi-domained-house-rent.test/api/v1/admin/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@houserent.test",
    "password": "password"
  }'
```

#### 2. Create New Tenant
```bash
curl -X POST http://multi-domained-house-rent.test/api/v1/tenants \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {admin-token}" \
  -d '{
    "name": "New Property Company",
    "domain": "newcompany.multi-domained-house-rent.test",
    "email": "admin@newcompany.com",
    "password": "password123"
  }'
```

#### 3. Tenant User Login
```bash
curl -X POST http://johndoe.multi-domained-house-rent.test/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@johndoe.com",
    "password": "password"
  }'
```

#### 4. Create House
```bash
curl -X POST http://johndoe.multi-domained-house-rent.test/api/v1/houses \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {tenant-token}" \
  -d '{
    "name": "Sunset Apartments",
    "address": "123 Main Street",
    "description": "Modern apartment complex"
  }'
```

#### 5. Create Floor
```bash
curl -X POST http://johndoe.multi-domained-house-rent.test/api/v1/houses/1/floors \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {tenant-token}" \
  -d '{
    "floor_number": 1,
    "description": "Ground floor with parking"
  }'
```

#### 6. Create Flat
```bash
curl -X POST http://johndoe.multi-domained-house-rent.test/api/v1/flats \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {tenant-token}" \
  -d '{
    "flat_number": "A-101",
    "rent_amount": 1500.00,
    "house_id": 1,
    "floor_id": 1,
    "description": "2 bedroom apartment"
  }'
```

#### 7. Create Tenant User
```bash
curl -X POST http://johndoe.multi-domained-house-rent.test/api/v1/tenant-users \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {tenant-token}" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "password": "password123"
  }'
```

#### 8. Assign Flat to Tenant User
```bash
curl -X POST http://johndoe.multi-domained-house-rent.test/api/v1/tenant-users/1/assign-flat \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {tenant-token}" \
  -d '{
    "flat_id": 1
  }'
```

## 🔧 Development Tools

### Testing API with cURL
```bash
# Test script included in project
./test-api.sh
```
