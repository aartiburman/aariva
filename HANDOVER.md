# Project Handover Documentation: Nepoora Vendor Admin & API

This document provides a comprehensive overview of the Nepoora Vendor Admin backend project, its architecture, setup instructions, and API documentation for handover to the client.

## 1. Project Overview
Nepoora is a multi-vendor marketplace platform. This backend system manages vendor administration, user interactions, product management, orders, and payment processing.

- **Framework**: Laravel 12.x
- **Language**: PHP 8.2+
- **Database**: MySQL / MariaDB
- **Authentication**: Laravel Sanctum (Token-based)
- **Routing Architecture**: 
    - **Customer**: RESTful API endpoints defined in `routes/api.php`.
    - **Admin & Vendor**: Web-based dashboard routes defined in `routes/web.php`.
- **Notifications**: Firebase Cloud Messaging (FCM)
- **Payments**: PayPal, Khalti
- **SMS Gateway**: Sparrow SMS

## 2. Technology Stack
- **Backend**: Laravel Framework
- **API**: RESTful API with JSON responses
- **Frontend (Admin)**: Blade Templates with customized Admin Dashboard assets
- **Key Libraries**:
    - `kreait/laravel-firebase`: Firebase integration
    - `laravel/sanctum`: API Authentication
    - `srmklive/paypal`: PayPal Payment integration
    - `stevebauman/location`: Geo-location services
    - `stichoza/google-translate-php`: Multi-language support

## 3. Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- Composer
- MySQL/MariaDB
- Node.js & NPM (for asset compilation)

## 4. Customer API Documentation (api.php)
This section lists the RESTful API endpoints used by the Customer mobile application. All customer-facing functionality is handled via the API.

### A. Authentication & Account
| Endpoint | Method | Description | Key Parameters |
|----------|--------|-------------|----------------|
| `/api/signup` | POST | Register a new customer | `name`, `email_or_phone`, `password`, `device_token` |
| `/api/userlogin` | POST | Standard login | `email_or_phone`, `password` |
| `/api/social-login` | POST | Social media login (Google/Apple) | `provider`, `access_token` |
| `/api/verify-otp` | POST | Verify OTP for account/password | `email_or_phone`, `otp` |
| `/api/forgot-password` | POST | Request password reset OTP | `email_or_phone` |
| `/api/reset-password` | POST | Set new password with OTP | `email_or_phone`, `otp`, `password` |
| `/api/logout` | GET | Revoke access token | Requires Auth Token |

### B. Profile & KYC
| Endpoint | Method | Description | Key Parameters |
|----------|--------|-------------|----------------|
| `/api/my-profile` | GET | Fetch user profile data | Requires Auth Token |
| `/api/update-profile` | POST | Update name, email, phone, image | `name`, `email`, `phone`, `image` |
| `/api/update-kyc-documents` | POST | Upload KYC docs (ID, etc.) | `document_type`, `file` |
| `/api/get-my-document` | GET | Fetch uploaded KYC docs | Requires Auth Token |
| `/api/get-referral-details` | GET | Get referral code and earnings | Requires Auth Token |

### C. Marketplace & Products
| Endpoint | Method | Description | Key Parameters |
|----------|--------|-------------|----------------|
| `/api/home` | POST | Home screen banners & sections | - |
| `/api/get-categories` | GET | List all main categories | - |
| `/api/get-subcategories` | GET | List subcategories by category | `category_id` |
| `/api/product-list` | GET | Paginated product list with filters | `category_id`, `brand_id`, `sort` |
| `/api/product-search` | GET | Search products by keywords | `query` |
| `/api/get-product-detail` | GET | Full product details & variants | `product_id` |
| `/api/get-featured-product` | GET | List featured items | - |
| `/api/get-trending-product` | GET | List trending items | - |

### D. Cart, Wishlist & Orders
| Endpoint | Method | Description | Key Parameters |
|----------|--------|-------------|----------------|
| `/api/add-remove-cart` | POST | Sync cart items | `product_id`, `qty`, `variant_id` |
| `/api/get-cart-detail` | GET | Get cart summary & totals | Requires Auth Token |
| `/api/add-to-wishlist` | POST | Toggle product in wishlist | `product_id` |
| `/api/get-wishlist` | POST | List wishlist items | Requires Auth Token |
| `/api/place-order` | POST | Create order & select payment | `user_id`, `payment_mode`, `address_id` |
| `/api/my-orders` | GET | List user's order history | Requires Auth Token |
| `/api/get-order-detail` | GET | Status and items of an order | `order_id` |
| `/api/track-order` | GET | Real-time tracking status | `order_id` |

### E. Payments (Khalti & PayPal)
| Endpoint | Method | Description | Key Parameters |
|----------|--------|-------------|----------------|
| `/api/khalti/verify` | POST | Verify Khalti payment status | `token`, `amount` |
| `/api/paypal/create-payment`| POST | Initiate PayPal checkout | `order_id` |
| `/api/paypal/capture-payment`| POST | Confirm PayPal transaction | `payment_id`, `payer_id` |

