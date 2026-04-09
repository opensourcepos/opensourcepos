## Server Requirements

- PHP version `8.1` to `8.4` are supported, PHP version `≤7.4` is NOT supported. Please note that PHP needs to have the extensions `php-json`, `php-gd`, `php-bcmath`, `php-intl`, `php-openssl`, `php-mbstring`, `php-curl` and `php-xml` installed and enabled. An unstable master build can be downloaded in the releases section.
- MySQL `5.7` is supported, also MariaDB replacement `10.x` is supported and might offer better performance.
- Apache `2.4` is supported. Nginx should work fine too, see [wiki page here](https://github.com/opensourcepos/opensourcepos/wiki/Local-Deployment-using-LEMP).
- Raspberry PI based installations proved to work, see [wiki page here](<https://github.com/opensourcepos/opensourcepos/wiki/Installing-on-Raspberry-PI---Orange-PI-(Headless-OSPOS)>).
- For Windows based installations please read [the wiki](https://github.com/opensourcepos/opensourcepos/wiki). There are closed issues about this subject, as this topic has been covered a lot.

## Security Configuration

### Allowed Hostnames (REQUIRED for Production)

⚠️ **CRITICAL**: OpenSourcePOS validates the Host header to prevent Host Header Injection attacks (GHSA-jchf-7hr6-h4f3). **You MUST configure `app.allowedHostnames` for production deployments. If not configured, the application will fail to start.**

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
- Prevents Host Header Injection attacks (GHSA-jchf-7hr6-h4f3)
- Ensures URLs are generated with the correct domain
- Security advisory: https://github.com/opensourcepos/opensourcepos/security/advisories/GHSA-jchf-7hr6-h4f3
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

Start the containers using the following command

```
    docker-compose up
```

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
