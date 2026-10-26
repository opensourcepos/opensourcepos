## Server Requirements

- PHP version `8.2.35` to `8.4.27` are supported (minimum patch levels required for CVE-2026-109618 / CVE-2026-109619 security fixes). PHP version `≤ 8.1` is NOT supported. Please note that PHP needs to have the extensions `php-json`, `php-gd`, `php-bcmath`, `php-intl`, `php-openssl`, `php-mbstring`, `php-curl` and `php-xml` installed and enabled. An unstable master build can be downloaded in the releases section.
- MySQL `5.7` is supported, also MariaDB replacement `10.x` is supported and might offer better performance.
- Apache `2.4` is supported. Nginx should work fine too, see [wiki page here](https://github.com/opensourcepos/opensourcepos/wiki/Local-Deployment-using-LEMP).
- Raspberry PI based installations proved to work, see [wiki page here](<https://github.com/opensourcepos/opensourcepos/wiki/Installing-on-Raspberry-PI---Orange-PI-(Headless-OSPOS)>).
- For Windows based installations please read [the wiki](https://github.com/opensourcepos/opensourcepos/wiki). There are closed issues about this subject, as this topic has been covered a lot.

## Security Configuration

### Allowed Hostnames (REQUIRED for Production)

⚠️ **CRITICAL**: OpenSourcePOS validates the Host header to prevent Host Header Injection attacks. **You MUST configure `app.allowedHostnames` for production deployments. If not configured, the application will fail to start.**

**Add to your `.env` file:**

```bash
# Comma-separated list of allowed hostnames (no protocols or ports)
app.allowedHostnames = 'yourdomain.com,www.yourdomain.com'
```

**For local development:**

```bash
app.allowedHostnames = 'localhost'
```

**If you see this error at startup:**

```text
RuntimeException: Security: allowedHostnames is not configured.
```

**Solution**: Add `app.allowedHostnames` to your `.env` file with your domain(s).

**Why this matters:**
- Prevents Host Header Injection attacks
- Ensures URLs are generated with the correct domain
- Fixes issue #4480: .env configuration now works via comma-separated values

### HTTPS Behind Proxy

If your installation is behind a proxy with SSL offloading, set:
```
FORCE_HTTPS = true
```

## Local install

First of all, if you're seeing the message `system folder missing` after launching your browser, that most likely means you have cloned the repository and have not built the project. To build the project from a source commit point instead of from an official release check out [Building OSPOS](BUILD.md). Otherwise, continue with the following steps.

1. Download the a [pre-release for a specific branch](https://github.com/opensourcepos/opensourcepos/releases) or the latest stable [from GitHub here](https://github.com/opensourcepos/opensourcepos/releases). A repository clone will not work unless know how to build the project.
2. Create/locate a new MySQL database to install Open Source Point of Sale into.
3. Unzip and upload Open Source Point of Sale files to the web-server.
4. If `.env` does not exist, copy `.env.example` to `.env`.
5. Open `.env` and modify credentials to connect to your database if needed.
6. The database schema will be automatically created when you first access the application. Migrations run automatically on fresh installs.
7. Go to your install `public` dir via the browser.
8. Log in using
   - Username: admin
   - Password: pointofsale
9. If everything works, then set the `CI_ENVIRONMENT` variable to `production` in the .env file
10. Enjoy!
11. Oops, an issue? Please make sure you read the FAQ, wiki page, and you checked open and closed issues on GitHub. PHP `display_errors` is disabled by default. Create an` app/Config/.env` file from the `.env.example` to enable it in a development environment.

## Local install using Docker

OSPOS can be deployed using Docker on Linux, Mac, and Windows. Locally or on a host (server).
This setup dramatically reduces the number of possible issues as all setup is now done in a Dockerfile.
Docker runs natively on Mac and Linux. Windows requires WSL2 to be installed. Please refer to the Docker documentation for instructions on how to set it up on your platform.

**Be aware that this setup is not suited for production usage! Change the default passwords in the compose file before exposing the containers publicly.**

> [!NOTE]
> All commands in this guide must be run from the **repository root** directory, not from `docs/`.

First create a **regular `.env` file** in the project root (a missing one is not
auto-created as a file — see the compose `create_host_path: false` setting). Copy
the shipped example:

```
    cp .env.example .env
```

Then start the containers:

```
    docker-compose up
```

## Background Job Scheduling

OSPOS has a background job queue (Office → Job Queue) for long-running tasks such as large CSV imports, built on `codeigniter4/tasks` (scheduling) and `codeigniter4/queue` (job execution). Core queues are `default`, `imports`, and `api`. The "Process All Jobs" / "Process Selected Jobs" buttons on the Job Queue → Utilities tab drain these queues on demand.

The three trigger modes below control how/when queued jobs are processed:

- **Web** (default) — no setup required. Due tasks and any pending core-queue jobs are processed via a request hook after page loads, using `fastcgi_finish_request()` where available. Works out of the box on shared hosting, VPS, and Docker.
- **Auto** — a single cron entry (Linux/Mac) or Task Scheduler task (Windows) runs `spark tasks:run` on a fixed interval, which drains the core queues itself (see `jobs_queue_worker` in `app/Config/Tasks.php`) as well as running scheduled tasks. Recommended for VPS/dedicated servers with cron access.
- **Manual** — `php spark tasks:run` can be invoked by hand to run the scheduler once. The "Process All Jobs" / "Process Selected Jobs" buttons on the Job Queue → Utilities tab drain the core queues synchronously.

### `auto` mode: Linux/Mac cron

Add the following entry to your crontab (`crontab -e`), adjusting the path to your OSPOS install:

```
* * * * * cd /path/to/ospos && php spark tasks:run >> /dev/null 2>&1
```

### `auto` mode: Windows Task Scheduler

The Task Scheduler GUI's fastest repeat interval is 5 minutes. To match the once-per-minute cadence of the cron example above, use the `tools/Windows/create-windows-task-scheduler-task.ps1` script instead, which registers the task via `New-ScheduledTaskTrigger -RepetitionInterval`. It also sets up "Run whether user is logged on or not" + "Do not store password" and "Run task as soon as possible after a scheduled start is missed" automatically.

1. Open PowerShell **as Administrator** (`Register-ScheduledTask` requires elevation) and `cd` to your OSPOS project root.
2. Run the script, replacing `-PhpPath` and `-ProjectPath` with your actual PHP and install paths:
   ```powershell
   .\tools\Windows\create-windows-task-scheduler-task.ps1 -PhpPath 'C:\php\php.exe' -ProjectPath 'C:\laragon\www\opensourcepos'
   ```
   - `-PhpPath`: full path to `php.exe`.
   - `-ProjectPath`: the OSPOS project root — the directory containing the `spark` file.
   - Optionally pass `-TaskName` to override the default task name of `OSPOS Task Runner`.
3. Verify the task in Task Scheduler: it should show a 1-minute repeat interval, and the **General** tab should show "Run whether user is logged on or not" with "Do not store password" checked.
4. Before trusting the scheduled task, verify it manually: open `cmd.exe`, `cd` to the OSPOS project root, and run the same `php.exe spark tasks:run` command. You should see `Running Tasks...` then `Completed Running Tasks`, and a new heartbeat line in `writable/logs/`.

### `manual` mode

No setup is required. Set Mode to Manual on the Job Queue → Settings tab, then use the "Process All Jobs" / "Process Selected Jobs" buttons on the Utilities tab whenever you need jobs processed.

### Docker considerations

Docker containers are single-process by default, so cron does not run inside the OSPOS container out of the box — this is why **Web mode is the default** and works with zero extra Docker configuration.

If you want `auto` mode under Docker, pick one of the following:

**Option 1: separate worker container (recommended)**

Add a second service to your `docker-compose.yml` pointing at the same database:

```yaml
worker:
  image: opensourcepos
  command: sh -c "while true; do php spark tasks:run; sleep 60; done"
  depends_on:
    - db
  restart: unless-stopped
```

**Option 2: supervisor inside the container**

Add supervisor to the image and configure it to run both the web server and the scheduler. Works, but goes against the one-process-per-container convention — acceptable for simple single-container setups.

**Option 3: cron sidecar container**

Run a minimal sidecar container that fires `php spark tasks:run` every minute via cron, sharing the same network and database as the main container.

## Nginx install using Docker

Since OSPOS version `3.3.0` the Docker installation offers a reverse proxy based on Nginx with a Let's Encrypt TLS certificate termination (aka HTTPS connection).
Let's Encrypt is a free certificate issuer, requiring a special installation that this Docker installation would take care of for you.
Any Let's Encrypt TLS certificate renewal will be managed automatically, therefore there is no need to worry about those details.

Before starting your installation, you should edit the `docker/.env` file and configure it to contain the correct MySQL/MariaDB and phpMyAdmin passwords (don't use the defaults!).
You will also need to register to Let's Encrypt. Configure your host domain name and Let's Encrypt email address in the `docker/.env` file.
The variable `STAGING` needs to be set to `0` when you are confident your configuration is correct so that Let's Encrypt will issue a final proper TLS certificate.

Follow local install steps, but instead use

```
    docker/install-nginx.sh
```

Do **not** use below command on live deployments unless you want to tear everything down. All your disk content will be wiped!

```
    docker/uninstall.sh
```

## Cloud install

### Recommended: DigitalOcean

Sign up through [our referral link](https://m.do.co/c/ac38c262507b) to get a [**$100, 60-day credit**](https://m.do.co/c/ac38c262507b).

1. Create an Ubuntu 20.04+ or 22.04+ droplet
2. SSH into your server: `ssh root@<your-droplet-ip>`
3. Run the one-line installer:
   ```bash
   curl -sSL https://raw.githubusercontent.com/opensourcepos/opensourcepos/master/tools/scripts/install-ubuntu.sh | sudo bash
   ```

   > A shorter URL (`curl -sSL https://opensourcepos.org/install | sudo bash`) is also provided once the `opensourcepos.org/install` redirect is configured.

The installer will:
- Install Apache, MariaDB, PHP 8.2 and required extensions
- Download the **latest stable release** of OSPOS from GitHub
- Create a database with secure random password
- Configure OSPOS and Apache
- **Set up SSL/TLS certificates** (interactive prompt or environment variables)
- Display login credentials after completion

**Interactive Mode (Recommended for first-time users):**

When run without environment variables, the installer will prompt you:
1. Whether to configure SSL (recommended for production)
2. Your domain name (e.g., `pos.example.com`)
3. Your email for Let's Encrypt (for production SSL)

The prompts require a real terminal (standard input attached to a tty), so download
the script first and run it directly — do **not** pipe it, or the installer detects a
non-interactive session and skips the prompts:

```bash
# 1. Download the installer
curl -sSL https://raw.githubusercontent.com/opensourcepos/opensourcepos/master/tools/scripts/install-ubuntu.sh -o install-ospos.sh
# 2. Run it interactively (standard input stays attached to your terminal)
sudo bash install-ospos.sh
# Script will ask:
# - Configure SSL? (y/n)
# - Domain name: pos.example.com
# - Email for Let's Encrypt: admin@example.com
```

**Non-Interactive Mode (for automation):**

```bash
# Development (no SSL)
curl -sSL https://raw.githubusercontent.com/opensourcepos/opensourcepos/master/tools/scripts/install-ubuntu.sh | APACHE_SERVER_NAME=localhost sudo -E bash

# Production with Let's Encrypt SSL
curl -sSL https://raw.githubusercontent.com/opensourcepos/opensourcepos/master/tools/scripts/install-ubuntu.sh | APACHE_SERVER_NAME=pos.example.com SSL_EMAIL=admin@example.com sudo -E bash

# Custom database password
curl -sSL https://raw.githubusercontent.com/opensourcepos/opensourcepos/master/tools/scripts/install-ubuntu.sh | DB_PASS=securepassword APACHE_SERVER_NAME=pos.example.com SSL_EMAIL=admin@example.com sudo -E bash
```

**Environment variables:**
- `DB_HOST` - Database host (default: localhost)
- `DB_NAME` - Database name (default: ospos)
- `DB_USER` - Database user (default: ospos)
- `DB_PASS` - Database password (default: auto-generated)
- `MYSQL_ROOT_PASS` - MariaDB root password (default: empty/no password)
- `OSPOS_DIR` - Installation directory (default: /var/www/ospos)
- `OSPOS_VERSION` - OSPOS version to install (default: latest stable release)
- `PHP_VERSION` - PHP version (default: 8.2)
- `APACHE_SERVER_NAME` - Server hostname (default: localhost, or set interactively)
- `SSL_EMAIL` - Email for Let's Encrypt. Enables production SSL with auto-renewal **only when a public hostname is also provided** (via `SSL_DOMAIN` or a non-local `APACHE_SERVER_NAME`); `SSL_EMAIL` alone has no effect and the installer falls back to plain HTTP.
- `SSL_DOMAIN` - Domain for the SSL certificate. With `SSL_EMAIL` it uses Let's Encrypt; on its own it generates a self-signed certificate. Also serves as the installer's hostname when `APACHE_SERVER_NAME` is not set.

> **Testing:** This installer is tested with each commit via our CI workflow. A fresh Ubuntu container is spawned, the script runs to completion, and basic sanity checks verify the installation. For production deployments, we recommend testing on a staging server first. If you encounter issues, please [open an issue](https://github.com/opensourcepos/opensourcepos/issues/new?template=bug_report.yml) with your server version and error output.

For other cloud providers or manual installation, see the [detailed installation guide](https://github.com/opensourcepos/opensourcepos/wiki/Getting-Started-installations) in the wiki.

**Important:** Change the default password after first login!
