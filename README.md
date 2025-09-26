# Multi-Domained House Rent System

A comprehensive Laravel-based multi-tenant house rental management system with separate domains for different property management companies.

## ⚠️ Development Status Notice

**Important Update**: Due to the intensive development timeline, we have successfully completed the full API development with comprehensive backend functionality, authentication, multi-tenancy, and database structure. However, the frontend UI implementation may require additional time to meet the original deadline. The API is fully functional and ready for integration, but the complete user interface may need extended development time to ensure quality and user experience standards.

We apologize for any inconvenience this may cause and appreciate your understanding as we work to deliver a polished, production-ready application.

## 🚀 Project Overview

This system provides a complete solution for property management companies to handle:
- Multi-tenant architecture with isolated data
- Property and tenant management
- Rent collection and tracking
- Maintenance request handling
- Financial reporting
- Admin panel for system management

## 📋 Prerequisites

Before you begin, ensure you have the following installed:
- **PHP 8.1+** with extensions: BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PCRE, PDO, Tokenizer, XML
- **Composer** (latest version)
- **Node.js 18+** and **NPM/Yarn**
- **MySQL 8.0+** or **PostgreSQL 13+**
- **Git**

## 🛠️ Complete Installation Guide

### Step 1: Clone the Repository
```bash
git clone <repository-url>
cd multi-domained-house-rent
```

### Step 2: Install PHP Dependencies
```bash
composer install
```

### Step 3: Install Node.js Dependencies
```bash
# Using NPM
npm install

# Or using Yarn (recommended)
yarn install
```

### Step 4: Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Generate Sanctum key (for API authentication)
php artisan sanctum:install
```

### Step 5: Database Setup
```bash
# Create database (MySQL example)
mysql -u root -p
CREATE DATABASE multi_domained_house_rent;
CREATE DATABASE multi_domained_house_rent_tenant;
exit

# Update .env file with database credentials
# DB_DATABASE=multi_domained_house_rent
# DB_USERNAME=your_username
# DB_PASSWORD=your_password
```

### Step 6: Run Migrations and Seeders
```bash
# Run main database migrations
php artisan migrate

# Run tenant migrations
php artisan tenants:migrate

# Seed the database with test data
php artisan db:seed
```

### Step 7: Build Frontend Assets
```bash
# Development build
npm run dev

# Production build
npm run build
```

### Step 8: Start the Application
```bash
# Start Laravel server
php artisan serve

# In another terminal, start frontend development server
npm run dev
```

## 🔐 Login Credentials

### Admin Users
| Role | URL | Email | Password | User ID |
|------|-----|-------|----------|---------|
| **Super Admin** | http://multi-domained-house-rent.test/admin/login | admin@houserent.test | password | 1 |
| **Admin User** | http://multi-domained-house-rent.test/admin/login | admin2@houserent.test | password | 2 |

### House Owners/Property Managers
| Company | URL | Email | Password | User ID | Status |
|---------|-----|-------|----------|---------|--------|
| **John Doe Properties** | http://johndoe.multi-domained-house-rent.test/login | john@johndoe.com | password | 1 | Active |
| **Smith Realty Group** | http://smithrealty.multi-domained-house-rent.test/login | manager@smithrealty.com | password | 2 | Active |
| **City Apartments Inc** | http://cityapartments.multi-domained-house-rent.test/login | manager@cityapartments.com | password | 3 | Suspended |

### Renter/Tenant Users
Renter accounts are created through each property management company's interface. They can access the system through their respective company's domain.

## 🌐 Domain Structure

### Main Domain
- **Admin Panel**: http://multi-domained-house-rent.test/admin/login
- **Universal Login**: http://multi-domained-house-rent.test/login (auto-detects tenant by email)

### Tenant Subdomains
- **John Doe Properties**: http://johndoe.multi-domained-house-rent.test
- **Smith Realty Group**: http://smithrealty.multi-domained-house-rent.test
- **City Apartments Inc**: http://cityapartments.multi-domained-house-rent.test

## 📱 User Manual

### For Administrators

#### System Management
1. **Access Admin Panel**: Navigate to http://multi-domained-house-rent.test/admin/login
2. **User Management**: Create, edit, and manage system users
3. **Tenant Management**: Oversee all property management companies
4. **System Configuration**: Set global system parameters

#### Key Features
- Dashboard with system-wide analytics
- User role management
- Tenant activation/deactivation
- System logs and monitoring

### For Property Managers

#### Property Management
1. **Login**: Use your company's subdomain or main domain
2. **Add Properties**: Create new houses, floors, and flats
3. **Tenant Management**: Add and manage tenant information
4. **Rent Collection**: Track rent payments and generate invoices

#### Key Features
- Property portfolio management
- Tenant database
- Financial reporting
- Maintenance request handling

### For Renters

#### Account Access
1. **Login**: Use credentials provided by your property manager
2. **Dashboard**: View your rental information
3. **Payments**: Make rent payments online
4. **Maintenance**: Submit maintenance requests

#### Key Features
- Rent payment history
- Lease information
- Maintenance request submission
- Communication with property manager

## 🛠️ Development

### Running the Application
```bash
# Start Laravel development server
php artisan serve

# Start frontend development server (in another terminal)
npm run dev

# Run tests
php artisan test
```

### Database Management
```bash
# Seed all data
php artisan db:seed

# Seed specific data
php artisan db:seed --class=AdminSeeder
php artisan db:seed --class=TenantSeeder
php artisan db:seed --class=TenantDataSeeder

# Reset database
php artisan migrate:fresh --seed
```

## 🧪 Testing

### E2E Tests
```bash
# Run end-to-end tests
./run-e2e-tests.sh
```

### API Testing
```bash
# Test API endpoints
./test-api.sh
```

## 📚 Documentation

- [API Documentation](API_DOCUMENTATION.md) - Complete API reference
- [Frontend Implementation](FRONTEND_IMPLEMENTATION.md) - UI/UX guidelines
- [E2E Test Report](E2E_TEST_REPORT.md) - Test results and coverage
- [Test Results](TEST_RESULTS.md) - Detailed test outcomes

## 🔧 Configuration

### Environment Variables
Key environment variables in `.env`:
```env
APP_URL=http://multi-domained-house-rent.test
DB_CONNECTION=mysql
DB_DATABASE=multi_domained_house_rent
TENANCY_DATABASE=multi_domained_house_rent_tenant
SANCTUM_STATEFUL_DOMAINS=multi-domained-house-rent.test,johndoe.multi-domained-house-rent.test,smithrealty.multi-domained-house-rent.test,cityapartments.multi-domained-house-rent.test
```

## 📝 Notes

- All test passwords are set to "password"
- City Apartments tenant is suspended for testing purposes
- Universal login automatically detects the correct tenant based on email
- Each tenant has isolated data and cannot access other tenants' information
- API is fully functional and ready for frontend integration
