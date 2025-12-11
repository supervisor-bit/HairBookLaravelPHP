# 💻 HairBook Desktop pro Windows

Návod jak vytvořit standalone desktopovou aplikaci pro Windows z HairBook systému.

## 🎯 Možnosti

### 1. Electron aplikace (doporučeno)
- **Výhody:** Nejjednodušší, moderní UI, auto-update
- **Nevýhody:** Větší velikost (~150 MB)
- **Tech:** Electron + Laravel backend

### 2. PHP Desktop
- **Výhody:** Menší velikost (~50 MB), nativní vzhled
- **Nevýhody:** Méně moderní, složitější konfigurace
- **Tech:** PHP Desktop + Chromium

### 3. Tauri (moderní alternativa)
- **Výhody:** Malá velikost (~20 MB), rychlá, bezpečná
- **Nevýhody:** Složitější setup
- **Tech:** Rust + WebView

## 🚀 Doporučené řešení: Electron Desktop App

### Příprava projektu

#### 1. Vytvoření Electron wrapperu

Vytvořte novou složku `electron/` v projektu:

```bash
mkdir electron
cd electron
npm init -y
```

#### 2. Instalace závislostí

```bash
npm install --save-dev electron electron-builder
npm install express
```

#### 3. Vytvoření main.js

Vytvořte `electron/main.js`:

```javascript
const { app, BrowserWindow, Menu } = require('electron');
const path = require('path');
const { spawn } = require('child_process');

let mainWindow;
let phpServer;

// Spuštění PHP serveru
function startPHPServer() {
    const phpPath = path.join(__dirname, '../php/php.exe');
    const artisanPath = path.join(__dirname, '../app/artisan');
    
    phpServer = spawn(phpPath, [artisanPath, 'serve', '--host=127.0.0.1', '--port=8000'], {
        cwd: path.join(__dirname, '../app')
    });
    
    phpServer.stdout.on('data', (data) => {
        console.log(`PHP: ${data}`);
    });
    
    phpServer.stderr.on('data', (data) => {
        console.error(`PHP Error: ${data}`);
    });
}

// Vytvoření okna
function createWindow() {
    mainWindow = new BrowserWindow({
        width: 1400,
        height: 900,
        minWidth: 1200,
        minHeight: 700,
        icon: path.join(__dirname, 'icon.png'),
        webPreferences: {
            nodeIntegration: false,
            contextIsolation: true,
            devTools: false // Vypnout v produkci
        },
        backgroundColor: '#0f172a',
        title: 'HairBook - Salon Management'
    });

    // Menu
    const menu = Menu.buildFromTemplate([
        {
            label: 'HairBook',
            submenu: [
                { label: 'O aplikaci', role: 'about' },
                { type: 'separator' },
                { label: 'Ukončit', role: 'quit' }
            ]
        },
        {
            label: 'Úpravy',
            submenu: [
                { label: 'Zpět', role: 'undo' },
                { label: 'Znovu', role: 'redo' },
                { type: 'separator' },
                { label: 'Vyjmout', role: 'cut' },
                { label: 'Kopírovat', role: 'copy' },
                { label: 'Vložit', role: 'paste' }
            ]
        },
        {
            label: 'Zobrazení',
            submenu: [
                { label: 'Reload', role: 'reload' },
                { label: 'Celá obrazovka', role: 'togglefullscreen' }
            ]
        }
    ]);
    Menu.setApplicationMenu(menu);

    // Načtení aplikace (počkat na PHP server)
    setTimeout(() => {
        mainWindow.loadURL('http://127.0.0.1:8000');
    }, 2000);

    mainWindow.on('closed', () => {
        mainWindow = null;
    });
}

// Inicializace
app.on('ready', () => {
    startPHPServer();
    createWindow();
});

// Ukončení
app.on('window-all-closed', () => {
    if (phpServer) {
        phpServer.kill();
    }
    app.quit();
});

app.on('activate', () => {
    if (mainWindow === null) {
        createWindow();
    }
});

// Ukončení PHP serveru při zavření
app.on('will-quit', () => {
    if (phpServer) {
        phpServer.kill();
    }
});
```

