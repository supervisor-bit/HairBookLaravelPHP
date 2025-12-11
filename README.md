# 💇‍♀️ HairBook - Salon Management System

Kompletní systém pro správu kadeřnického salonu s kalendářem, evidencí klientů, produktů, návštěv a financí.

![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=flat&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat&logo=php)
![SQLite](https://img.shields.io/badge/SQLite-3-003B57?style=flat&logo=sqlite)
![Alpine.js](https://img.shields.io/badge/Alpine.js-3.14-8BC0D0?style=flat&logo=alpine.js)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.0-06B6D4?style=flat&logo=tailwind-css)

## 🎯 Funkce

### 📅 Kalendář a rezervace
- **Denní zobrazení** - časový rozvrh 8:00-20:00
- **Týdenní přehled** - zobrazení celého týdne
- **Správa rezervací** - vytvoření, úprava, mazání
- **Opakované rezervace** - automatické vytvoření opakujících se termínů
- **Kontrola dostupnosti** - ověření volných termínů s alternativami
- **Auto-doplňování klientů** - rychlé vyplnění existujících klientů

### 👥 Správa klientů
- Evidence klientů s kontakty
- Historie všech návštěv
- Poznámky ke klientům
- Statistiky utracených částek
- Rychlé vyhledávání

### ✂️ Návštěvy a služby
- Vytváření návštěv s detaily služeb
- Evidování použitých produktů při službách
- Prodej produktů domů (retail)
- Uzavírání návštěv s účtenkami
- Šablony služeb pro rychlejší práci

### 📦 Správa skladu
- Evidence produktů po kusech nebo gramech
- Sledování minimálních stavů
- Automatické odpisy při službách
- Ruční úpravy skladových zásob
- Import produktů z CSV
- Skupiny produktů s barevným rozlišením

### 💰 Finance
- Přehled tržeb po obdobích (dnes, týden, měsíc, rok)
- Oddělení tržeb ze služeb a prodeje domů
- Měsíční statistiky s detaily
- Rozbalovací skupiny návštěv po měsících

### ⚙️ Nastavení
- Informace o salonu
- Záloha a obnova databáze
- Hromadný import produktů
- Ochrana heslem

## 🚀 Rychlá instalace

### Požadavky
- PHP 8.2 nebo novější
- Composer
- SQLite 3
- Node.js & NPM (volitelné, pro development)

### Instalace

```bash
# 1. Klonování repozitáře
git clone https://github.com/supervisor-bit/HairBookLaravelPHP.git
cd HairBookLaravelPHP

# 2. Instalace závislostí
composer install --no-dev --optimize-autoloader

# 3. Konfigurace prostředí
cp .env.example .env
php artisan key:generate

# 4. Vytvoření databáze
touch database/database.sqlite
php artisan migrate --force

# 5. Spuštění aplikace
php artisan serve
```

Aplikace bude dostupná na: `http://localhost:8000`

### První přihlášení

Při prvním spuštění si vytvoříte heslo na: `http://localhost:8000/auth/setup`

## 📖 Detailní dokumentace

Kompletní instalační průvodce najdete v souboru [INSTALL.md](INSTALL.md).

## 🏗️ Technologie

### Backend
- **Laravel 12** - PHP framework
- **SQLite** - databáze (jednoduchá záloha = jeden soubor)
- **Eloquent ORM** - práce s databází

### Frontend
- **Alpine.js 3.14** - reaktivní UI komponenty
- **Tailwind CSS 3** (CDN) - utility-first CSS
- **Alpine Collapse** - animované rozbalovací sekce

### Design
- Dark theme s glass morphism efekty
- Responsive design
- Moderní UI s českým prostředím

## 📁 Struktura projektu

```
HairBookPHP/
├── app/
│   ├── Http/Controllers/    # Kontrolery
│   ├── Models/              # Eloquent modely
│   └── Services/            # Business logika
├── database/
│   ├── migrations/          # Databázové migrace
│   └── database.sqlite      # SQLite databáze
├── resources/
│   └── views/               # Blade šablony
├── routes/
│   └── web.php             # Definice routů
└── storage/
    └── app/backups/        # Zálohy databáze
```

## 🔒 Bezpečnost

- Ochrana aplikace heslem
- CSRF ochrana na všech formulářích
- SQL injection ochrana (Eloquent ORM)
- XSS ochrana (Blade templating)
- Automatické zálohy databáze

## 📊 Databázové tabulky

- `users` - uživatelé systému
- `clients` - klienti salonu
- `client_notes` - poznámky ke klientům
- `products` - produkty v evidenci
- `product_groups` - skupiny produktů
- `visits` - návštěvy klientů
- `visit_services` - služby v návštěvách
- `visit_service_products` - produkty použité při službách
- `visit_retail_items` - produkty prodané domů
- `stock_adjustments` - úpravy skladových zásob
- `service_templates` - šablony služeb
- `appointments` - rezervace v kalendáři
- `app_settings` - nastavení aplikace

## 🔄 Záloha a obnova

### Záloha
```bash
# Manuální záloha
cp database/database.sqlite storage/app/backups/backup_$(date +%Y-%m-%d_%H-%M-%S).sqlite

# Nebo přes webové rozhraní v Nastavení
```

### Obnova
```bash
# Z webového rozhraní: Nastavení → Obnovit ze zálohy
# Nebo manuálně:
cp storage/app/backups/backup_XXX.sqlite database/database.sqlite
```

## 🌐 Deployment

### Sdílený hosting (např. Wedos, Hostinger)

1. Nahrajte soubory přes FTP
2. Nastavte document root na složku `public/`
3. Upravte `.env` pro produkční prostředí
4. Spusťte migrace: `php artisan migrate --force`

Detailní postup v [INSTALL.md](INSTALL.md).

### VPS (Digital Ocean, Linode, Hetzner)

```bash
# Nastavení webového serveru (Nginx/Apache)
# Instalace PHP 8.2 + rozšíření
# Konfigurace SSL certifikátu (Let's Encrypt)
# Nastavení oprávnění

# Deployment
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
chmod -R 775 storage bootstrap/cache
```

## 🛠️ Development

```bash
# Spuštění dev serveru
php artisan serve

# Sledování logů
php artisan pail

# Spuštění testů
php artisan test

# Code formatting
./vendor/bin/pint
```

## 📝 Changelog

### v1.0.0 (2025-12-11)
- ✨ Kompletní kalendář s denním a týdenním zobrazením
- ✨ Správa rezervací s opakováním
- ✨ Evidence klientů s historií návštěv
- ✨ Správa produktů a skladu
- ✨ Finanční přehledy s měsíčními statistikami
- ✨ Záloha a obnova databáze
- ✨ Import produktů z CSV
- ✨ Poznámky ke klientům
- ✨ Šablony služeb
- ✨ Domovská stránka se statistikami

## 🤝 Přispívání

Pull requesty jsou vítány! Pro větší změny prosím nejprve otevřete issue.

## 📄 Licence

MIT License - volně k použití pro komerční i nekomerční účely.

## 💬 Podpora

- 🐛 Issues: [GitHub Issues](https://github.com/supervisor-bit/HairBookLaravelPHP/issues)
- 📚 Dokumentace: [Wiki](https://github.com/supervisor-bit/HairBookLaravelPHP/wiki)

## 👨‍💻 Autor

Vytvořeno s ❤️ pro kadeřnické salony

---

**Verze:** 1.0.0  
**Poslední update:** 11. prosince 2025
