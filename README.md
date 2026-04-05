<div align="center">

# Event Booking & Ticketing Platform

**Browse events, reserve seats, and manage bookings from a single PHP application.**

[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=flat&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Docker](https://img.shields.io/badge/Docker-ready-2496ED?style=flat&logo=docker&logoColor=white)](https://www.docker.com/)

</div>

---

## Overview

This project is a full-stack **event ticketing** web application built with **PHP** and **MySQL**. It supports public event discovery, seat-based booking, ticket customization and verification, user profiles, contact messaging, and a dedicated **admin** area for operations. Optional integrations cover **transactional email** (SendGrid or SMTP), **media uploads**, and an **AI-assisted chatbot** for visitor support.

---

## Table of contents

- [Features](#features)
- [Tech stack](#tech-stack)
- [Repository structure](#repository-structure)
- [Prerequisites](#prerequisites)
- [Getting started](#getting-started)
- [Configuration](#configuration)
- [Local development](#local-development)
- [Testing](#testing)
- [Deployment](#deployment)
- [Security](#security)
- [Contributing](#contributing)
- [License](#license)

---

## Features

| Area | Capabilities |
|------|----------------|
| **Public site** | Homepage, event listings by category, booking flow, checkout, ticket customization, QR-friendly ticket views, FAQ and contact |
| **Accounts** | Authentication, profile management |
| **Admin** | Dashboard, events, venues, categories, bookings, users, tickets, messages, locations |
| **APIs** | REST-style endpoints under `public/api/` for events, bookings, venues, uploads, and related resources |
| **Optional** | SendGrid or PHPMailer email, Cloudinary-friendly deployment paths, OpenRouter-based chatbot (`config/ai_config.php`) |

---

## Tech stack

| Layer | Technology |
|--------|------------|
| Runtime | PHP 8.1+ (Dockerfile targets **PHP 8.3** CLI) |
| Data | MySQL / MariaDB via **PDO** |
| Dependencies | **Composer** ([`composer.json`](composer.json)) — e.g. PHPMailer |
| Quality | **PHPUnit 11** ([`phpunit.xml`](phpunit.xml)) |
| Containers | **Docker** + [**Railway**](https://railway.app/) ([`Dockerfile`](Dockerfile), [`railway.json`](railway.json)) |

---

## Repository structure

```
event-booking-website/
├── app/                 # Controllers, models, services, views (including admin)
├── config/              # Database, email, AI, error handling
├── database/            # Session helpers, SQL migrations
├── public/              # Web root: index, APIs, assets, uploads
│   └── router.php       # Front controller for PHP built-in server
├── tests/               # PHPUnit tests
├── Dockerfile
├── composer.json
└── phpunit.xml
```

---

## Prerequisites

- **PHP** 8.1 or newer (extensions as referenced in the [`Dockerfile`](Dockerfile): `pdo_mysql`, `gd`, `mbstring`, `zip`, `curl`, and related)
- **MySQL** 5.7+ or **MariaDB**
- [**Composer**](https://getcomposer.org/) for dependency installation and running tests

---

## Getting started

### 1. Clone the repository

```bash
git clone https://github.com/<your-username>/<your-repo>.git
cd <your-repo>
```

Replace the URL with your fork or upstream remote.

### 2. Create the database

Create an empty schema (default name used in code: **`event_ticketing_db`**). Collation `utf8mb4` is recommended.

### 3. Run SQL migrations

Apply the scripts in [`database/migrations/`](database/migrations/) in an order consistent with your baseline schema (dependencies first). Adjust if you are migrating an existing database.

### 4. Install dependencies

```bash
composer install
```

### 5. Point the web server at `public/`

- **Apache (e.g. XAMPP):** set the virtual host or application **document root** to the `public` directory, **or** browse to  
  `http://localhost/<project-folder>/public/`
- Ensure requests for APIs and static files under `public/` are not blocked by rewrite rules.

---

## Configuration

| Concern | Location / variables |
|---------|----------------------|
| **Database** | [`config/db_connect.php`](config/db_connect.php) — `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_PORT`; or Railway-style `MYSQLHOST`, `MYSQLDATABASE`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLPORT` |
| **Email** | [`config/email_config.php`](config/email_config.php) — provider selection; for SendGrid set `SENDGRID_API_KEY` and optionally `FROM_EMAIL` in the environment |
| **AI chatbot** | [`config/ai_config.php`](config/ai_config.php) — keep API keys out of version control in production; use secrets or environment injection |
| **Uploads** | Writable directories under `public/uploads/` (see [`Dockerfile`](Dockerfile) for expected layout) |

---

## Local development

### PHP built-in server (with clean URLs)

From the **repository root**:

```bash
php -S localhost:8080 -t public public/router.php
```

Open [http://localhost:8080/](http://localhost:8080/). The router dispatches to views in `app/views/`, while `public/api/` and static assets are served from `public/`.

---

## Testing

```bash
vendor/bin/phpunit
```

Test suites are defined in [`phpunit.xml`](phpunit.xml) (e.g. `tests/Unit`). Run after `composer install` so dev dependencies are present.

---

## Deployment

- **Docker:** the image installs Composer dependencies, prepares upload paths, and starts PHP’s built-in server bound to **`PORT`** (default `8080` in the image).
- **Railway:** build and deploy settings are in [`railway.json`](railway.json); provision MySQL and set the same environment variables as in production.

---

## Security

- Never commit **API keys**, **SMTP passwords**, or **production database credentials**.
- Prefer **environment variables** or your host’s **secret store** for sensitive configuration.
- Restrict direct access to `config/` and non-public paths at the web server level where applicable.

---

## Contributing

1. Fork the repository and create a feature branch.
2. Make focused changes with clear commit messages.
3. Run **`vendor/bin/phpunit`** before opening a pull request.
4. Describe the change and any configuration or migration steps in the PR.

---

## License

This project is licensed under the [MIT License](LICENSE).
