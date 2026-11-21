# Nginx (LEMP) Installation

Community guide — not officially supported, see [INSTALL.md](../INSTALL.md)
for the supported setups. Covers a manual Nginx + MariaDB + PHP-FPM stack on
Ubuntu 22.04/24.04. For the Docker-based Nginx setup with TLS, see the
[Nginx install using Docker section of INSTALL.md](../INSTALL.md#nginx-install-using-docker).

> **Note:** PHP 8.2+ is required. See [INSTALL.md](../INSTALL.md#server-requirements)
> for the full list of supported PHP versions and required extensions.

## 1. Install the stack

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx mariadb-server
```

Install PHP-FPM with the required extensions. Ubuntu 22.04 ships PHP 8.1,
which is **not supported** — use the [ondrej/php PPA](https://launchpad.net/~ondrej/+archive/ubuntu/php)
to get a supported version (Ubuntu 24.04 already ships PHP 8.3):

```bash
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mbstring php8.3-xml \
    php8.3-mysql php8.3-gd php8.3-intl php8.3-bcmath php8.3-curl
```

Enable and start the services:

```bash
sudo systemctl enable --now nginx
sudo systemctl enable --now mariadb
sudo mysql_secure_installation
```

## 2. Configure Nginx

Remove the default site and create a server block for OSPOS. The example below
serves the app from the web root, so you access it at `http://localhost/`:

```bash
sudo rm /etc/nginx/sites-enabled/default
sudo nano /etc/nginx/sites-available/ospos
```

```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/ospos/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;   # adjust for your PHP version
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

This configuration serves the application at the virtual host root
(`http://localhost/`); set `server_name` to your domain for production use.

```bash
sudo ln -s /etc/nginx/sites-available/ospos /etc/nginx/sites-enabled/ospos
sudo nginx -t && sudo systemctl reload nginx
```

## 3. Install OSPOS

```bash
sudo mkdir -p /var/www/ospos
# download the latest release from GitHub and extract it into /var/www/ospos
sudo chown -R www-data:www-data /var/www/ospos
```

Then follow the remaining steps in [INSTALL.md](../INSTALL.md)
(`.env` configuration, log in, set `CI_ENVIRONMENT = production`).

> **Note:** for current releases the database is created automatically on
> first run. You do not need to import a schema file manually.

## Optional: Adminer

For a lightweight database admin tool (phpMyAdmin alternative), download
[Adminer](https://www.adminer.org/) and place the single `.php` file in your
web root, then browse to it.
