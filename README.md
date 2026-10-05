# Symfony - AZ Portfolio

A production-oriented Symfony portfolio application built to demonstrate modern **PHP/Symfony development, Docker-based infrastructure, RESTful architecture, database integration, Git/GitHub workflows, and CI/CD deployment**. 

This project is part of my personal developer portfolio and is designed to demonstrate the skills and engineering practices I use as a **Full Stack PHP Developer**.

## 🚀 Project Overview

**Symfony - AZ Portfolio** is the Symfony implementation of my AZ Portfolio platform.

The project is being developed as a modern PHP application using Symfony and is intended to demonstrate how I approach:

* Symfony application architecture
* Object-oriented PHP development
* MySQL database integration
* Doctrine ORM
* Environment-based configuration
* Docker development environments
* Linux/Apache production infrastructure
* Git and GitHub workflows
* GitHub Actions CI/CD
* Production deployment automation
* Application security and configuration management

The application is deployed to a production Linux server and is maintained through a Git-based development and deployment workflow.

## 🛠️ Technology Stack

| Technology         | Purpose                              |
| ------------------ | ------------------------------------ |
| **PHP 8.5**        | Application development              |
| **Symfony 8**      | PHP application framework            |
| **Doctrine ORM**   | Database abstraction and persistence |
| **MySQL 8.4**      | Relational database                  |
| **Docker**         | Local development infrastructure     |
| **Apache**         | Web server                           |
| **Linux / Ubuntu** | Production server                    |
| **Git**            | Version control                      |
| **GitHub**         | Source control and collaboration     |
| **GitHub Actions** | CI/CD automation                     |
| **Composer**       | PHP dependency management            |

## 🏗️ Architecture

The project follows a containerized development architecture.

```text
┌─────────────────────────────┐
│        Developer            │
│                             │
│  Windows + PowerShell       │
│  Git + VS Code              │
└──────────────┬──────────────┘
               │
               │ Git
               ▼
┌─────────────────────────────┐
│          GitHub             │
│                             │
│  adrianzalewski88           │
│  /symfony-az-portfolio      │
└──────────────┬──────────────┘
               │
               │ GitHub Actions
               ▼
┌─────────────────────────────┐
│       Production Server     │
│                             │
│        Ubuntu Linux         │
│           Apache            │
│             │               │
│             ▼               │
│        Symfony App          │
│             │               │
│             ▼               │
│          MySQL              │
└─────────────────────────────┘
```

### Local Development

The application uses Docker to provide a consistent development environment.

```text
Docker
├── Symfony / Apache / PHP
└── MySQL
```

This keeps application dependencies isolated from the host operating system while providing an environment that closely mirrors production.

## 📁 Project Structure

The application follows Symfony's conventional project structure.

```text
.
├── assets/
├── bin/
├── config/
├── migrations/
├── public/
├── src/
│   ├── Controller/
│   ├── Entity/
│   ├── Repository/
│   └── ...
├── templates/
├── tests/
├── var/
├── vendor/
├── .env
├── composer.json
├── compose.yaml
└── symfony.lock
```

> Generated dependencies and environment-specific files are intentionally excluded from version control.

## ⚙️ Local Development

### Requirements

The recommended development environment includes:

* Docker Desktop
* Git
* Composer
* PHP 8.5+
* Node.js / npm when frontend asset tooling is required

Docker is used to minimize the number of dependencies that need to be installed directly on the host machine.

### Clone the Repository

```bash
git clone https://github.com/adrianzalewski88/symfony-az-portfolio.git

cd symfony-az-portfolio
```

### Start the Application

Start the Docker environment:

```bash
docker compose up -d
```

Check the running containers:

```bash
docker compose ps
```

### Install PHP Dependencies

Inside the application container:

```bash
composer install
```

### Configure the Environment

Create the local environment configuration as needed:

```bash
cp .env .env.local
```

Update database and application configuration in `.env.local`.

Example database configuration:

```dotenv
DATABASE_URL="mysql://az_portfolio:az_portfolio_password@database:3306/az_portfolio?serverVersion=8.4&charset=utf8mb4"
```

### Run Database Migrations

```bash
php bin/console doctrine:migrations:migrate
```

### Clear Symfony Cache

```bash
php bin/console cache:clear
```

## 🗄️ Database

The application uses **MySQL 8.4** with **Doctrine ORM**.

Database schema changes are managed through Doctrine migrations.

Typical migration workflow:

```bash
php bin/console make:migration
```

Review the generated migration and apply it with:

```bash
php bin/console doctrine:migrations:migrate
```

