# Raspberry Pi Installation

Community guide — not officially supported, see [INSTALL.md](../INSTALL.md) for
the supported setups.

This guide covers installing on a Raspberry Pi or similar ARM-based device
(Orange Pi, etc.).

## Quick Start with Docker (recommended)

Docker is the easiest way to run OSPOS on a Raspberry Pi since arm64/aarch64
images are available.

```bash
# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Add your user to the docker group (optional — log out and back in afterwards
# so the new group membership applies to your session)
sudo usermod -aG docker $USER
```

Then follow the [Docker install steps in INSTALL.md](../INSTALL.md#local-install-using-docker):

```bash
git clone https://github.com/opensourcepos/opensourcepos.git
cd opensourcepos
cp .env.example .env
# edit .env with your database credentials
docker compose up   # Compose plugin, installed by the Docker script above
```

Access at `http://<your-pi-ip>/` (the compose file publishes port 80) with the
default credentials `admin` / `pointofsale`.

> **Note:** the compose file sets `ALLOWED_HOSTNAMES=localhost`. If you access
> the app through the Pi's IP address, add that address to `ALLOWED_HOSTNAMES`
> in `docker-compose.yml`, otherwise generated links will point at `localhost`
> on your own machine.

> **Note:** the default Docker setup is not suited for production. Change the
> default passwords before exposing it publicly.

## Standard LAMP installation

Raspberry Pi OS is based on Debian, so installation is similar to Ubuntu.
Install the stack first:

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install LAMP stack (Raspberry Pi OS bookworm ships PHP 8.2, which is supported)
sudo apt install apache2 mariadb-server php php-curl php-mysql php-gd \
    php-intl php-bcmath php-mbstring php-xml php-mysqli libapache2-mod-php
```

Download the latest release from
[GitHub Releases](https://github.com/opensourcepos/opensourcepos/releases) and
extract it into its own directory **outside the default web root**, so that
`.env` and the rest of the application are not served by Apache:

```bash
sudo mkdir -p /var/www/ospos
# extract the release archive into /var/www/ospos
sudo chown -R www-data:www-data /var/www/ospos
```

Point a virtual host at the `public` directory only, and allow the
`.htaccess` front-controller rules for that directory (Debian's default
`AllowOverride None` would silently disable them):

```bash
sudo nano /etc/apache2/sites-available/ospos.conf
```

```apache
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /var/www/ospos/public

    <Directory /var/www/ospos/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
    </Directory>
</VirtualHost>
```

```bash
sudo a2enmod rewrite
sudo a2dissite 000-default.conf
sudo a2ensite ospos.conf
sudo systemctl restart apache2
```

Then follow the remaining steps in [INSTALL.md](../INSTALL.md)
(`.env` configuration — including `app.allowedHostnames`, log in,
`CI_ENVIRONMENT = production`). If you access the app through the Pi's IP
address, include that address in `app.allowedHostnames` as well.

> **Note:** for current releases the database is created automatically on
> first run. You do not need to import a schema file manually.

## Nginx instead of Apache

See the [Nginx (LEMP) guide](nginx.md).

## Performance tips

1. **Use a fast SD card** — Class 10 or better (or use an SSD).
2. **Increase the swap file** — helps with memory-intensive operations:
   ```bash
   sudo dphys-swapfile swapoff
   sudo nano /etc/dphys-swapfile   # set CONF_SWAPSIZE=2048
   sudo dphys-swapfile setup
   sudo dphys-swapfile swapon
   ```
3. **Disable unnecessary services** to free up resources.
4. **Prefer Docker** over a full LAMP stack where possible.

## Hardware recommendations

| Model | RAM | Notes |
|-------|-----|-------|
| Raspberry Pi 4 | 4 GB+ | Recommended for production use |
| Raspberry Pi 4 | 2 GB | Works well for light use |
| Raspberry Pi 3 B+ | 1 GB | Functional but slower |
| Raspberry Pi Zero W | 512 MB | Not recommended |
