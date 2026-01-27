# Beayar ERP - Getting Started Guide

Welcome to the **Beayar ERP** project! This guide will help you set up the application locally, run it, and start developing.

## Prerequisites

Before you begin, ensure you have the following installed on your machine:

*   **PHP** >= 8.2
*   **Composer** (PHP Dependency Manager)
*   **Node.js** & **NPM** (For frontend assets)
*   **MySQL** or **MariaDB** (Database)

## Installation Steps

### 1. Clone the Repository

```bash
git clone <repository_url>
cd beayar-erp
```

### 2. Install Backend Dependencies

Install the PHP packages via Composer:

```bash
composer install
```

### 3. Install Frontend Dependencies

Install the JavaScript packages via NPM:

```bash
npm install
```

### 4. Environment Configuration

Copy the example environment file to create your own `.env` file:

```bash
cp .env.example .env
```

Open the `.env` file and configure your database connection:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=beayar
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. Generate Application Key

Generate the Laravel application key:

```bash
php artisan key:generate
```

### 6. Run Migrations & Seeders

Create the database tables and populate them with initial data:

```bash
php artisan migrate --seed
```

> **Note:** If you are migrating legacy data, refer to `docs/phase7.md` for specific migration commands.

### 7. Run the Application

You will need to run two separate commands in different terminal windows:

**Terminal 1: Start the Laravel Backend Server**
```bash
php artisan serve
```
The API/Backend will be available at `http://localhost:8000`.

**Terminal 2: Start the Vite Frontend Server**
```bash
npm run dev
```
This compiles assets in real-time.

## Project Structure

*   **`app/Models`**: Eloquent models (Business entities like `User`, `Quotation`, `Bill`).
*   **`app/Services`**: Business logic layer (e.g., `SubscriptionService`, `QuotationService`).
*   **`app/Http/Controllers`**: API and View controllers.
*   **`database/migrations`**: Database schema definitions.
*   **`tests/`**: Unit and Feature tests.
*   **`docs/`**: Project documentation (Phases, API docs).

## Running Tests

To ensure everything is working correctly, run the test suite:

```bash
php artisan test
```

## Troubleshooting

*   **Database Connection Refused:** Check if your MySQL server is running and the credentials in `.env` are correct.
*   **Vite Manifest Not Found:** Ensure you have run `npm run dev` or `npm run build`.
*   **Permission Denied:** Ensure `storage/` and `bootstrap/cache/` directories are writable (`chmod -R 775 storage bootstrap/cache`).

## Next Steps

*   Review **Phase Documentation** in `docs/` to understand the implementation plan.
*   Check `docs/phase8.md` for the latest testing and verification status.