#### 4. Package.json konfigurace

Upravte `electron/package.json`:

```json
{
  "name": "hairbook-desktop",
  "version": "1.0.0",
  "description": "HairBook Salon Management System",
  "main": "main.js",
  "scripts": {
    "start": "electron .",
    "build": "electron-builder build --win --x64",
    "build-portable": "electron-builder build --win portable"
  },
  "author": "HairBook",
  "license": "MIT",
  "build": {
    "appId": "com.hairbook.desktop",
    "productName": "HairBook",
    "directories": {
      "output": "dist"
    },
    "win": {
      "target": [
        {
          "target": "nsis",
          "arch": ["x64"]
        },
        {
          "target": "portable",
          "arch": ["x64"]
        }
      ],
      "icon": "icon.ico"
    },
    "nsis": {
      "oneClick": false,
      "allowToChangeInstallationDirectory": true,
      "createDesktopShortcut": true,
      "createStartMenuShortcut": true,
      "shortcutName": "HairBook"
    },
    "extraResources": [
      {
        "from": "../",
        "to": "app",
        "filter": ["**/*", "!node_modules", "!electron", "!.git"]
      },
      {
        "from": "php",
        "to": "php"
      }
    ]
  },
  "dependencies": {
    "express": "^4.18.2"
  },
  "devDependencies": {
    "electron": "^28.0.0",
    "electron-builder": "^24.9.1"
  }
}
```

### Příprava PHP runtime

#### 1. Stažení PHP pro Windows

```powershell
# PowerShell
Invoke-WebRequest -Uri "https://windows.php.net/downloads/releases/php-8.2.13-nts-Win32-vs16-x64.zip" -OutFile "php.zip"
Expand-Archive -Path "php.zip" -DestinationPath "electron/php"
```

#### 2. Konfigurace PHP

Vytvořte `electron/php/php.ini`:

```ini
extension_dir = "ext"
extension=mbstring
extension=openssl
extension=pdo_sqlite
extension=sqlite3
extension=fileinfo
extension=curl

date.timezone = Europe/Prague
memory_limit = 256M
```

### Příprava Laravel aplikace

#### 1. Optimalizace pro desktop

V `.env`:

```env
APP_ENV=desktop
APP_DEBUG=false
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite

SESSION_DRIVER=file
CACHE_STORE=file
```

#### 2. Build script

Vytvořte `build-desktop.sh`:

```bash
#!/bin/bash

echo "🔨 Building HairBook Desktop..."

# Optimalizace Laravel
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Vytvoření databáze
touch database/database.sqlite

# Build Electron app
cd electron
npm install
npm run build

echo "✅ Build complete! Installer is in electron/dist/"
```

### Build procesu

```bash
# 1. Příprava aplikace
chmod +x build-desktop.sh
./build-desktop.sh

# 2. Vytvoření ikony
# Vytvořte icon.ico (256x256) a icon.png

# 3. Build
cd electron
npm run build
```

Výsledek: `electron/dist/HairBook Setup 1.0.0.exe`

---

## 📦 Alternativa: PHP Desktop (jednodušší)

### Instalace PHP Desktop

1. **Stáhněte PHP Desktop:**
   ```
   https://github.com/cztomczak/phpdesktop/releases
   ```

2. **Rozbalte do složky `hairbook-desktop/`**

3. **Zkopírujte Laravel aplikaci:**
   ```bash
   xcopy /E /I /Y HairBookPHP hairbook-desktop\www
   ```

4. **Konfigurace settings.json:**

```json
{
    "application": {
        "name": "HairBook",
        "version": "1.0.0"
    },
    "main_window": {
        "title": "HairBook - Salon Management",
        "width": 1400,
        "height": 900,
        "minimum_size": [1200, 700],
        "disable_maximize_button": false,
        "enable_downloads": true
    },
    "web_server": {
        "listen_on": ["127.0.0.1", 8000],
        "www_directory": "www/public"
    },
    "chrome": {
        "cache_path": "cache",
        "context_menu": {
            "enable_dev_tools": false
        }
    }
}
```

