# LocalDevServer

A modern, high-performance, and modular local web development environment for macOS built with Docker and Docker Compose. 

Designed to behave like XAMPP, **LocalDevServer** allows developers to create, manage, and run multiple PHP and Laravel applications simultaneously from a single central `htdocs` directory without manually configuring Apache VirtualHosts every time.

---

## Table of Contents

1. [Features & Architecture](#features--architecture)
2. [Prerequisites & Docker Installation](#1-prerequisites--docker-installation)
3. [Installation & Setup](#2-installation--setup)
4. [Project Folder Structure](#3-folder-structure)
5. [Starting and Stopping the Server](#4-starting-and-stopping-the-server)
6. [Managing PHP Projects](#5-managing-php-projects)
7. [Managing Laravel Applications](#6-managing-laravel-applications)
8. [Local Domain Management](#7-local-domain-management)
9. [Database Management & DBeaver](#8-database-management--dbeaver)
10. [Multi-PHP Version Switching](#9-multi-php-version-switching)
11. [Backup & Restore Operations](#10-backup--restore-operations)
12. [Troubleshooting Common Issues](#11-troubleshooting-common-docker-errors)
13. [macOS Port Conflict Resolution](#12-macos-port-conflict-resolution)
14. [Command-Line Interface Reference](#cli-reference)

---

## Features & Architecture

- **Zero-Config VirtualHosts:** Automatically detects new folders in `htdocs/` and sets up `http://<folder>.local`.
- **Intelligent Laravel Detection:** Detects `public/` directory or `artisan` and sets DocumentRoot strictly to `public/`, protecting root `.env` and application files.
- **PHP 8.5+ (Latest):** Powered by the latest PHP 8.5 runtime with pre-compiled extensions (PDO MySQL, PostgreSQL, Redis, GD, Zip, Opcache) and Composer.
- **Database Diversity:** Out-of-the-box support for MySQL 8.x, MariaDB 11.x, and PostgreSQL 16+ with persistent storage and phpMyAdmin.
- **macOS Tailored:** Optimized for Apple Silicon (`arm64`) and Intel Macs, configured with host UID/GID permissions mapping to eliminate file permission problems.
- **Developer Toolkit:** Composer, Node.js 20, npm, and Redis are ready to use with convenient CLI helpers.
- **Interactive Dashboard:** Access `http://localhost` for a live overview of running services, databases, and detected projects.

---

## 1. Prerequisites & Docker Installation

### System Requirements
- **Operating System:** macOS Monterey (12.0) or later (Apple Silicon M1/M2/M3/M4 or Intel x86_64).
- **Docker Desktop for Mac:** [Download Docker Desktop](https://www.docker.com/products/docker-desktop/) (Version 4.25+ recommended).

### Recommended Docker Desktop Settings on macOS
1. Open Docker Desktop **Settings** (gear icon).
2. Under **General**, verify **Use Virtualization framework** is enabled.
3. Under **Resources > Advanced**:
   - Allocate at least **4 CPUs** and **4 GB to 8 GB RAM** for optimal performance.
4. Under **Resources > File Sharing**, ensure your project directory path (e.g. `/Users/<your-user>/Desktop/LocalDevServer`) is permitted.

---

## 2. Installation & Setup

1. **Clone or Download LocalDevServer:**
   ```bash
   cd ~/Desktop/LocalDevServer
   ```

2. **Verify Configuration:**
   Copy the example environment file if not already present:
   ```bash
   cp .env.example .env
   ```

3. **Check System Health:**
   Run the built-in diagnostic tool:
   ```bash
   ./localdev doctor
   ```

4. **Start the Environment:**
   ```bash
   ./localdev start
   ```

5. **Open the Dashboard:**
   Visit [http://localhost](http://localhost) in your web browser.

---

## 3. Folder Structure

```text
LocalDevServer/
│
├── docker-compose.yml       # Central Docker Compose orchestration
├── .env                     # Local environment settings & port bindings
├── .env.example             # Example configuration template
├── localdev                 # Unified CLI management tool
├── README.md                # Comprehensive documentation
├── .gitignore               # Excludes binary databases and logs from Git
│
├── docker/
│   ├── apache/              # Apache 2.4 configuration, Dockerfile & watcher
│   │   ├── Dockerfile
│   │   ├── httpd.conf
│   │   ├── dashboard/       # Built-in http://localhost status dashboard
│   │   ├── scripts/         # Auto-vhost generation and reload daemon
│   │   └── vhosts/          # Generated VirtualHost configurations
│   │
│   ├── php/                 # Multi-version PHP FastCGI Dockerfiles
│   │   ├── 8.2/             # PHP 8.2-FPM + Composer + Extensions
│   │   ├── 8.3/             # PHP 8.3-FPM (Default)
│   │   └── 8.4/             # PHP 8.4-FPM
│   │
│   ├── mysql/               # MySQL 8.x configuration (my.cnf)
│   ├── mariadb/             # MariaDB 11.x configuration (my.cnf)
│   ├── postgresql/          # PostgreSQL configuration and init scripts
│   ├── phpmyadmin/          # phpMyAdmin multi-server configuration
│   └── nginx/               # Optional Nginx alternative configuration
│
├── htdocs/                  # Central directory for all your PHP & Laravel projects
│   ├── school/              # Example standard PHP project -> http://school.local
│   ├── hotel/               # Example standard PHP project -> http://hotel.local
│   ├── laravel-school/      # Example Laravel app -> http://laravel-school.local
│   └── laravel-hotel/       # Example Laravel app -> http://laravel-hotel.local
│
├── databases/               # Persistent host storage (retained across container stops)
│   ├── mysql/
│   ├── mariadb/
│   ├── postgresql/
│   └── redis/
│
├── backups/                 # Database dumps (.sql / .sql.gz)
│
└── logs/                    # Runtime service logs (Apache, PHP, DBs)
    ├── apache/
    ├── php/
    ├── mysql/
    ├── mariadb/
    ├── postgresql/
    └── nginx/
```

---

## 4. Starting and Stopping the Server

The `./localdev` CLI controls all Docker services:

| Task | Command |
| :--- | :--- |
| **Start all services** | `./localdev start` |
| **Start specific service** | `./localdev start mysql` (or `php83`, `apache`, etc.) |
| **Stop all services** | `./localdev stop` |
| **Stop specific service** | `./localdev stop mariadb` |
| **Restart services** | `./localdev restart` |
| **View service status** | `./localdev status` |
| **View real-time logs** | `./localdev logs` (or `./localdev logs apache`) |
| **Rebuild containers** | `./localdev build` |

> [!NOTE]
> Persistent database volumes in `databases/` are **never** deleted when stopping containers.

---

## 5. Managing PHP Projects

### Creating a Standard PHP Project
To create a new standard PHP project:
```bash
./localdev create php myapp
```
To specify a PHP version (e.g. PHP 8.2):
```bash
./localdev create php myapp 8.2
```

This immediately:
1. Creates directory `htdocs/myapp/`.
2. Adds a starter `index.php` that verifies PHP settings and tests connections to MySQL, MariaDB, PostgreSQL, and Redis.
3. Generates the Apache VirtualHost routing `http://myapp.local` to `htdocs/myapp`.
4. Enables `.htaccess` and URL rewriting.

### XAMPP-Style URL Access (htdocs)

Just like in standard XAMPP, you can also access files and folders directly via `localhost`:

- **Browse all projects:** [http://localhost/htdocs/](http://localhost/htdocs/)
- **Run any PHP file:** `http://localhost/htdocs/<foldername>/<filename>.php` (e.g. [http://localhost/htdocs/school/index.php](http://localhost/htdocs/school/index.php))
- **Direct folder access:** `http://localhost/<foldername>/` (e.g. [http://localhost/school/](http://localhost/school/))
- **Laravel auto-routing:** Visiting `http://localhost/htdocs/laravel-school/` automatically forwards to `public/`!

### Manual Folder Creation (Zero-Config)
If you prefer creating projects manually:
```bash
mkdir htdocs/ecommerce
echo "<?php phpinfo();" > htdocs/ecommerce/index.php
```
The Apache watcher will detect the new directory, create the VirtualHost automatically within 2 seconds, and make it available at `http://ecommerce.local`.

---

## 6. Managing Laravel Applications

### Creating a Laravel Project
```bash
./localdev create laravel my-laravel-app
```
Or with a specific PHP version:
```bash
./localdev create laravel my-laravel-app 8.4
```

### Automatic DocumentRoot & Security
When LocalDevServer detects a Laravel structure (by checking for `public/` directory or `artisan`):
- Apache sets `DocumentRoot` strictly to `/var/www/html/my-laravel-app/public`.
- The parent directory, containing sensitive files such as `.env`, `composer.json`, `app/`, `storage/`, and `database/`, is completely inaccessible from the browser.
- Direct web requests to dotfiles (like `http://my-laravel-app.local/.env`) return **403 Forbidden**.
- URL rewriting is handled by `public/.htaccess`, routing all requests to `public/index.php`.

### Running Artisan and Composer
You do not need PHP or Composer installed on your Mac:
```bash
# Run Artisan commands:
./localdev artisan my-laravel-app migrate
./localdev artisan my-laravel-app make:controller ProductController
./localdev artisan my-laravel-app route:list

# Run Composer commands:
./localdev composer my-laravel-app install
./localdev composer my-laravel-app require guzzlehttp/guzzle

# Run frontend build tools via Node.js container:
./localdev npm my-laravel-app install
./localdev npm my-laravel-app run dev
```

---

## 7. Local Domain Management

LocalDevServer maps project names directly to local domains:
- Folder `htdocs/school` &rarr; `http://school.local`
- Folder `htdocs/laravel-hotel` &rarr; `http://laravel-hotel.local`

### Synchronizing `/etc/hosts`
To resolve `.local` domains on macOS:
```bash
./localdev hosts:sync
```
*(Enter your macOS password when prompted to authorize updating `/etc/hosts`).*

> [!TIP]
> **Instant Zero-Config Alternative (`*.localhost`):**
> If you see `DNS_PROBE_FINISHED_NXDOMAIN` in Chrome for `.local`, you can access your project immediately without any `/etc/hosts` setup using `.localhost`:
> - `http://school.localhost`
> - `http://hotel.localhost`
> - `http://laravel-school.localhost`
> - `http://laravel-hotel.localhost`
> 
> Under RFC 6761, modern browsers (Chrome, Safari, Firefox, Edge) automatically resolve any `*.localhost` domain to `127.0.0.1` out-of-the-box. LocalDevServer has both `.local` and `.localhost` pre-configured.

### How `/etc/hosts` Management Works
1. Scans all active folders inside `htdocs/`.
2. Reads or inserts a dedicated, isolated block inside `/etc/hosts`:
   ```text
   ### LocalDevServer BEGIN ###
   127.0.0.1 school.local www.school.local
   127.0.0.1 hotel.local www.hotel.local
   127.0.0.1 laravel-school.local www.laravel-school.local
   ### LocalDevServer END ###
   ```
3. Leaves all existing user configurations untouched and eliminates duplicate entries.
4. Flushes the macOS DNS cache automatically:
   ```bash
   sudo dscacheutil -flushcache
   sudo killall -HUP mDNSResponder
   ```

> [!TIP]
> You can customize the domain extension in `.env` (e.g. `DOMAIN_SUFFIX=test` if you prefer `school.test`).

---

## 8. Database Management & DBeaver

LocalDevServer includes three pre-configured database systems:

| Engine | Default External Port | Container Host | Default DB | User | Password |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **MySQL 8.x** | `3307` | `mysql` | `localdev_mysql` | `devuser` (or `root`) | `devpass` (or `rootpass`) |
| **MariaDB 11.x** | `3308` | `mariadb` | `localdev_mariadb` | `devuser` (or `root`) | `devpass` (or `rootpass`) |
| **PostgreSQL 16+** | `5433` | `postgresql` | `localdev_postgres` | `devuser` | `devpass` |
| **Redis 7.x** | `6379` | `redis` | `db 0` | *None* | *None* |

### Connecting via phpMyAdmin
Open [http://localhost:8081](http://localhost:8081).
- The login screen includes a **Server Choice** dropdown to easily switch between **MySQL 8.x** and **MariaDB**.
- Username: `root`, Password: `rootpass` (or `devuser` / `devpass`).

### Connecting via DBeaver
[DBeaver](https://dbeaver.io/) is the recommended free, universal GUI database client:

1. **Connect to MySQL 8.x:**
   - **Host:** `127.0.0.1`
   - **Port:** `3307`
   - **Database:** `localdev_mysql`
   - **Username:** `devuser` / **Password:** `devpass`
   - *(Optional: On the "Driver properties" tab, set `allowPublicKeyRetrieval=true` if using caching_sha2_password).*

2. **Connect to MariaDB:**
   - **Host:** `127.0.0.1`
   - **Port:** `3308`
   - **Database:** `localdev_mariadb`
   - **Username:** `devuser` / **Password:** `devpass`

3. **Connect to PostgreSQL:**
   - **Host:** `127.0.0.1`
   - **Port:** `5433`
   - **Database:** `localdev_postgres`
   - **Username:** `devuser` / **Password:** `devpass`

---

## 9. Multi-PHP Version Switching

LocalDevServer runs independent PHP-FPM containers for:
- **PHP 8.2** (`localdev_php82`)
- **PHP 8.3** (`localdev_php83`, system default)
- **PHP 8.4** (`localdev_php84`)

### Switching via CLI
```bash
./localdev php:switch myapp 8.2
```

### Switching via `.php-version` file
Inside any project folder, simply create or edit `.php-version`:
```bash
echo "8.4" > htdocs/myapp/.php-version
```
The Apache watcher instantly updates the project's upstream FastCGI proxy to `php84:9000` without downtime.

---

## 10. Backup & Restore Operations

### Backing Up Databases
Automated database dump scripts are stored in the `backups/` directory:
```bash
# Backup all databases (MySQL, MariaDB, and PostgreSQL)
./localdev backup

# Backup only MySQL
./localdev backup mysql

# Backup only PostgreSQL
./localdev backup postgres
```
Files are saved with timestamps: e.g. `backups/mysql_all_20261103_163000.sql.gz`.

### Restoring a Backup
```bash
./localdev restore backups/mysql_all_20261103_163000.sql.gz mysql
```

---

## 11. Troubleshooting Common Docker Errors

### Error: `permission denied while trying to connect to the Docker daemon`
- Ensure Docker Desktop is launched and running on macOS.
- Check the whale icon in the macOS menu bar.

### VirtualHost not loading / Domain not resolving
1. Run `./localdev vhost:sync` to regenerate Apache virtual hosts.
2. Run `./localdev hosts:sync` to refresh `/etc/hosts` and flush macOS DNS.
3. Test locally with curl:
   ```bash
   curl -H "Host: school.local" http://127.0.0.1
   ```

### Storage permissions in Laravel (`permission denied` on storage/logs)
Run:
```bash
chmod -R 777 htdocs/<your-laravel-project>/storage
chmod -R 777 htdocs/<your-laravel-project>/bootstrap/cache
```

---

## 12. macOS Port Conflict Resolution

On macOS, native services or prior installations (such as Homebrew MySQL, Postgres.app, or Apache) might occupy standard ports.

### Default Port Mappings in `.env`
LocalDevServer deliberately avoids default database ports to eliminate conflicts:
- Apache: `80` (or set `APACHE_PORT=8080`)
- MySQL: `3307` (instead of 3306)
- MariaDB: `3308` (instead of 3306)
- PostgreSQL: `5433` (instead of 5432)
- phpMyAdmin: `8081` (instead of 8080)
- Redis: `6379`

### Resolving Port 80 Conflicts
If macOS native Apache (`httpd`) is running:
```bash
# Check what is using port 80
sudo lsof -i :80

# Stop native macOS Apache
sudo apachectl stop
sudo launchctl unload -w /System/Library/LaunchDaemons/org.apache.httpd.plist 2>/dev/null || true
```
Alternatively, change `APACHE_PORT=8080` in `.env`.

---

## CLI Reference

```text
./localdev start [service]            Start all or a specific service
./localdev stop [service]             Stop all or a specific service
./localdev restart [service]          Restart all or a specific service
./localdev status                     Show container status and port mappings
./localdev logs [service]             Follow live container logs
./localdev build [service]            Rebuild docker container images
./localdev update                     Pull latest images and rebuild
./localdev doctor                     Run environment diagnostics & port audit

./localdev create php <name> [ver]    Create new PHP project (8.2, 8.3, 8.4)
./localdev create laravel <name> [ver] Create new Laravel project
./localdev list                       List all projects, domains, and PHP versions
./localdev remove <name>              Safely remove a project and domain
./localdev php:switch <prj> <ver>    Switch project PHP version (8.2 / 8.3 / 8.4)

./localdev hosts:sync                 Synchronize local domains with /etc/hosts
./localdev vhost:sync                 Regenerate and reload Apache VirtualHosts

./localdev artisan <prj> <cmd>        Run artisan commands inside PHP container
./localdev composer <prj> <cmd>       Run composer commands inside PHP container
./localdev npm <prj> <cmd>            Run npm commands inside Node.js container
./localdev shell [service]            Open interactive bash shell in container

./localdev backup [all|db]            Create compressed database backups
./localdev restore <file> [db]        Restore database dump
```

# LCS
