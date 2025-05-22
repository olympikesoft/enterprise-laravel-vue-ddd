# Corporate Fundraising Platform

This application allows employees within large corporations to create, manage, and search for fundraising campaigns, as well as donate to causes. It also includes an administration panel for managing application parameters and viewing dashboards.

## High-Level Features

*   **Campaign Management:** Authenticated employees can create, manage (edit), and search fundraising campaigns for causes they believe other employees should support.
*   **Donations:** Authenticated employees can donate to campaigns. They will receive a confirmation for their donation.
*   **Administration Panel:** A restricted section for administrators to:
    *   Manipulate application parameters.
    *   View dashboards (e.g., user stats, campaign overview).
    *   Manage campaigns (approve, reject, view all).
    *   Manage users.
*   **Payment System Integration:** Designed to be flexible, as the specific payment system is to be decided later. (Currently, donation processing will be simulated or use a placeholder).

## Technology Stack

*   **Backend:**
    *   PHP 8.2+
    *   Laravel 12.x
    *   Composer for dependency management
    *   Pest for PHP testing
    *   PHPStan (Level 8) for static analysis
    *   Laravel Sanctum for API authentication
    *   Laravel Queues (e.g., for email notifications)
*   **Frontend:**
    *   Vue.js 3.x
    *   Vite for frontend tooling
    *   Vuetify 3.x for UI components
    *   Pinia for state management
    *   Vue Router for client-side routing
    *   Axios for HTTP requests
*   **Database:**
    *   SQLite (default for easy setup, configurable for PostgreSQL, MySQL, or MariaDB)

## Architectural Choices & Philosophy

This project adopts a **Domain-Driven Design (DDD)** approach, aiming for a clean, maintainable, and scalable codebase.

1.  **Layered Architecture:**
    *   **Interfaces (Presentation) Layer:** Located in `app/Interfaces/Http`. Handles HTTP requests, responses, and transformations (e.g., DTOs for requests/responses). Contains API Controllers.
    *   **Application Layer:** Located in `app/Application`. Orchestrates use cases, coordinating domain objects and infrastructure services. Contains Application Services/Commands/Queries.
    *   **Domain Layer:** Located in `app/Domain`. The heart of the application, containing business logic, entities, aggregates, value objects, domain events, and repository interfaces. It's independent of other layers.
        *   **Subdomains:** The domain is broken down into subdomains like `Campaign`, `Donation`, and `User` (or `Employee`) to manage complexity.
        *   **Shared Kernel:** `app/Domain/Shared` contains common value objects and concepts used across multiple subdomains (e.g., `Money`).
    *   **Infrastructure Layer:** Located in `app/Infrastructure`. Provides implementations for interfaces defined in the domain or application layers (e.g., Eloquent repositories, payment gateway integrations, email services).

2.  **Key DDD Patterns Used:**
    *   **Aggregates:** Clusters of domain objects that are treated as a single unit (e.g., `Campaign` with its related entities).
    *   **Entities:** Objects with a distinct identity that persists over time.
    *   **Value Objects:** Immutable objects representing descriptive aspects of the domain (e.g., `Money`, `CampaignStatus`).
    *   **Repositories:** Abstractions over data persistence, decoupling the domain from specific database technologies. Interfaces are in `app/Domain/.../Repository`, implementations in `app/Infrastructure/Persistence/Eloquent`.
    *   **Domain Events:** Represent significant occurrences within the domain (e.g., `CampaignCreated`, `DonationSucceeded`). These can be used to trigger side effects or communicate between subdomains.
    *   **Application Services / Commands & Queries:** Encapsulate specific use cases or operations.

3.  **Why DDD?**
    *   **Complexity Management:** For a B2B application with potentially evolving business rules, DDD helps manage complexity by focusing on the core domain.
    *   **Maintainability & Testability:** Clear separation of concerns makes the code easier to understand, modify, and test.
    *   **Scalability:** Well-defined boundaries between layers and subdomains facilitate scaling parts of the application independently.
    *   **Alignment with Business:** DDD encourages close collaboration with domain experts, leading to a model that accurately reflects the business.

## Prerequisites

*   PHP >= 8.2
*   Composer
*   Node.js >= 18.x
*   NPM or Yarn
*   A supported database (SQLite, PostgreSQL, MySQL, or MariaDB)

## Setup and Installation

1.  **Clone the repository:**
    ```bash
    git clone <your-repository-url>
    cd <project-directory>
    ```

2.  **Install PHP dependencies:**
    ```bash
    composer install
    ```

3.  **Install Node.js dependencies:**
    ```bash
    npm install
    ```

4.  **Set up environment file:**
    Copy the example environment file and generate an application key:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5.  **Configure your `.env` file:**
    *   Set up your database connection (e.g., for SQLite, ensure `DB_CONNECTION=sqlite` and the file `database/database.sqlite` exists or can be created).
        ```dotenv
        DB_CONNECTION=sqlite
        # For other databases:
        # DB_HOST=127.0.0.1
        # DB_PORT=3306
        # DB_DATABASE=your_db_name
        # DB_USERNAME=your_db_user
        # DB_PASSWORD=your_db_password
        ```
    *   Ensure `SANCTUM_STATEFUL_DOMAINS` and `SESSION_DOMAIN` are set correctly if your frontend and backend are on different subdomains/ports during development (e.g., `localhost:3000,localhost:8000`).
        ```dotenv
        # Example for Vite dev server and Laravel serve
        SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173
        SESSION_DOMAIN=localhost
        FRONTEND_URL=http://localhost:5173
        ```

6.  **Create the database file (if using SQLite):**
    ```bash
    touch database/database.sqlite
    ```

7.  **Run database migrations (and seeders if available):**
    ```bash
    php artisan migrate --seed
    # If you need to refresh the database:
    # php artisan migrate:fresh --seed
    ```

## Running the Application

### Development Mode (Recommended)

The `composer.json` includes a convenient script to run all necessary development services concurrently:
```bash
composer run dev