5. **Přejmenujte phpdesktop-chrome.exe na HairBook.exe**

6. **Vytvořte installer pomocí Inno Setup:**

```inno
[Setup]
AppName=HairBook
AppVersion=1.0.0
DefaultDirName={pf}\HairBook
DefaultGroupName=HairBook
OutputBaseFilename=HairBook-Setup
Compression=lzma2
SolidCompression=yes

[Files]
Source: "hairbook-desktop\*"; DestDir: "{app}"; Flags: ignoreversion recursesubdirs

[Icons]
Name: "{group}\HairBook"; Filename: "{app}\HairBook.exe"
Name: "{commondesktop}\HairBook"; Filename: "{app}\HairBook.exe"

[Run]
Filename: "{app}\HairBook.exe"; Description: "Spustit HairBook"; Flags: nowait postinstall
```

---

## 🎨 Přidání splash screenu

Vytvořte `electron/splash.html`:

```html
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .splash {
            text-align: center;
            color: white;
        }
        .logo {
            font-size: 72px;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 48px;
            margin: 0;
        }
        p {
            font-size: 18px;
            opacity: 0.8;
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 30px auto 0;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="splash">
        <div class="logo">💇‍♀️</div>
        <h1>HairBook</h1>
        <p>Načítání aplikace...</p>
        <div class="spinner"></div>
    </div>
</body>
</html>
```

---

## 🔄 Auto-update

Pro automatické aktualizace použijte `electron-updater`:

```bash
npm install electron-updater
```

V `main.js`:

```javascript
const { autoUpdater } = require('electron-updater');

app.on('ready', () => {
    autoUpdater.checkForUpdatesAndNotify();
    startPHPServer();
    createWindow();
});

autoUpdater.on('update-downloaded', () => {
    dialog.showMessageBox({
        type: 'info',
        title: 'Aktualizace dostupná',
        message: 'Nová verze byla stažena. Restartovat?',
        buttons: ['Ano', 'Později']
    }).then((result) => {
        if (result.response === 0) {
            autoUpdater.quitAndInstall();
        }
    });
});
```

---

## 📦 Výsledné soubory

Po buildu získáte:

```
electron/dist/
├── HairBook Setup 1.0.0.exe      # Installer (~150 MB)
├── HairBook 1.0.0 Portable.exe   # Portable verze
└── win-unpacked/                 # Rozbalená verze
```

---

## ✅ Výhody desktop aplikace

- ✨ Funguje bez internetu
- 🔒 Data zůstávají lokálně
- 🚀 Rychlejší než webový prohlížeč
- 💾 Snadná záloha (jeden soubor SQLite)
- 🎨 Nativní vzhled Windows
- 🔄 Auto-update možnosti
- 🖥️ Ikona na ploše a v Start menu

---

## 📊 Porovnání velikostí

| Metoda | Velikost | Rychlost | Složitost |
|--------|----------|----------|-----------|
| Electron | ~150 MB | ⭐⭐⭐⭐ | Střední |
| PHP Desktop | ~50 MB | ⭐⭐⭐ | Nízká |
| Tauri | ~20 MB | ⭐⭐⭐⭐⭐ | Vysoká |

---

## 🚀 Distribuce

### 1. Přes vlastní web
```
https://vasedomena.cz/download/HairBook-Setup.exe
```

### 2. Microsoft Store
- Vyžaduje Developer účet ($19)
- Automatické aktualizace
- Důvěryhodnost

### 3. Chocolatey package
```powershell
choco install hairbook
```

---

## 📝 Poznámky

- Desktop verze používá stejný kód jako web
- Data jsou v SQLite databázi v AppData
- Záloha: kopírovat database.sqlite
- Licence: MIT (volná distribuce)

---

**Další kroky:**
1. Rozhodnout mezi Electron/PHP Desktop
2. Vytvořit ikonu aplikace
3. Build a testování
4. Vytvoření installeru
5. Distribuce

Chceš, abych vytvořil kompletní Electron setup pro tvůj projekt?
