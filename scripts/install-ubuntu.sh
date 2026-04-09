#!/bin/bash

set -e

COLOR_RED='\033[0;31m'
COLOR_GREEN='\033[0;32m'
COLOR_YELLOW='\033[1;33m'
COLOR_BLUE='\033[0;34m'
COLOR_RESET='\033[0m'

echo -e "${COLOR_BLUE}╔══════════════════════════════════════════════════════════╗${COLOR_RESET}"
echo -e "${COLOR_BLUE}║     Open Source Point of Sale - Ubuntu Installer        ║${COLOR_RESET}"
echo -e "${COLOR_BLUE}║                    Version 3.4+                          ║${COLOR_RESET}"
echo -e "${COLOR_BLUE}╚══════════════════════════════════════════════════════════╝${COLOR_RESET}"
echo ""

if [ "$EUID" -ne 0 ]; then
    echo -e "${COLOR_RED}Please run this script as root or with sudo${COLOR_RESET}"
    exit 1
fi

export DEBIAN_FRONTEND=noninteractive

DB_HOST="${DB_HOST:-localhost}"
DB_NAME="${DB_NAME:-ospos}"
DB_USER="${DB_USER:-ospos}"
DB_PASS="${DB_PASS:-$(openssl rand -base64 24)}"
OSPOS_DIR="${OSPOS_DIR:-/var/www/ospos}"
OSPOS_VERSION="${OSPOS_VERSION:-}"
PHP_VERSION="${PHP_VERSION:-8.2}"
APACHE_SERVER_NAME="${APACHE_SERVER_NAME:-localhost}"
MYSQL_ROOT_PASS="${MYSQL_ROOT_PASS:-}"

echo -e "${COLOR_YELLOW}Configuration:${COLOR_RESET}"
echo -e "  Database Name: ${DB_NAME}"
echo -e "  Database User: ${DB_USER}"
echo -e "  Database Host: ${DB_HOST}"
echo -e "  Install Directory: ${OSPOS_DIR}"
echo -e "  PHP Version: ${PHP_VERSION}"
if [ -n "$OSPOS_VERSION" ]; then
    echo -e "  OSPOS Version: ${OSPOS_VERSION}"
else
    echo -e "  OSPOS Version: latest"
fi
echo ""

if [ -d "$OSPOS_DIR" ]; then
    echo -e "${COLOR_RED}Installation directory $OSPOS_DIR already exists${COLOR_RESET}"
    echo -e "${COLOR_YELLOW}Remove it or set OSPOS_DIR environment variable${COLOR_RESET}"
    exit 1
fi

echo -e "${COLOR_GREEN}[1/9] Updating system packages...${COLOR_RESET}"
apt-get update -qq

echo -e "${COLOR_GREEN}[2/9] Installing Apache, PHP, and dependencies...${COLOR_RESET}"
apt-get install -y -qq \
    apache2 \
    mariadb-server \
    mariadb-client \
    php${PHP_VERSION} \
    php${PHP_VERSION}-mysql \
    php${PHP_VERSION}-gd \
    php${PHP_VERSION}-bcmath \
    php${PHP_VERSION}-intl \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-zip \
    php${PHP_VERSION}-gd \
    git \
    curl \
    unzip \
    openssl

echo -e "${COLOR_GREEN}[3/9] Starting MariaDB...${COLOR_RESET}"
systemctl start mariadb
systemctl enable mariadb

if [ -z "$MYSQL_ROOT_PASS" ]; then
    echo -e "${COLOR_GREEN}[3/9] Securing MariaDB installation...${COLOR_RESET}"
    mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '';"
    mysql -e "FLUSH PRIVILEGES;"
else
    mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '${MYSQL_ROOT_PASS}';"
fi

echo -e "${COLOR_GREEN}[4/9] Creating database and user...${COLOR_RESET}"
mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'${DB_HOST}' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'${DB_HOST}';
FLUSH PRIVILEGES;
EOF

echo -e "${COLOR_GREEN}[5/9] Downloading OSPOS...${COLOR_RESET}"
mkdir -p /var/www
cd /var/www

