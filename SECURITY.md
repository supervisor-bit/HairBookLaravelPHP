# 🔐 Bezpečnostní průvodce HairBook

Doporučení a best practices pro zabezpečení HairBook aplikace v produkčním prostředí.

## 📋 Bezpečnostní checklist

### Před nasazením do produkce

- [ ] **APP_ENV=production** v `.env` souboru
- [ ] **APP_DEBUG=false** - nikdy nezobrazujte debug info v produkci
- [ ] **APP_KEY** - vygenerovaný silný klíč
- [ ] **HTTPS** - SSL certifikát nainstalován a aktivní
- [ ] **Firewall** - správně nakonfigurován
- [ ] **.env soubor** - není veřejně přístupný
- [ ] **Oprávnění** - správně nastavená na souborech a složkách
- [ ] **Composer** - závislosti aktuální: `composer update`
- [ ] **PHP verze** - aktuální s bezpečnostními záplatami
- [ ] **Backup strategie** - pravidelné automatické zálohy

---

## 🔒 Konfigurace .env pro produkci

```env
# Základní nastavení
APP_NAME=HairBook
APP_ENV=production
APP_KEY=base64:VYGENEROVANÝ_KLÍČ
APP_DEBUG=false
APP_URL=https://vasedomena.cz

# Databáze
DB_CONNECTION=sqlite

# Session a cache
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database

# Logy
LOG_CHANNEL=daily
LOG_LEVEL=warning
```

---

## 🛡️ Zabezpečení serveru

### 1. Firewall (UFW - Ubuntu)

```bash
# Povolení pouze potřebných portů
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'  # nebo 'Apache Full'
sudo ufw enable

# Kontrola stavu
sudo ufw status
```

### 2. Fail2Ban - ochrana proti brute-force

```bash
# Instalace
sudo apt install fail2ban

# Konfigurace pro Nginx
sudo nano /etc/fail2ban/jail.local
```

Vložte:
```ini
[nginx-http-auth]
enabled = true
port = http,https
logpath = /var/log/nginx/error.log

[nginx-noscript]
enabled = true
port = http,https
logpath = /var/log/nginx/access.log
maxretry = 6
```

```bash
sudo systemctl restart fail2ban
```

### 3. SSH zabezpečení

```bash
sudo nano /etc/ssh/sshd_config
```

Doporučené nastavení:
```
PermitRootLogin no
PasswordAuthentication no
PubkeyAuthentication yes
Port 2222  # Změňte výchozí port
MaxAuthTries 3
```

```bash
sudo systemctl restart ssh
```

### 4. Automatické aktualizace (Ubuntu)

```bash
sudo apt install unattended-upgrades
sudo dpkg-reconfigure --priority=low unattended-upgrades
```

---

## 🔐 Zabezpečení Laravel aplikace

### 1. Silné APP_KEY

```bash
php artisan key:generate
```

Nikdy nesdílejte tento klíč! Každé prostředí by mělo mít vlastní.

### 2. Ochrana .env souboru

Ujistěte se, že `.env` není veřejně přístupný:

**Nginx:**
```nginx
location ~ /\.env {
    deny all;
}
```

**Apache (.htaccess v root):**
```apache
<Files .env>
    Order allow,deny
    Deny from all
</Files>
```

### 3. Oprávnění souborů

```bash
# Vlastník: www-data (nebo váš web server uživatel)
sudo chown -R www-data:www-data /cesta/k/aplikaci

# Složky: 755, Soubory: 644
find /cesta/k/aplikaci -type f -exec chmod 644 {} \;
find /cesta/k/aplikaci -type d -exec chmod 755 {} \;

# Storage a cache: 775
chmod -R 775 storage bootstrap/cache
```

### 4. Skrytí Laravel verze

V `public/index.php`, odstraňte nebo zakomentujte:
```php
// header('X-Powered-By: Laravel');
```

V Nginx konfiguraci:
```nginx
fastcgi_hide_header X-Powered-By;
```

### 5. Rate limiting

Laravel má vestavěný rate limiting. Použijte v routách:

```php
Route::middleware(['throttle:60,1'])->group(function () {
    // Max 60 requestů za minutu
});
```

Pro přihlášení:
```php
Route::middleware(['throttle:5,1'])->group(function () {
    // Max 5 pokusů za minutu
});
```

---

## 🔍 Monitoring a logy

### 1. Nastavení logování

V `config/logging.php`:
```php
'daily' => [
    'driver' => 'daily',
    'path' => storage_path('logs/laravel.log'),
    'level' => env('LOG_LEVEL', 'warning'),
    'days' => 14,
],
```

### 2. Sledování chyb

**Sentry** (doporučeno pro produkci):
```bash
composer require sentry/sentry-laravel
```

### 3. Kontrola logů

