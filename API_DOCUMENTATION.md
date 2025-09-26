# House Rent Management System API Documentation

## Overview
Multi-tenant house rental management system with subdomain-based tenancy. Each house owner gets their own subdomain and isolated database.

## Setup Instructions

### 1. Install Dependencies
```bash
composer install
```

### 2. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your database credentials:
```
DB_DATABASE=house_rent_central
DB_USERNAME=your_username
DB_PASSWORD=your_password
APP_DOMAIN=houserent.test
```

### 3. Database Setup
```bash
# Create central database
php artisan migrate --database=central

# Seed initial admin users
php artisan db:seed
```

### 4. Configure Local Domain
Add to your `/etc/hosts` file:
```
127.0.0.1 houserent.test
127.0.0.1 *.houserent.test
```

## Default Credentials
- Admin Email: `admin@houserent.test`
- Admin Password: `password`

## API Endpoints

### Central API (Admin Panel)
Base URL: `http://houserent.test/api/v1`

#### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/admin/login` | Admin login |
| POST | `/logout` | Logout (requires auth) |
| GET | `/me` | Get current user info |

#### Tenant Management (Admin Only)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/tenants` | List all tenants |
| POST | `/tenants` | Register new tenant |
| GET | `/tenants/{id}` | Get tenant details |
| PUT | `/tenants/{id}` | Update tenant |
| DELETE | `/tenants/{id}` | Delete tenant |
| POST | `/tenants/{id}/suspend` | Suspend tenant |
| POST | `/tenants/{id}/activate` | Activate tenant |

### Tenant API
Base URL: `http://{subdomain}.houserent.test/api/v1`

#### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/login` | Tenant user login |
| POST | `/logout` | Logout |
| GET | `/me` | Get current user |

#### House Management
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/houses` | List all houses |
| POST | `/houses` | Create new house |
| GET | `/houses/{id}` | Get house details |
| PUT | `/houses/{id}` | Update house |
| DELETE | `/houses/{id}` | Delete house |
| GET | `/houses/{id}/statistics` | Get house statistics |

#### Floor Management
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/houses/{houseId}/floors` | List floors in a house |
| POST | `/houses/{houseId}/floors` | Add floor to house |
| GET | `/houses/{houseId}/floors/{id}` | Get floor details |
| PUT | `/houses/{houseId}/floors/{id}` | Update floor |
| DELETE | `/houses/{houseId}/floors/{id}` | Delete floor |

#### Flat Management
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/flats` | List all flats |
| POST | `/flats` | Create new flat |
| GET | `/flats/{id}` | Get flat details |
| PUT | `/flats/{id}` | Update flat |
| DELETE | `/flats/{id}` | Delete flat |
| PATCH | `/flats/{id}/status` | Update flat status |
| GET | `/flats/available` | Get available flats |

#### Tenant User Management
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/tenant-users` | List tenant users |
| POST | `/tenant-users` | Create tenant user |
| GET | `/tenant-users/{id}` | Get user details |
| PUT | `/tenant-users/{id}` | Update user |
| DELETE | `/tenant-users/{id}` | Delete user |
| POST | `/tenant-users/{id}/assign-flat` | Assign flat to user |
| POST | `/tenant-users/{id}/remove-flat` | Remove user from flat |

## Request Examples

### 1. Admin Login
```bash
curl -X POST http://houserent.test/api/v1/admin/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@houserent.test",
    "password": "password"
  }'
```

### 2. Register New Tenant
```bash
curl -X POST http://houserent.test/api/v1/tenants \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "subdomain": "johndoe",
    "name": "John Doe Properties",
    "email": "john@example.com",
    "phone": "1234567890",
    "address": "123 Main St",
    "owner_name": "John Doe"
  }'
```

### 3. Create House (Tenant API)
```bash
curl -X POST http://johndoe.houserent.test/api/v1/houses \
  -H "Authorization: Bearer {tenant_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Sunrise Apartments",
    "address": "456 Oak Street",
    "city": "New York",
    "state": "NY",
    "zip_code": "10001",
    "total_floors": 5,
    "description": "Modern apartment complex",
    "amenities": ["parking", "gym", "pool"]
  }'
```

### 4. Create Flat
```bash
curl -X POST http://johndoe.houserent.test/api/v1/flats \
  -H "Authorization: Bearer {tenant_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "house_id": 1,
    "floor_id": 1,
    "flat_number": "101",
    "name": "Flat 101",
    "type": "2bhk",
    "bedrooms": 2,
    "bathrooms": 2,
    "size_sqft": 1200,
    "rent_amount": 2000,
    "security_deposit": 4000,
    "status": "available",
    "is_furnished": true
  }'
```

## Response Formats

### Success Response
```json
{
  "message": "Operation successful",
  "data": {...}
}
```

### Error Response
```json
{
  "message": "Error message",
  "errors": {
    "field": ["validation error"]
  }
}
```

### Pagination Response
```json
{
  "data": [...],
  "current_page": 1,
  "per_page": 15,
  "total": 100,
  "last_page": 7
}
```

## Status Codes
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 422: Validation Error
- 500: Server Error

## Flat Status Types
- `available`: Ready for rent
- `occupied`: Currently rented
- `maintenance`: Under maintenance
- `reserved`: Reserved for future

## User Status Types
- `active`: Active user
- `inactive`: Inactive user
- `pending`: Pending approval
- `terminated`: Terminated lease