# Test Suite

The AZ Portfolio Symfony test suite uses PHPUnit, Symfony's `WebTestCase` and `KernelTestCase`, Doctrine ORM, and MySQL.

PHPUnit is configured through `phpunit.dist.xml`, with the test bootstrap in `tests/bootstrap.php`. The suite includes unit, integration, and functional tests.

## Prerequisites

- Docker Desktop running.
- The application's Docker Compose services available.
- Composer dependencies installed in the app container.
- A dedicated MySQL test database.
- The Symfony test environment enabled when running PHPUnit.

## Prepare the Test Database

Start the application and database services:

```powershell
docker compose up -d database app
```

The test suite uses a dedicated `az_portfolio_test` database. Create it and grant the application database user access:

```powershell
docker compose exec database mysql -uroot -proot_password -e "CREATE DATABASE IF NOT EXISTS az_portfolio_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL PRIVILEGES ON az_portfolio_test.* TO 'az_portfolio'@'%'; FLUSH PRIVILEGES;"
```

This command assumes the MySQL root password is `root_password` and the application database user is `az_portfolio`, matching the local Docker configuration. Adjust the credentials if your local configuration differs.

## Run the Tests

From the project root, run PHPUnit inside the application container:

```powershell
docker compose exec -e APP_ENV=test app php bin/phpunit
```

**Important:** Explicitly setting `APP_ENV=test` is currently required for the test suite to initialize the Symfony test environment correctly.

To run an individual test file:

```powershell
docker compose exec -e APP_ENV=test app php bin/phpunit tests/Unit
docker compose exec -e APP_ENV=test app php bin/phpunit tests/Integration
docker compose exec -e APP_ENV=test app php bin/phpunit tests/Functional
```

To clear the test cache if configuration changes cause problems:

```powershell
docker compose exec -e APP_ENV=test app php bin/console cache:clear --env=test
```

## Database Safety

`DatabaseTestTrait` resets the database tables used by database-dependent tests. Run the suite only against the dedicated test database.

Never configure `APP_ENV=test` to use a database containing development, staging, or production data. Database-reset helpers can delete existing tables and records.

Verify the effective `DATABASE_URL` and test database configuration before running tests if you change your Docker Compose or Symfony environment settings.

## Test Layers

- `Unit/`: Entity behavior and Symfony Validator constraints.
- `Integration/`: Doctrine repositories, database constraints, and forms requiring the application container.
- `Functional/`: HTTP routes, authentication, authorization, CRUD workflows, CSRF protection, and console commands.
- `Support/`: Shared fixtures, database reset helpers, and reusable test utilities.

## Testing Dependencies

The suite relies on the project's Composer dependencies, including:

- `phpunit/phpunit`
- `symfony/browser-kit`
- `symfony/css-selector`
- `symfony/dom-crawler`

Install dependencies after pulling changes:

```powershell
docker compose exec app composer install
```

## Continuous Integration

The GitHub Actions workflow runs the test suite against an isolated MySQL service before deploying changes pushed to `main`.

The deployment job depends on the successful test job. Pull requests targeting `main` run the tests without deploying to production.

## Current Test Coverage

The suite currently contains 60 tests and 133 assertions. The tests cover entity validation, database behavior, dashboard access, skill and category management, authentication, CSRF validation, and user creation through the console.

Run the full suite after changing application code, database mappings, security rules, forms, or console commands.