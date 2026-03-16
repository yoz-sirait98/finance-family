# Family Finance Backend API

This directory contains the REST API for the Family Finance Management System, built with **Laravel 11** and **PHP 8.3**.

## Requirements
*   PHP 8.3 or higher
*   Composer
*   MySQL 8.0+

## Local Setup

1.  **Install dependencies:**
    ```bash
    composer install
    ```
2.  **Environment Setup:**
    Duplicate `.env.example` as `.env`.
    Configure your database credentials:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=finance_family
    DB_USERNAME=root
    DB_PASSWORD=
    ```
3.  **Generate app key:**
    ```bash
    php artisan key:generate
    ```
4.  **Run Migrations and Seeders:**
    This application requires base categories to function nicely. The seeder also provides a demo family.
    ```bash
    php artisan migrate
    php artisan db:seed
    ```
5.  **Start the local server:**
    ```bash
    php artisan serve --port=8000
    ```

## Architecture Details

*   **Authentication**: We use Laravel Sanctum issuing plain-text Bearer Tokens. All routes under `/api/*` expect this token except `/api/auth/login` and `/api/auth/register`.
*   **Services Pattern**: Thick controllers are avoided. Business logic, specifically for cascading account balance recalculations (`AccountBalanceService`), dual-entry transfers (`TransactionService`), and deadline checking (`RecurringTransactionService`) exist in the `app/Services` folder.
*   **Localization**: The app defaults to `Asia/Jakarta` timezone and assumes IDR format logic for monetary values.

## Scheduled Tasks

The application has a background job that automatically inserts returning transactions that are due. For production, ensure you add the Laravel scheduler to your server's cron tab:

```bash
* * * * * cd /path-to-your-project/backend && php artisan schedule:run >> /dev/null 2>&1
```

## Available API Endpoint Groups
*   `POST /auth/*`: Login, Register, Logout, Password changes
*   `CRUD /members`: Family member profiles
*   `CRUD /accounts`: Bank/Cash balances
*   `CRUD /categories`: Income/Expense labels
*   `CRUD /transactions`: Core ledger (Includes `POST /transactions/transfer`)
*   `CRUD /budgets`: Monthly budget caps and alerts
*   `CRUD /goals`: Target savings and contributions
*   `CRUD /recurring-transactions`: Automation rules
*   `GET /dashboard/*`: Aggregated UI metric endpoints
