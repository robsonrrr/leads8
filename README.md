[![Koseven Logo](https://i.imgur.com/2CeT8JL.png)# Leads6

This is the `leads6` application, a CRM for managing leads.

## Migration from leads5

This project is a migration of the legacy `leads5` application. The key changes are:

-   **PHP Version**: Upgraded from PHP 7 to PHP 8.1+.
-   **Framework**: Migrated from the defunct Kohana framework to its community-driven successor, [Koseven](https://koseven.ga/).
-   **Dependencies**: All dependencies have been updated to be compatible with PHP 8 via Composer.

## Requirements

-   PHP 8.1 or higher
-   Composer
-   Docker (for deployment)

## Installation

1.  Clone the repository.
2.  Install dependencies using Composer:
    ```bash
    composer install
    ```
3.  Configure your web server to point to the `public` directory.
4.  Ensure the environment configuration file is available at `/data/config.env`.

## Key Dependencies

-   [koseven/koseven](https://github.com/koseven/koseven): The core framework.
-   [mustache/mustache](https://github.com/bobthecow/mustache.php): Logic-less templates.
-   [phpoffice/phpspreadsheet](https://github.com/PHPOffice/PhpSpreadsheet): Library for reading and writing spreadsheet files.
-   [josegonzalez/dotenv](https://github.com/josegonzalez/php-dotenv): Loads environment variables from `.env` to `getenv()`, `$_ENV` and `$_SERVER`.

## Deployment

This application is deployed as a Docker container within a Docker Swarm cluster, managed by a shell script. See `Dockerfile` and the deployment script for more details.
# leads-php8
