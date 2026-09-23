## Server Requirements

- PHP version `8.2` to `8.4` are supported, PHP version `≤ 8.1` is NOT supported. Please note that PHP needs to have the extensions `php-json`, `php-gd`, `php-bcmath`, `php-intl`, `php-openssl`, `php-mbstring`, `php-curl` and `php-xml` installed and enabled. An unstable master build can be downloaded in the releases section.
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

OSPOS is scaffolding a background job queue (Office → Job Queue) for long-running tasks such as large CSV imports. This is Phase 1: the scheduler infrastructure and trigger modes exist, but no real job types are wired up yet. Currently the scheduler only runs a `jobs_heartbeat` placeholder task that writes a debug log entry; the "Process All Jobs" / "Process Selected Jobs" endpoints on the Job Queue → Utilities tab are stubs that return `not_yet_implemented`. Actual job processing (e.g. CSV imports) will land in a later phase.

The three trigger modes below control how/when the scheduler runs, not what it processes yet:

- **Web** (default) — no setup required. The scheduler runs via a request hook after page loads, using `fastcgi_finish_request()` where available. Works out of the box on shared hosting, VPS, and Docker.
- **Auto** — a cron entry (Linux/Mac) or Task Scheduler task (Windows) triggers the scheduler on a fixed interval. Recommended for VPS/dedicated servers with cron access.
- **Manual** — `php spark tasks:run` can be invoked by hand to run the scheduler once. The "Process All Jobs" button on the Job Queue → Utilities tab is present but not yet functional (Phase 1 stub).

### `auto` mode: Linux/Mac cron

Add the following entry to your crontab (`crontab -e`), adjusting the path to your OSPOS install:

```
* * * * * cd /path/to/ospos && php spark tasks:run >> /dev/null 2>&1
```

### `auto` mode: Windows Task Scheduler

1. Open Task Scheduler and create a new task.
2. Trigger: `Daily`, check `Repeat task every: 5 minutes` (the fastest interval the GUI allows), `for a duration of: Indefinitely`, no expiration.
   - Don't use a `One time` trigger with a fixed repeat duration (e.g. `1 day`) — it stops repeating once that duration elapses instead of running forever.
3. Action: start a program.
   - Program/script: `php.exe` (full path, e.g. `C:\php\php.exe`)
   - Arguments: `spark tasks:run`
   - Start in: **must be the OSPOS project root — the directory containing the `spark` file** (e.g. `C:\laragon\www\opensourcepos`, or `C:\wamp64\www\opensourcepos\public\..`). This is NOT your PHP installation directory. If `Start in` is wrong or blank, `spark` fails immediately with `Could not open input file: spark` and the window closes before you can read it.
   - Alternatively, avoid relying on `Start in` altogether by giving the full path to `spark` directly in Arguments: `Arguments: C:\laragon\www\opensourcepos\spark tasks:run`.
4. On the **General** tab, select **"Run whether user is logged on or not"**. Without this, the task runs in your interactive session and briefly flashes a console window every time it fires.
   - You'll be prompted for your account password to save the task. If it's rejected (common with Microsoft accounts using Windows Hello — fingerprint/PIN/face login won't work here), check **"Do not store password"** instead. This limits the task to local computer resources only, which is sufficient for `spark tasks:run`.
5. Save the task. It will invoke the scheduler every 5 minutes — less frequent than the once-per-minute cron example above, so jobs are processed in 5-minute batches instead.
6. Before trusting the scheduled task, verify it manually: open `cmd.exe`, `cd` to the same `Start in` directory, and run the same `Program/script` + `Arguments`. You should see `Running Tasks...` then `Completed Running Tasks`, and a new heartbeat line in `writable/logs/`.

### `manual` mode

No setup is required. Set Mode to Manual on the Job Queue → Settings tab, then use the "Process All Jobs" button on the Utilities tab whenever you need jobs processed.

### Docker considerations

Docker containers are single-process by default, so cron does not run inside the OSPOS container out of the box — this is why **Web mode is the default** and works with zero extra Docker configuration.

If you want `auto` mode under Docker, pick one of the following:

**Option 1: separate worker container (recommended)**

Add a second service to your `docker-compose.yml` pointing at the same database:

```yaml
worker:
  image: opensourcepos
  command: php spark tasks:run
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

If you choose DigitalOcean:
[Through this link](https://m.do.co/c/ac38c262507b), you will get a [**free $100, 60-day credit**](https://m.do.co/c/ac38c262507b). [Check the wiki](https://github.com/opensourcepos/opensourcepos/wiki/Getting-Started-installations) for further instructions on how to install the necessary components.
