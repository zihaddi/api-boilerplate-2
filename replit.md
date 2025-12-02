# Laravel 11 Multi-Tenant Boilerplate

## Overview
A production-ready Laravel 11 boilerplate with comprehensive multi-user authentication, role-based access control (RBAC), dynamic menu management, and integrated payment gateway system supporting multiple providers.

## Project Structure

```
app/
├── Http/
│   ├── Controllers/Api/Admin/
│   │   ├── AuthController.php          # Authentication endpoints
│   │   ├── PaymentController.php       # Payment management
│   │   ├── RoleController.php          # Role management
│   │   ├── RolePermissionController.php # Permission management
│   │   ├── TreeEntityController.php    # Menu management
│   │   └── UserController.php          # User management
│   └── Middleware/
│       └── CheckPermission.php         # Route authorization middleware
├── Models/
│   ├── User.php                        # User model with permission helpers
│   ├── Role.php                        # Role model
│   ├── RolePermission.php              # Role-Permission mapping
│   ├── TreeEntity.php                  # Dynamic menu entities
│   └── Payment.php                     # Payment transactions
├── Services/
│   └── Payment/
│       ├── PaymentGatewayInterface.php # Gateway contract
│       ├── PaymentService.php          # Payment orchestration
│       ├── StripeGateway.php           # Stripe implementation
│       ├── PayPalGateway.php           # PayPal implementation
│       └── SSLCommerzGateway.php       # SSLCommerz implementation
database/
├── migrations/
│   └── 2025_11_30_100000_create_payments_table.php
├── seeders/
│   ├── DatabaseSeeder.php
│   ├── RolesSeeder.php                 # 5 default roles
│   ├── BoilerplateMenuSeeder.php       # Admin menu structure
│   └── RolePermissionsSeeder.php       # Role-based permissions
routes/
└── api/
    └── admin.php                       # Admin API routes
```

## Features

### Authentication System
- JWT-based authentication with Laravel Sanctum
- Access and refresh token management
- OTP verification for registration/password reset
- User session management

### Role-Based Access Control (RBAC)
**Default Roles:**
1. Super Admin (ID: 1) - Full system access
2. Admin (ID: 2) - Administrative access except system settings
3. Manager (ID: 3) - Service and appointment management
4. Staff (ID: 4) - Basic operational access
5. Customer (ID: 5) - View-only dashboard access

**Permission Types:**
- View: Read access to resources
- Add: Create new resources
- Edit: Modify own resources
- Edit Other: Modify others' resources
- Delete: Remove own resources
- Delete Other: Remove others' resources

### Dynamic Menu System
Menu management through `TreeEntity` model supporting:
- Hierarchical parent-child relationships
- Route-based access control
- Icon and ordering customization
- Role-specific visibility

### Payment Gateway System
**Supported Gateways:**
1. **Stripe** - Credit card payments
2. **PayPal** - PayPal account payments
3. **SSLCommerz** - Bangladesh payment gateway

**Features:**
- Unified payment interface
- Webhook handling for all gateways
- Transaction tracking and history
- Refund processing
- Payment statistics and reporting

## API Endpoints

### Authentication
```
POST /api/admin/login          # User login
POST /api/admin/otp-resend     # Resend OTP
POST /api/admin/otp-verify     # Verify OTP
POST /api/admin/set-password   # Set new password
POST /api/admin/forgot-password # Password recovery
POST /api/admin/refresh-token  # Refresh access token
POST /api/admin/user           # Get authenticated user
POST /api/admin/logout         # Logout
```

### Payment Management
```
GET  /api/admin/payments           # List all payments
GET  /api/admin/payments/stats     # Payment statistics
GET  /api/admin/payments/gateways  # Available gateways
GET  /api/admin/payments/my-payments # User's payments
POST /api/admin/payments/initiate  # Create payment
GET  /api/admin/payments/{id}      # Payment details
POST /api/admin/payments/{id}/confirm # Confirm payment
POST /api/admin/payments/{id}/refund  # Refund payment
```

### Payment Webhooks (No auth)
```
POST /api/payments/stripe/webhook
POST /api/payments/paypal/webhook
POST /api/payments/sslcommerz/success
POST /api/payments/sslcommerz/fail
POST /api/payments/sslcommerz/cancel
POST /api/payments/sslcommerz/ipn
```

## Environment Variables

### Required
```env
APP_KEY=
DB_CONNECTION=sqlite
```

### Payment Gateways (Optional)
```env
# Stripe
STRIPE_KEY=
STRIPE_SECRET_KEY=
STRIPE_WEBHOOK_SECRET=

# PayPal
PAYPAL_CLIENT_ID=
PAYPAL_CLIENT_SECRET=
PAYPAL_MODE=sandbox

# SSLCommerz
SSLCZ_STORE_ID=
SSLCZ_STORE_PASSWORD=
SSLCZ_SANDBOX_MODE=true
```

## Default Credentials
- Email: admin@boilerplate.com
- Password: password123

## Setup Commands
```bash
php artisan migrate --force
php artisan db:seed
```

## Architecture Patterns

### Repository Pattern
All data access goes through repository classes for testability and maintainability.

### Service Layer
Business logic is encapsulated in service classes (e.g., PaymentService).

### Gateway Pattern
Payment gateways implement a common interface for easy extension.

### Middleware Authorization
Route protection via CheckPermission middleware with action-based checks.

**Security Model (Fail-Closed):**
- Routes without explicit TreeEntity mapping are DENIED by default
- Hierarchical route matching supports nested route names (e.g., `payments.refund` falls back to `payments`)
- Permission values support both boolean and entity ID formats for flexibility
- Super Admin role bypasses all permission checks

### Dependency Injection Pattern
Payment gateways use Laravel's service container for proper dependency injection:
- Gateway classes are resolved via `App::make()` for testability
- Configuration loaded from `config/payment.php`
- Easy to add custom gateways by implementing `PaymentGatewayInterface`

## User Permission Helpers
```php
$user->isSuperAdmin();           // Check super admin
$user->isAdmin();                // Check admin role
$user->hasRole('Manager');       // Check specific role
$user->hasPermission('users', 'edit'); // Check permission
$user->canView('payments');      // Shorthand view check
$user->canAdd('payments');       // Shorthand add check
$user->canEdit('payments');      // Shorthand edit check
$user->canDelete('payments');    // Shorthand delete check
```

## Recent Changes
- 2025-11-30: Fixed CheckPermission middleware authorization bypass
- 2025-11-30: Created comprehensive boilerplate seeders
- 2025-11-30: Implemented multi-gateway payment system
- 2025-11-30: Added User model permission helpers
- 2025-11-30: Integrated payment routes with authorization
