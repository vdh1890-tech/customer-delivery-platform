# Project Modules Structure

This project has been reorganized into a modular structure to improve maintainability and scalability.

## Directory Layout

The `modules/` directory contains the following sub-modules, each responsible for a specific domain of the application:

### 1. AdminPortal (`modules/AdminPortal/`)
Contains the administrative interface and logic.
- **dashboard.php**: Main admin dashboard.
- **orders.php, customers.php, drivers.php**: Management pages.
- **includes/**: Admin-specific headers and footers.
- **APIs**: `delete_item.php`, `update_order_status.php`, `update_price.php`.

### 2. CustomerPortal (`modules/CustomerPortal/`)
Handles customer-facing authentication and account management.
- **login.php, signup.php**: User authentication.
- **my_account.php**: Customer profile and order history.

### 3. DriverPanel (`modules/DriverPanel/`)
Interface for drivers to manage deliveries.
- **dashboard.php**: Driver's main view showing assigned orders.

### 4. ProductManagement (`modules/ProductManagement/`)
Dedicated module for managing the product catalog.
- **index.php**: Product listing, adding, editing, and deleting products.
- **add_product.php, update_product.php**: Logic for product operations.

### 5. PerformanceInsights (`modules/PerformanceInsights/`)
Analytics and reporting.
- **analytics.php**: Visual reports on sales, orders, and revenue.

### 6. AutomatedInvoice (`modules/AutomatedInvoice/`)
Invoice generation.
- **invoice.php**: Generates printable invoices for orders.

## Common Directories
- **includes/** (Root): Contains shared logic like `db_connect.php` and the global `header.php`.
- **assets/** (Root): Contains shared CSS, JS, and Images.

## Development Notes
- When including files, always use relative paths (e.g., `../../includes/db_connect.php`).
- The global `header.php` automatically detects the root path based on the script location.