---

## 5. Admin & Vendor Web Dashboard (web.php)
All administrative and vendor actions are performed through the web-based dashboard using Laravel Blade templates. These routes are protected by session-based authentication.

### A. Admin Dashboard Menus & Functionality
The Admin panel is the central control hub for the entire marketplace.
- **Dashboard**: Provides a high-level overview of total sales, active vendors, pending orders, and system health.
- **Vendor Management**:
    - **Vendor List**: Manage active vendors, edit their profiles, or suspend accounts.
    - **Vendor Request**: Review and approve/reject new vendor applications and their business documents.
    - **Payouts**: Review, approve, and process withdrawal requests from vendors.
- **Category & Brands**:
    - **Category/Subcategory/Child Category**: Hierarchical management of product classifications.
    - **Brand**: Manage product brands available on the platform.
- **Products**:
    - **Product List**: Global view of all products (Admin and Vendor owned).
    - **Add Product (Single/Similar/Bulk)**: Tools for catalog management and inventory expansion.
    - **Product Size**: Manage size charts and variations across different categories.
- **Campaigns**: Create and manage time-bound marketing events (e.g., "Black Friday") that vendors can join.
- **Orders & Refunds**:
    - **Order List**: Track every transaction, update delivery statuses, and manage cancellations.
    - **Refund Requests**: Centralized management of customer refund claims.
- **POS (Point of Sale)**: Interface for manual order creation and physical store transactions.
- **Offers & Coupons**: Manage platform-wide discounts, flash sales, and promotional codes.
- **CMS (Content Management)**:
    - **Banner/Blogs**: Manage visual assets and informational content.
    - **Legal Pages**: Edit Privacy Policy, T&C, Vendor Policy, About Us, and FAQs.
- **Reports**:
    - **Sales/Vendor/Product Reports**: Detailed analytics for business intelligence.
    - **KYC Verification**: Track the verification status of all platform participants.
- **Support**: Manage support tickets from vendors and handle escalated customer issues.
- **Website Settings**:
    - **General/Global Fees**: Configure site identity and commission rates.
    - **Gateways (Payment/SMS/Mail/FCM)**: Technical configuration for third-party integrations.

### B. Vendor Dashboard Menus & Functionality
The Vendor panel is a restricted dashboard for sellers to manage their own stores.
- **Dashboard**: Store-specific analytics, including total earnings and current wallet balance.
- **My Profile & KYC**: Manage store identity and upload mandatory business verification documents.
- **Campaigns**: Opt-in to platform-wide campaigns created by the Admin to boost visibility.
- **Wallet & Payouts**: Real-time tracking of earnings and submission of withdrawal requests.
- **Product Management**:
    - **Product List**: Manage store-specific inventory, pricing, and stock levels.
    - **Add Product**: Tools to list new items individually or in bulk.
- **Orders & Refunds**:
    - **Order List**: Manage order fulfillment, print invoices, and update shipping status for store orders.
    - **Refund Requests**: Respond to customer refund requests for store-specific products.
- **Sales Report**: Performance analytics for the individual store.
- **Support**:
    - **My Tickets**: Communication channel with the Platform Admin.
    - **Customer Tickets**: Manage direct support queries from customers.
- **Delivery Settings**: Configure shipping zones and delivery parameters specific to the vendor's operations.

---

## 6. Environment Configuration (.env)
Key variables that must be configured:
- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: Database connection.
- `SANCTUM_STATEFUL_DOMAINS`: Domains allowed for Sanctum auth.
- `FIREBASE_API_KEY`, `FIREBASE_PROJECT_ID`: FCM configuration.
- `PAYPAL_CLIENT_ID`, `PAYPAL_CLIENT_SECRET`: PayPal integration.
- `KHALTI_PUBLIC_KEY`, `KHALTI_SECRET_KEY`: Khalti integration.
- `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`: SMTP settings.
- `SPARROW_SMS_TOKEN`: SMS Gateway token.

## 7. Directory Structure
- `app/Http/Controllers/Api/`: All API-specific logic (Customer).
- `app/Http/Controllers/Admin/`: Backend dashboard controllers (Admin & Vendor).
- `app/Models/`: Eloquent models representing database tables.
- `app/Helpers/`: Utility classes for common tasks (Price, SMS, Email).
- `routes/api.php`: Definition of all Customer API endpoints.
- `routes/web.php`: Definition of all Admin and Vendor web routes.
- `public/uploads/`: Directory for uploaded images and documents.
<!-- 
## 8. Deployment Notes
- Ensure the `storage` and `bootstrap/cache` directories are writable by the web server.
- Use `php artisan config:cache` and `php artisan route:cache` in production for better performance.
- Configure a cron job for Laravel's task scheduler:
  `* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1` -->
