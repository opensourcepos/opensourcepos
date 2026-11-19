## Server Requirements

- PHP version `8.2` (>= `8.2.35`), `8.3` (>= `8.3.36`), `8.4` (>= `8.4.27`), and `8.5` (>= `8.5.12`) are supported. PHP version `≤ 8.1` is NOT supported. Please note that PHP needs to have the extensions `php-json`, `php-gd`, `php-bcmath`, `php-intl`, `php-openssl`, `php-mbstring`, `php-curl` and `php-xml` installed and enabled. An unstable master build can be downloaded in the releases section.
- MySQL `5.7` is supported, also MariaDB replacement `10.x` is supported and might offer better performance.
- Apache `2.4` is supported. Nginx should work fine too, see [the Nginx (LEMP) guide](install/nginx.md).
- Raspberry PI based installations proved to work, see [the Raspberry Pi guide](install/raspberry-pi.md).
- For Windows based installations see [the XAMPP guide (local testing)](install/xampp.md) and [the IIS guide](install/iis.md). These are community guides and there are many closed issues about this subject, so check the issues first.

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
11. Oops, an issue? Please make sure you read the [README](../README.md) and the [FAQ](../README.md#-faq) and you checked open and closed issues on GitHub. PHP `display_errors` is disabled by default. Create an `app/Config/.env` file from the `.env.example` to enable it in a development environment.

## Local install using Docker

OSPOS can be deployed using Docker on Linux, Mac, and Windows. Locally or on a host (server).
This setup dramatically reduces the number of possible issues as all setup is now done in a Dockerfile.
Docker runs natively on Mac and Linux. Windows requires WSL2 to be installed. Please refer to the Docker documentation for instructions on how to set it up on your platform.

**Be aware that this setup is not suited for production usage! Change the default passwords in the compose file before exposing the containers publicly.**

> [!NOTE]
> All commands in this guide must be run from the **repository root** directory, not from `docs/`.

First create a **regular `.env` file** in the project root (a missing one is not
auto-created as a file — see the compose `create_host_path: false` setting). Copy
the shipped example **only if `.env` does not already exist**, so a re-run never
overwrites keys that `env:provision` has already written:

```
    [ -f .env ] || cp .env.example .env
```

Then start the containers:

```
    docker-compose up
```

### Throttle and encryption keys

OpenSourcePOS runs on two runtime secrets:

- `encryption.key` — the AES key that protects stored configuration secrets at rest (the `smtp_pass`, `mailchimp_api_key`, `mailchimp_list_id`, `clcdesq_api_key` and `clcdesq_api_url` values in the `ospos_app_config` table).
- `throttle.key` — an HMAC secret used to hash the login rate-limit cache keys.

Both are required for a working installation. You can either supply them yourself, or let the application generate and persist them into `.env` on first start.

#### Key precedence

The value in the `.env` file is always used first. `ENCRYPTION_KEY` and `THROTTLE_KEY` are only consulted as a **fallback** when the matching `.env` value (`encryption.key` / `throttle.key`) is empty. Do not set the pair to different values — the `.env` value wins.

#### Recommended — supply the keys via the environment

This is the most robust option: the application never has to write to `.env`, so it works on every platform (including the single-file bind mount in the bundled `docker-compose.yml`) and you control the value across container recreation.

1. For a **fresh install**, generate two strong, unique 256-bit keys (64 hex characters each) on the host:

   ```bash
   export THROTTLE_KEY="$(openssl rand -hex 32)"
   export ENCRYPTION_KEY="$(openssl rand -hex 32)"
   ```

   If you are **upgrading** an existing installation, do **not** generate a new `ENCRYPTION_KEY` — retain the key your data is already encrypted under, or those stored secrets become unreadable. Generate one only if you have no existing key to preserve.

2. Persist them so the same value is present every time the container is created (a host file, or a Compose-side `.env` file used for variable substitution).

3. Declare them on the service so the container actually receives them:

   ```yaml
   environment:
       - ENCRYPTION_KEY=${ENCRYPTION_KEY}
       - THROTTLE_KEY=${THROTTLE_KEY}
   ```

With both set, `env:provision` skips generating and writing both keys, so no `.env` rename is required — **except** when the effective `encryption.key` is a short CI3 key, whether it comes from `.env` or from `ENCRYPTION_KEY`. In that case `env:provision` rotates the key: it writes the new CI4 key to `.env`, and (when `.env` already contains an `encryption.key=` line) the old key as a commented line. That rotation still requires `.env` to be atomically replaceable (see the CI3 note below and the warning in the alternative option). Protect `.env`, and delete the commented old key once the upgrade is complete.

> [!NOTE]
> The container does **not** inherit your shell's environment variables. A `THROTTLE_KEY` or `ENCRYPTION_KEY` that exists only in your shell will not reach the app — it must be declared in the Compose `environment` list **or** a service `env_file`, not both. Compose `environment` overrides `env_file`, and an unresolved `${...}` entry becomes an empty string that would win over the file's value — so if you supply these keys via `env_file`, remove them from the `environment` list.

#### Alternative — let the application generate them into `.env`

The simplest path for a quick local start. The bundled `docker-compose.yml` bind-mounts a single host file `./.env` to `/app/.env`:

```yaml
volumes:
    - type: bind
      source: ./.env
      target: /app/.env
      bind:
          create_host_path: false
```

`create_host_path: false` means a missing host file fails startup instead of being auto-created as a directory (a directory would break the app's atomic `.env` write).

1. Create the host `.env` file from the example **only if it does not already exist** (re-running a plain copy would overwrite the keys `env:provision` has already written):

   ```bash
   [ -f .env ] || cp .env.example .env
   ```

2. Start the stack:

   ```bash
   docker-compose up
   ```

On start the service runs `php spark env:provision` before Apache, which generates both keys (32 random bytes each) and persists them into that `.env` file. Because the keys live in the host file, they survive container recreation and image upgrades.

> [!WARNING]
> `env:provision` persists keys by writing a temp file in the `.env`'s directory and then `rename()`-ing it over `.env`. On Linux, `rename()` **cannot replace a mount point**, and a single-file bind mount (like the one above) makes `.env` a mount point — so this write can fail, `env:provision` exits non-zero, and Apache never starts (the `&&` in the compose `command`). The throttle key is re-written on **every** start, not just the first, so the failure recurs. Use this option only when `.env` is a regular file in an atomically-replaceable location (a directory-backed mount or the container's own filesystem); otherwise use the recommended environment approach above.

#### Key-change and backup caveats

- **Keep the value stable across restarts.** Whatever store you use (the bind-mounted `.env`, a Compose-side `.env`, or an `env_file`), make sure the same value is present every time the container is created, or the throttle/encryption state will be reset.
- **Changing `throttle.key` resets active login rate limits.** The key is the HMAC input for the throttle cache keys, so a new key makes the existing throttle buckets unrecognisable — a currently-throttled login is effectively un-throttled.
- **Back up `encryption.key`.** It is a hard secret: any stored secret encrypted with it is unrecoverable if the key is lost. Keep an out-of-band backup and never commit it to the repository.
- **Do not put real keys in this file or in commits.** The `openssl rand -hex 32` placeholders above are for illustration only — always use a freshly generated value.

#### Upgrading from CI3

Do **not** supply a brand-new `ENCRYPTION_KEY` before upgrading a CI3 installation. The existing short `encryption.key` is required to decrypt the stored CI3 secrets; `env:provision` then rotates it to a new CI4 key in place and re-encrypts the data.

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

For other cloud providers or manual installation, follow the [Local install](#local-install) steps above with your provider's LAMP stack, or see the community guides in [`install/`](install/) (Nginx, Raspberry Pi, XAMPP, IIS).

**Important:** Change the default password after first login!
