# Login Credentials

## Admin Login
**URL**: http://multi-domained-house-rent.test/admin/login
**Email**: admin@example.com
**Password**: password

## Tenant Logins

### John Doe Properties
**URL**: http://johndoe.multi-domained-house-rent.test/login
(or http://multi-domained-house-rent.test/login)
**Email**: john@johndoe.com
**Password**: password

### Smith Realty Group
**URL**: http://smithrealty.multi-domained-house-rent.test/login
(or http://multi-domained-house-rent.test/login)
**Email**: manager@smithrealty.com
**Password**: password

### City Apartments Inc
**URL**: http://cityapartments.multi-domained-house-rent.test/login
(or http://multi-domained-house-rent.test/login)
**Email**: manager@cityapartments.com
**Password**: password

## Notes
- The tenant login works from both the subdomain and the main domain
- When using the main domain, the system automatically detects the correct tenant based on the email
- City Apartments tenant is suspended for testing purposes
- All passwords are set to "password" for testing