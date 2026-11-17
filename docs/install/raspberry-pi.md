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

# Add your user to the docker group (optional)
sudo usermod -aG docker $USER
```

Then follow the [Docker install steps in INSTALL.md](../INSTALL.md#local-install-using-docker):

```bash
git clone https://github.com/opensourcepos/opensourcepos.git
cd opensourcepos
cp .env.example .env
# edit .env with your database credentials
docker-compose up
```

Access at `http://<your-pi-ip>:8080` with the default credentials
`admin` / `pointofsale`.

> **Note:** the default Docker setup is not suited for production. Change the
> default passwords before exposing it publicly.

## Standard LAMP installation

Raspberry Pi OS is based on Debian, so installation is similar to Ubuntu:

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install LAMP stack (PHP 8.2+ required)
sudo apt install apache2 mariadb-server php php-curl php-mysql php-gd \
    php-intl php-bcmath php-mbstring php-xml php-mysqli libapache2-mod-php

# Enable the rewrite module
sudo a2enmod rewrite
sudo systemctl restart apache2

# Set ownership
sudo chown -R www-data:www-data /var/www/html
```

Download the latest release from
[GitHub Releases](https://github.com/opensourcepos/opensourcepos/releases) and
extract it to `/var/www/html/`, then follow the remaining steps in
[INSTALL.md](../INSTALL.md).

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