This provides a version-controlled approach to database schema changes.

## 🔐 Configuration & Security

Environment-specific configuration is intentionally kept outside of source control.

Sensitive values such as:

* Database credentials
* Application secrets
* Production configuration
* Deployment credentials

should never be committed to the repository.

Symfony environment variables are used to separate application configuration from application code.

## 🐳 Docker

Docker is used to create a reproducible local development environment.

The primary goals are:

1. Isolate PHP/Symfony dependencies.
2. Provide a consistent MySQL environment.
3. Reduce host-machine configuration requirements.
4. Make local development closer to production.
5. Make the infrastructure easier to understand and reproduce.

Useful commands:

```bash
docker compose up -d
```

```bash
docker compose down
```

```bash
docker compose ps
```

```bash
docker compose logs -f
```

To rebuild the application container:

```bash
docker compose build --no-cache
```

## 🔄 Git & GitHub Workflow

Development follows a Git-based workflow.

Typical development cycle:

```text
Create Feature
     │
     ▼
Local Development
     │
     ▼
Test Application
     │
     ▼
Git Commit
     │
     ▼
Push to GitHub
     │
     ▼
Pull Request
     │
     ▼
GitHub Actions
     │
     ▼
Production Deployment
```

This project is intentionally maintained as a public GitHub repository to demonstrate real-world development practices to prospective employers.

## 🤖 CI/CD

GitHub Actions is used to automate application workflows.

The CI/CD pipeline is designed to provide automated validation and production deployment when changes are merged into the main branch.

The deployment workflow demonstrates experience with:

* GitHub Actions
* SSH-based deployment
* Linux server administration
* Apache
* PHP/Symfony deployment
* Production environment configuration
* Deployment users and permissions
* Automated application updates

The production server does **not** need to contain the complete local development toolchain. Build and deployment responsibilities are separated where appropriate.

## 🌐 Production

The Symfony application is deployed to a Linux production server running:

* Ubuntu
* Apache
* PHP 8.5
* Symfony
* MySQL

Production deployment is automated through GitHub Actions.

**Production URL:**

https://symfony.adrian-zalewski.com/

## 🎯 Portfolio Goals

This project is more than a portfolio website. It is intended to demonstrate practical experience with the technologies commonly expected from a modern senior PHP developer.

### Backend

* PHP
* Symfony
* Doctrine
* MySQL
* RESTful architecture
* Object-oriented programming
* Environment configuration
* Application security

### Infrastructure

* Docker
* Linux
* Apache
* MySQL
* SSH
* Server permissions
* Production deployment

### Development Workflow

* Git
* GitHub
* Pull Requests
* Branch-based development
* GitHub Actions
* CI/CD

## 🧭 Roadmap

Planned development includes:

* [ ] Complete Symfony portfolio application
* [ ] Implement portfolio project entities
* [ ] Implement project categories
* [ ] Implement project detail pages
* [ ] Improve database architecture
* [ ] Add comprehensive validation
* [ ] Expand automated testing
* [ ] Add API endpoints
* [ ] Improve CI validation
* [ ] Continue production deployment automation
* [ ] Integrate the application with the broader AZ Portfolio ecosystem

## 📚 Related AZ Portfolio Projects

The Symfony application is one part of a larger portfolio project demonstrating multiple modern PHP development approaches.

### React

**AZ Portfolio - React**

https://github.com/adrianzalewski88/react-az-portfolio

A React/Vite frontend demonstrating modern JavaScript development, API integration, routing, and frontend deployment.

### Laravel

**AZ Portfolio - Laravel**

https://github.com/adrianzalewski88/laravel-az-portfolio

A Laravel implementation demonstrating modern PHP development, REST APIs, authentication, OAuth2, database architecture, and API-driven frontend integration.

### Symfony

**AZ Portfolio - Symfony**

https://github.com/adrianzalewski88/symfony-az-portfolio

This repository.

## 👨‍💻 About

**Adrian Zalewski**

Senior Full Stack PHP Developer candidate specializing in:

* PHP
* Symfony
* Laravel
* Drupal
* WordPress
* MySQL
* JavaScript
* React
* Linux
* Apache
* Docker
* Git
* GitHub
* CI/CD

I build and maintain production websites and applications while continuing to expand my experience with modern application architecture, development workflows, APIs, and cloud/server infrastructure.

### Connect

* GitHub: https://github.com/adrianzalewski88
* LinkedIn: https://www.linkedin.com/in/adrian-zalewski-1988-fa/

---

**Built by Adrian Zalewski as part of the AZ Portfolio project.**