```bash
tail -f storage/logs/laravel.log
```

### 4. Rotace logů

Vytvořte `/etc/logrotate.d/hairbook`:
```
/cesta/k/aplikaci/storage/logs/*.log {
    daily
    rotate 14
    compress
    delaycompress
    notifempty
    missingok
    su www-data www-data
}
```

---

## 🌐 SSL/HTTPS konfigurace

### Let's Encrypt (zdarma)

```bash
# Instalace Certbot
sudo apt install certbot python3-certbot-nginx

# Získání certifikátu
sudo certbot --nginx -d vasedomena.cz -d www.vasedomena.cz

# Automatické obnovení
sudo crontab -e
# Přidejte:
0 3 * * * certbot renew --quiet
```

### Nginx SSL konfigurace

```nginx
server {
    listen 443 ssl http2;
    server_name vasedomena.cz;

    ssl_certificate /etc/letsencrypt/live/vasedomena.cz/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/vasedomena.cz/privkey.pem;
    
    # SSL konfigurace
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    
    # HSTS
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    
    # Další security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    
    # ... zbytek konfigurace
}

# Redirect HTTP -> HTTPS
server {
    listen 80;
    server_name vasedomena.cz www.vasedomena.cz;
    return 301 https://$server_name$request_uri;
}
```

---

## 💾 Záloha a obnova

### Automatické zálohy databáze

Vytvořte backup script `backup.sh`:

```bash
#!/bin/bash

BACKUP_DIR="/home/hairbook/backups"
APP_DIR="/home/hairbook/HairBookLaravelPHP"
DATE=$(date +%Y-%m-%d_%H-%M-%S)

mkdir -p $BACKUP_DIR

# Backup databáze
cp $APP_DIR/database/database.sqlite $BACKUP_DIR/db_backup_$DATE.sqlite

# Smazání starších než 30 dní
find $BACKUP_DIR -name "db_backup_*.sqlite" -mtime +30 -delete

# Komprese
gzip $BACKUP_DIR/db_backup_$DATE.sqlite
```

Přidejte do crontab:
```bash
crontab -e
# Každý den ve 2:00
0 2 * * * /home/hairbook/backup.sh
```

### Off-site zálohy

**Použijte rclone pro zálohu do cloudu:**

```bash
# Instalace rclone
curl https://rclone.org/install.sh | sudo bash

# Konfigurace (např. Google Drive, Dropbox)
rclone config

# Synchronizace záloh
rclone sync /home/hairbook/backups remote:hairbook-backups
```

Přidejte do crontab:
```bash
# Každý den ve 3:00
0 3 * * * rclone sync /home/hairbook/backups remote:hairbook-backups
```

---

## 🚨 Incident response

### Co dělat při bezpečnostním incidentu

1. **Okamžitě aktivujte maintenance mód:**
   ```bash
   php artisan down
   ```

2. **Prověřte logy:**
   ```bash
   tail -100 storage/logs/laravel.log
   tail -100 /var/log/nginx/access.log
   tail -100 /var/log/nginx/error.log
   ```

3. **Obnovte ze zálohy** (pokud je databáze kompromitována)

4. **Změňte APP_KEY:**
   ```bash
   php artisan key:generate --force
   ```

5. **Změňte heslo aplikace** v administraci

6. **Aktualizujte závislosti:**
   ```bash
   composer update
   ```

7. **Zkontrolujte nahrané soubory** ve `storage/app/`

8. **Deaktivujte maintenance:**
   ```bash
   php artisan up
   ```

---

## 🔬 Bezpečnostní audit

### Pravidelné kontroly

```bash
# Kontrola závislostí na zranitelnosti
composer audit

# Kontrola PHP konfigurací
php -i | grep -E 'expose_php|display_errors|error_reporting'

# Kontrola oprávnění
find . -type f -perm 0777
find . -type d -perm 0777
```

### Security skenování

**OWASP ZAP** - automatické skenování:
```bash
docker run -t owasp/zap2docker-stable zap-baseline.py -t https://vasedomena.cz
```

---

## 📚 Další zdroje

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security](https://laravel.com/docs/security)
- [PHP Security Guide](https://phptherightway.com/#security)
- [Mozilla SSL Configuration Generator](https://ssl-config.mozilla.org/)

---

## ☎️ Kontakt při bezpečnostním problému

Pokud objevíte bezpečnostní zranitelnost, prosím nahlaste ji na:
- 📧 security@hairbook.cz
- 🐛 [GitHub Security Advisory](https://github.com/supervisor-bit/HairBookLaravelPHP/security/advisories/new)

**Prosím neveřejně nezveřejňujte zranitelnosti před jejich opravou.**

---

**Poslední aktualizace:** 11. prosince 2025  
**Verze:** 1.0.0
