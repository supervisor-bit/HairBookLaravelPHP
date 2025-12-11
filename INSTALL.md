# 📦 Instalační průvodce HairBook

Detailní návod pro instalaci a deployment HairBook systému na různých platformách.

## 📋 Obsah

1. [Požadavky](#požadavky)
2. [Lokální instalace](#lokální-instalace)
3. [Sdílený hosting](#sdílený-hosting)
4. [VPS / Dedikovaný server](#vps--dedikovaný-server)
5. [Docker](#docker)
6. [Časté problémy](#časté-problémy)

---

## Požadavky

### Minimální požadavky
- **PHP:** 8.2 nebo novější
- **Databáze:** SQLite 3
- **Web server:** Apache 2.4+ / Nginx 1.18+
- **Composer:** 2.0+
- **Disk:** min. 100 MB
- **RAM:** min. 256 MB

### PHP rozšíření
Ujistěte se, že máte nainstalovaná následující PHP rozšíření:

```bash
php -m | grep -E 'pdo_sqlite|mbstring|openssl|tokenizer|json|ctype|fileinfo|bcmath'
```

Potřebná rozšíření:
- PDO (pdo_sqlite)
- MBString
- OpenSSL
- Tokenizer
- JSON
- Ctype
- Fileinfo
- BCMath

---

## Lokální instalace

### Windows (XAMPP/WAMP/Laragon)

#### 1. Stáhněte XAMPP
```
https://www.apachefriends.org/download.html
```

#### 2. Naklonujte projekt
```bash
cd C:\xampp\htdocs
git clone https://github.com/supervisor-bit/HairBookLaravelPHP.git hairbook
cd hairbook
```

#### 3. Instalace závislostí
```bash
composer install --no-dev --optimize-autoloader
```

#### 4. Konfigurace
```bash
copy .env.example .env
php artisan key:generate
```

#### 5. Vytvoření databáze
```bash
type nul > database\database.sqlite
php artisan migrate --force
```

#### 6. Nastavte VirtualHost (volitelné)
Upravte `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    ServerName hairbook.local
    DocumentRoot "C:/xampp/htdocs/hairbook/public"
    
    <Directory "C:/xampp/htdocs/hairbook/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Přidejte do `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1 hairbook.local
```

#### 7. Spuštění
- Spusťte XAMPP Control Panel
- Zapněte Apache
- Navštivte: `http://localhost/hairbook/public` nebo `http://hairbook.local`

### macOS (MAMP)

#### 1. Stáhněte MAMP
```
https://www.mamp.info/en/downloads/
```

#### 2. Naklonujte projekt
```bash
cd /Applications/MAMP/htdocs
git clone https://github.com/supervisor-bit/HairBookLaravelPHP.git hairbook
cd hairbook
```

#### 3. Instalace a konfigurace
```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --force
```

#### 4. Spuštění
- Spusťte MAMP
- Nastavte document root na `/Applications/MAMP/htdocs/hairbook/public`
- Navštivte: `http://localhost:8888`

### Linux (Ubuntu/Debian)

#### 1. Instalace požadovaného software
```bash
sudo apt update
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mbstring \
    php8.2-xml php8.2-sqlite3 php8.2-curl php8.2-zip php8.2-bcmath \
    composer git sqlite3
```

#### 2. Naklonování a instalace
```bash
cd /var/www
sudo git clone https://github.com/supervisor-bit/HairBookLaravelPHP.git hairbook
cd hairbook
sudo composer install --no-dev --optimize-autoloader
sudo cp .env.example .env
sudo php artisan key:generate
```

#### 3. Databáze a oprávnění
```bash
sudo touch database/database.sqlite
sudo php artisan migrate --force
sudo chown -R www-data:www-data /var/www/hairbook
sudo chmod -R 775 storage bootstrap/cache
```

#### 4. Konfigurace Nginx
```bash
sudo nano /etc/nginx/sites-available/hairbook
```

Vložte:
```nginx
server {
    listen 80;
    server_name hairbook.local;
    root /var/www/hairbook/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### 5. Aktivace a restart
```bash
sudo ln -s /etc/nginx/sites-available/hairbook /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```

---

## Sdílený hosting

### Příklad: Wedos, Hostinger, Forpsi

#### 1. Příprava souborů lokálně
```bash
git clone https://github.com/supervisor-bit/HairBookLaravelPHP.git
cd HairBookLaravelPHP
composer install --no-dev --optimize-autoloader
```

#### 2. Konfigurace .env
Upravte `.env` soubor:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://vasedomena.cz

DB_CONNECTION=sqlite

SESSION_DRIVER=database
CACHE_STORE=database
```

#### 3. Generování klíče
```bash
php artisan key:generate
```

#### 4. Vytvoření databáze
```bash
touch database/database.sqlite
php artisan migrate --force
```

#### 5. Upload přes FTP
- Nahrajte všechny soubory do kořenové složky vašeho hostingu
- Ujistěte se, že složka `public/` je nastavena jako document root

#### 6. Nastavení document root
V administraci hostingu nastavte document root na:
```
/cesta/k/aplikaci/public
```

#### 7. Nastavení oprávnění
Pokud máte SSH přístup:
```bash
chmod -R 775 storage bootstrap/cache
```

#### 8. První přihlášení
Navštivte: `https://vasedomena.cz/auth/setup`

### .htaccess pro sdílený hosting

Vytvořte `.htaccess` v kořenové složce (pokud document root nemůžete změnit):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

---

## VPS / Dedikovaný server

### Ubuntu 22.04 LTS

#### 1. Připojení na server
```bash
ssh root@vase-ip
```

#### 2. Aktualizace systému
```bash
apt update && apt upgrade -y
```

#### 3. Instalace PHP a závislostí
```bash
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt update

apt install -y php8.2-fpm php8.2-cli php8.2-mbstring php8.2-xml \
    php8.2-sqlite3 php8.2-curl php8.2-zip php8.2-bcmath php8.2-intl \
    nginx git composer sqlite3 certbot python3-certbot-nginx
```

#### 4. Vytvoření uživatele
```bash
adduser hairbook
usermod -aG www-data hairbook
```

#### 5. Naklonování projektu
```bash
su - hairbook
cd /home/hairbook
git clone https://github.com/supervisor-bit/HairBookLaravelPHP.git
cd HairBookLaravelPHP
```

#### 6. Instalace závislostí
```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
nano .env  # Upravte APP_ENV=production, APP_DEBUG=false
```

#### 7. Nastavení aplikace
```bash
php artisan key:generate
touch database/database.sqlite
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 8. Oprávnění
```bash
sudo chown -R hairbook:www-data /home/hairbook/HairBookLaravelPHP
sudo chmod -R 775 storage bootstrap/cache
```

#### 9. Nginx konfigurace
```bash
sudo nano /etc/nginx/sites-available/hairbook
```

Vložte:
```nginx
server {
    listen 80;
    server_name vasedomena.cz www.vasedomena.cz;
    root /home/hairbook/HairBookLaravelPHP/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### 10. Aktivace a SSL
```bash
sudo ln -s /etc/nginx/sites-available/hairbook /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx

# SSL certifikát (Let's Encrypt)
sudo certbot --nginx -d vasedomena.cz -d www.vasedomena.cz
```

#### 11. Automatické obnovení SSL
```bash
sudo crontab -e
# Přidejte:
0 3 * * * certbot renew --quiet
```

#### 12. Firewall
```bash
ufw allow 'Nginx Full'
ufw allow OpenSSH
ufw enable
```

---

## Docker

### Dockerfile
Vytvořte `Dockerfile` v kořenové složce projektu:

```dockerfile
FROM php:8.2-fpm

# Instalace závislostí
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev

# PHP rozšíření
RUN docker-php-ext-install pdo_sqlite mbstring exif pcntl bcmath

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Pracovní adresář
WORKDIR /var/www

# Kopírování aplikace
COPY . /var/www

# Instalace závislostí
RUN composer install --no-dev --optimize-autoloader

# Oprávnění
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
```

### docker-compose.yml
```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: hairbook-app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - ./:/var/www
    networks:
      - hairbook-network

  nginx:
    image: nginx:alpine
    container_name: hairbook-nginx
    restart: unless-stopped
    ports:
      - "8000:80"
    volumes:
      - ./:/var/www
      - ./docker/nginx:/etc/nginx/conf.d
    networks:
      - hairbook-network

networks:
  hairbook-network:
    driver: bridge
```

### Nginx konfigurace pro Docker
Vytvořte `docker/nginx/default.conf`:

```nginx
server {
    listen 80;
    index index.php index.html;
    error_log  /var/log/nginx/error.log;
    access_log /var/log/nginx/access.log;
    root /var/www/public;

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
        gzip_static on;
    }
}
```

### Spuštění Docker
```bash
docker-compose up -d
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --force
```

---

## Časté problémy

### 1. Chyba: "No application encryption key has been specified"
```bash
php artisan key:generate
```

### 2. Chyba: "Permission denied" při zápisu do storage
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 3. Chyba 500 na production
- Zkontrolujte `.env`: `APP_DEBUG=false`
- Zkontrolujte logy: `storage/logs/laravel.log`
- Vymažte cache:
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### 4. SQLite database locked
```bash
# Zvyšte timeout v config/database.php
'timeout' => 15,
```

### 5. CSRF token mismatch
- Zkontrolujte `SESSION_DRIVER` v `.env`
- Ujistěte se, že složka `storage/framework/sessions` existuje a je zapisovatelná

### 6. Bílá stránka po instalaci
- Zkontrolujte PHP error log
- Ujistěte se, že všechna PHP rozšíření jsou nainstalovaná
- Zkontrolujte oprávnění složek

### 7. Composer install selhává
```bash
# Pokud je nedostatek paměti:
COMPOSER_MEMORY_LIMIT=-1 composer install
```

---

## 🔐 Bezpečnostní checklist pro production

- [ ] `APP_ENV=production` v `.env`
- [ ] `APP_DEBUG=false` v `.env`
- [ ] Silné heslo pro aplikaci
- [ ] SSL certifikát (HTTPS)
- [ ] Firewall nakonfigurován
- [ ] Pravidelné zálohy databáze
- [ ] `storage/` a `bootstrap/cache/` mají správná oprávnění
- [ ] `.env` soubor není veřejně přístupný
- [ ] Aktualizované závislosti: `composer update`
- [ ] PHP verze aktuální (security patches)

---

## 📞 Potřebujete pomoc?

- 🐛 [GitHub Issues](https://github.com/supervisor-bit/HairBookLaravelPHP/issues)
- 📚 [Laravel Dokumentace](https://laravel.com/docs)

---

**Verze dokumentace:** 1.0.0  
**Datum:** 11. prosince 2025
