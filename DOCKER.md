# 🐳 Docker Deployment Guide

Kompletní návod pro deployment HairBook pomocí Dockeru.

## 🚀 Rychlý start

### 1. Build a spuštění (lokálně na Macu)

```bash
# Zkopíruj .env
cp .env.example .env

# Vygeneruj APP_KEY
php artisan key:generate

# Build Docker image
docker-compose build

# Spusť kontejner
docker-compose up -d

# Migrace databáze
docker-compose exec app php artisan migrate --force

# První přihlášení
open http://localhost:8080/auth/setup
```

Hotovo! Aplikace běží na `http://localhost:8080`

---

## 📦 Co Docker obsahuje

```
HairBookPHP/
├── Dockerfile              # Definice image
├── docker-compose.yml      # Orchestrace kontejnerů
└── docker/
    ├── nginx.conf          # Nginx konfigurace
    └── supervisord.conf    # Process manager
```

**Image obsahuje:**
- PHP 8.2 FPM
- Nginx web server
- SQLite databáze
- Všechny PHP rozšíření
- Supervisor (process manager)

---

## 🔧 Příkazy

### Základní operace

```bash
# Spuštění
docker-compose up -d

# Zastavení
docker-compose down

# Restart
docker-compose restart

# Logy
docker-compose logs -f

# Vstup do kontejneru
docker-compose exec app sh
```

### Laravel příkazy v Dockeru

```bash
# Migrace
docker-compose exec app php artisan migrate

# Cache clear
docker-compose exec app php artisan cache:clear

# Optimalizace
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

# Tinker
docker-compose exec app php artisan tinker
```

### Záloha databáze

```bash
# Záloha
docker cp hairbook-app:/var/www/html/database/database.sqlite ./backup.sqlite

# Obnova
docker cp ./backup.sqlite hairbook-app:/var/www/html/database/database.sqlite
```

---

## 🌐 Deployment na server

### VPS (Digital Ocean, Linode, Hetzner)

#### 1. Příprava serveru

```bash
# Připoj se na server
ssh root@vase-ip

# Instalace Dockeru
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Instalace Docker Compose
apt install docker-compose -y

# Vytvoř uživatele
adduser hairbook
usermod -aG docker hairbook
su - hairbook
```

#### 2. Deploy aplikace

```bash
# Naklonuj projekt
git clone https://github.com/supervisor-bit/HairBookLaravelPHP.git
cd HairBookLaravelPHP

# Nastav .env pro produkci
cp .env.example .env
nano .env
```

Uprav `.env`:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://vasedomena.cz
```

```bash
# Vygeneruj APP_KEY
docker run --rm -v $(pwd):/app composer php artisan key:generate

# Build a spuštění
docker-compose up -d

# Migrace
docker-compose exec app php artisan migrate --force
```

#### 3. Nginx reverse proxy (pro HTTPS)

Na hostitelském serveru vytvoř `/etc/nginx/sites-available/hairbook`:

```nginx
server {
    listen 80;
    server_name vasedomena.cz;

    location / {
        proxy_pass http://localhost:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

```bash
ln -s /etc/nginx/sites-available/hairbook /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx

# SSL s Let's Encrypt
apt install certbot python3-certbot-nginx -y
certbot --nginx -d vasedomena.cz
```

---

## 📊 Docker Hub (veřejná distribuce)

### Publikování image

```bash
# Login do Docker Hub
docker login

# Tag image
docker tag hairbook-app supervisor-bit/hairbook:1.0.0
docker tag hairbook-app supervisor-bit/hairbook:latest

# Push
docker push supervisor-bit/hairbook:1.0.0
docker push supervisor-bit/hairbook:latest
```

### Použití publikovaného image

```yaml
# docker-compose.yml pro koncové uživatele
version: '3.8'
services:
  app:
    image: supervisor-bit/hairbook:latest
    ports:
      - "8080:80"
    volumes:
      - ./database:/var/www/html/database
      - ./storage:/var/www/html/storage
    environment:
      - APP_KEY=${APP_KEY}
```

```bash
# Stažení a spuštění
docker-compose up -d
```

---

## 🔐 Produkční bezpečnost

### 1. Změň výchozí port

V `docker-compose.yml`:
```yaml
ports:
  - "127.0.0.1:8080:80"  # Pouze localhost
```

### 2. Přidej health check

```yaml
services:
  app:
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost/"]
      interval: 30s
      timeout: 10s
      retries: 3
```

### 3. Limit resources

```yaml
services:
  app:
    deploy:
      resources:
        limits:
          cpus: '1'
          memory: 512M
        reservations:
          cpus: '0.5'
          memory: 256M
```

### 4. Secrets management

```bash
# Použij Docker secrets místo .env
echo "your-app-key" | docker secret create app_key -
```

---

## 🔄 Auto-update s Watchtower

```yaml
# Přidej do docker-compose.yml
services:
  watchtower:
    image: containrrr/watchtower
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock
    command: --interval 3600  # Kontrola každou hodinu
```

---

## 📈 Monitoring

### Portainer (Docker GUI)

```bash
docker run -d \
  -p 9000:9000 \
  --name portainer \
  --restart always \
  -v /var/run/docker.sock:/var/run/docker.sock \
  -v portainer_data:/data \
  portainer/portainer-ce
```

Přístup: `http://vase-ip:9000`

---

## 💾 Backup strategie

### Automatická záloha s cron

```bash
# backup.sh
#!/bin/bash
DATE=$(date +%Y-%m-%d_%H-%M-%S)
docker cp hairbook-app:/var/www/html/database/database.sqlite /backups/db_$DATE.sqlite
find /backups -name "db_*.sqlite" -mtime +30 -delete
```

```bash
# Crontab - každý den ve 2:00
0 2 * * * /home/hairbook/backup.sh
```

---

## 🐛 Troubleshooting

### Kontejner se nespustí
```bash
docker-compose logs app
```

### Permission denied
```bash
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
```

### Database locked
```bash
# Zastavení, smazání a restart
docker-compose down
rm database/database.sqlite
docker-compose up -d
docker-compose exec app php artisan migrate --force
```

---

## 📝 Přehled portů

| Port | Služba | Popis |
|------|--------|-------|
| 8080 | HairBook | Hlavní aplikace |
| 9000 | Portainer | Docker management |
| 80 | Nginx | Reverse proxy |
| 443 | Nginx | HTTPS |

---

## ✅ Výhody Docker deploymentu

- ✨ Izolované prostředí
- 🚀 Rychlý deployment
- 🔄 Snadné aktualizace
- 📦 Přenositelnost (funguje všude stejně)
- 🔒 Bezpečnější než klasický hosting
- 📊 Snadný monitoring
- 💾 Jednoduchá záloha (celý kontejner)

---

## 🎯 Use cases

### Lokální vývoj
```bash
docker-compose up
```

### Produkční server
```bash
docker-compose -f docker-compose.prod.yml up -d
```

### Multi-tenancy (více salonů)
```bash
# Spusť více instancí na různých portech
docker-compose -p salon1 up -d
docker-compose -p salon2 up -d
```

---

**Verze:** 1.0.0  
**Datum:** 11. prosince 2025
