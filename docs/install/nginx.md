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

Install PHP-FPM with the required extensions (use the version installed by
your distribution or the [ondrej/php PPA](https://launchpad.net/~ondrej/+archive/ubuntu/php)
for the latest supported versions):

```bash
sudo apt install -y php-fpm php-cli php-mbstring php-xml php-mysql php-gd \
    php-intl php-bcmath php-curl
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
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;   # adjust for your PHP version
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

If you instead keep the app inside a shared web root (e.g.
`/var/www/html/opensourcepos`), set `root /var/www/html/opensourcepos/public;`
and access the app at `http://localhost/opensourcepos`.

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
