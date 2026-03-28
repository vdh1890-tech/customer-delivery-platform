# KR Blue Metals - Customer Delivery Platform

Welcome to the KR Blue Metals codebase. This project is a web-based delivery and management platform for construction materials.

## Project Structure Overview

The project is organized into a modular architecture to separate concerns between the public website, admin interface, and other functional areas.

### 📁 Root Directories

- **`modules/`**: Contains the core sub-applications.
    - `AdminPortal`: Administrative dashboard and management tools.
    - `CustomerPortal`: Customer authentication and profile management.
    - `DriverPanel`: Interface for drivers to view and update deliveries.
    - `ProductManagement`: Product CRUD operations.
    - `PerformanceInsights`: Business analytics and reporting.
    - `AutomatedInvoice`: Invoice generation logic.
    - *(See `modules/README.md` for details on internal module structure)*

- **`includes/`**: Essential shared components.
    - `db_connect.php`: Database connection settings.
    - `header.php`: The global website header and navigation logic.
    - `sms_helper.php`: Helper for sending SMS notifications.

- **`assets/`**: Globally shared static resources.
    - `css/`: Stylesheets (e.g., `style.css`).
    - `img/`: Images and icons.
    - `js/`: JavaScript files (e.g., `app.js`).

- **`api/`**: Centralized API endpoints for general application usage.
    - `auth_login.php`, `auth_register.php`: Authentication handlers.
    - `place_order.php`: Order submission logic.
    - `get_current_user.php`: Session data retrieval.

### 📄 Public Root Files

These files are the public face of the website:

- **`index.php`**: The main landing page.
- **`catalog.php`**: Product catalog browsing page.
- **`checkout.php`**: Shopping cart and checkout flow.
- **`about.php`**: Company information.
- **`contact.php`**: Contact form and details.
- **`careers.php`**: Job listings page.
- **`track_order.html`**: Public order tracking page.

## Getting Started

1. **Database**: Import the provided SQL file into your MySQL database.
2. **Configuration**: Update `includes/db_connect.php` with your database credentials.
3. **Access**:
    - Public Site: `http://localhost/customer_delivery_platform/`
    - Admin Portal: `http://localhost/customer_delivery_platform/modules/AdminPortal/login.php`
    - Driver Panel: `http://localhost/customer_delivery_platform/modules/DriverPanel/login.php`
