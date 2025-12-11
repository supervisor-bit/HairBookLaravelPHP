#!/bin/bash

# ========================================
# HairBook Deployment Script
# ========================================
# Automatický deployment script pro produkční prostředí
# Verze: 1.0.0
# Datum: 11. prosince 2025
# ========================================

set -e  # Exit on error

# Barvy pro výstup
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Funkce pro výpis zpráv
info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Banner
echo -e "${GREEN}"
echo "========================================="
echo "  💇‍♀️ HairBook Deployment Script"
echo "========================================="
echo -e "${NC}"

# Kontrola, zda jsme v root složce projektu
if [ ! -f "artisan" ]; then
    error "Tento script musí být spuštěn z kořenové složky Laravel projektu!"
    exit 1
fi

info "Začínám deployment..."

# 1. Backup databáze
info "Vytváření zálohy databáze..."
if [ -f "database/database.sqlite" ]; then
    BACKUP_DIR="storage/app/backups"
    mkdir -p $BACKUP_DIR
    BACKUP_FILE="$BACKUP_DIR/backup_$(date +%Y-%m-%d_%H-%M-%S).sqlite"
    cp database/database.sqlite $BACKUP_FILE
    success "Záloha vytvořena: $BACKUP_FILE"
else
    warning "Databáze neexistuje, záloha přeskočena"
fi

# 2. Aktivace maintenance módu
info "Aktivuji maintenance mód..."
php artisan down --retry=60 || warning "Maintenance mód se nepodařilo aktivovat"

# 3. Git pull
info "Stahuji poslední změny z repozitáře..."
if git pull origin main; then
    success "Git pull úspěšný"
else
    error "Git pull selhal!"
    php artisan up
    exit 1
fi

# 4. Instalace závislostí
info "Instaluji Composer závislosti..."
if composer install --no-dev --optimize-autoloader --no-interaction; then
    success "Composer závislosti nainstalovány"
else
    error "Instalace Composer závislostí selhala!"
    php artisan up
    exit 1
fi

# 5. Migrace databáze
info "Spouštím databázové migrace..."
if php artisan migrate --force; then
    success "Migrace dokončeny"
else
    error "Migrace selhaly!"
    warning "Obnovuji databázi ze zálohy..."
    if [ -f "$BACKUP_FILE" ]; then
        cp $BACKUP_FILE database/database.sqlite
        success "Databáze obnovena ze zálohy"
    fi
    php artisan up
    exit 1
fi

# 6. Cache optimalizace
info "Optimalizuji cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
success "Cache optimalizována"

# 7. Oprávnění
info "Nastavuji oprávnění..."
chmod -R 775 storage bootstrap/cache
success "Oprávnění nastavena"

# 8. Deaktivace maintenance módu
info "Deaktivuji maintenance mód..."
php artisan up
success "Maintenance mód deaktivován"

# 9. Restart služeb (volitelné)
if command -v systemctl &> /dev/null; then
    info "Restartuji PHP-FPM..."
    if sudo systemctl restart php8.2-fpm 2>/dev/null; then
        success "PHP-FPM restartováno"
    else
        warning "PHP-FPM se nepodařilo restartovat (může být potřeba sudo)"
    fi
fi

# Výsledek
echo ""
echo -e "${GREEN}"
echo "========================================="
echo "  ✅ Deployment úspěšně dokončen!"
echo "========================================="
echo -e "${NC}"
echo ""
echo "Poslední změny:"
git log -1 --oneline
echo ""
echo "Pro kontrolu stavu spusťte:"
echo "  php artisan about"
echo ""

exit 0