if [ -z "$OSPOS_VERSION" ]; then
    OSPOS_VERSION=$(curl -sS https://api.github.com/repos/opensourcepos/opensourcepos/releases/latest | grep '"tag_name":' | sed -E 's/.*"v([^"]+)".*/\1/')
    if [ -z "$OSPOS_VERSION" ]; then
        echo -e "${COLOR_RED}Failed to get latest release version${COLOR_RESET}"
        exit 1
    fi
fi

echo -e "${COLOR_BLUE}Downloading OSPOS version ${OSPOS_VERSION}...${COLOR_RESET}"
curl -sSL "https://github.com/opensourcepos/opensourcepos/releases/download/v${OSPOS_VERSION}/opensourcepos-${OSPOS_VERSION}.zip" -o ospos.zip

if [ ! -f ospos.zip ] || [ ! -s ospos.zip ]; then
    echo -e "${COLOR_RED}Failed to download OSPOS release v${OSPOS_VERSION}${COLOR_RESET}"
    rm -f ospos.zip
    exit 1
fi

unzip -q ospos.zip -d ospos-temp
mv ospos-temp/opensourcepos-${OSPOS_VERSION} ospos
rm -rf ospos-temp ospos.zip

echo -e "${COLOR_GREEN}Downloaded OSPOS ${OSPOS_VERSION}${COLOR_RESET}"

echo -e "${COLOR_GREEN}[6/9] Setting up OSPOS...${COLOR_RESET}"
cd ${OSPOS_DIR}

curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer 2>/dev/null

if [ -f "composer.json" ]; then
    echo -e "${COLOR_BLUE}Installing dependencies...${COLOR_RESET}"
    composer install --no-dev --optimize-autoloader --no-interaction --quiet 2>/dev/null
fi

echo -e "${COLOR_GREEN}[7/9] Configuring OSPOS...${COLOR_RESET}"
if [ -f ".env.example" ]; then
    cp .env.example .env
    sed -i "s/database\.default\.hostname = localhost/database.default.hostname = ${DB_HOST}/" .env
    sed -i "s/database\.default\.database = ospos/database.default.database = ${DB_NAME}/" .env
    sed -i "s/database\.default\.username = admin/database.default.username = ${DB_USER}/" .env
    sed -i "s/database\.default\.password = pointofsale/database.default.password = ${DB_PASS}/" .env
    sed -i "s/CI_ENVIRONMENT = development/CI_ENVIRONMENT = production/" .env
fi

echo -e "${COLOR_GREEN}[8/9] Importing database schema...${COLOR_RESET}"
mysql -u root ${DB_NAME} < app/Database/database.sql

echo -e "${COLOR_GREEN}[9/9] Configuring Apache...${COLOR_RESET}"
cat > /etc/apache2/sites-available/ospos.conf <<EOF
<VirtualHost *:80>
    ServerName ${APACHE_SERVER_NAME}
    DocumentRoot ${OSPOS_DIR}/public

    <Directory ${OSPOS_DIR}/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/ospos_error.log
    CustomLog \${APACHE_LOG_DIR}/ospos_access.log combined
</VirtualHost>
EOF

a2enmod rewrite
a2dissite 000-default.conf
a2ensite ospos.conf

chown -R www-data:www-data ${OSPOS_DIR}
chmod -R 750 ${OSPOS_DIR}/writable

systemctl restart apache2
systemctl enable apache2

echo ""
echo -e "${COLOR_GREEN}╔══════════════════════════════════════════════════════════╗${COLOR_RESET}"
echo -e "${COLOR_GREEN}║            Installation Complete!                         ║${COLOR_RESET}"
echo -e "${COLOR_GREEN}╚══════════════════════════════════════════════════════════╝${COLOR_RESET}"
echo ""
echo -e "${COLOR_YELLOW}Database Credentials:${COLOR_RESET}"
echo -e "  Database: ${DB_NAME}"
echo -e "  Username: ${DB_USER}"
echo -e "  Password: ${DB_PASS}"
echo ""
echo -e "${COLOR_YELLOW}Login Credentials:${COLOR_RESET}"
echo -e "  URL:      http://${APACHE_SERVER_NAME}/"
echo -e "  Username: admin"
echo -e "  Password: pointofsale"
echo ""
echo -e "${COLOR_RED}IMPORTANT: Change the default password after first login!${COLOR_RESET}"
echo ""
echo -e "${COLOR_BLUE}Configuration file: ${OSPOS_DIR}/.env${COLOR_RESET}"
echo ""