## 9Mint — Local Dev Setup (Windows)

You will install **PHP 8.x**, **Composer 2**, **Node.js 20 LTS**, and **MySQL 8**, then wire Laravel to your local DB.

---

## 0) What you need (once per machine)
- PHP **8.x** (ZIP)
- Composer **2**
- Node.js (Windows x64 **.msi**)
- MySQL **Server 8.0.44** + **Workbench** (via **MySQL Installer**)

---

## 1) PHP 8.x (ZIP, no admin) — **includes PATH setup**

1. Make folder  
   `C:\Users\<you>\php`
2. Extract the PHP ZIP **into that folder**. You should see:
   ```
   C:\Users\<you>\php\php.exe
   C:\Users\<you>\php\ext\...
   C:\Users\<you>\php\php.ini-development
   ```
3. **Add PHP to your user PATH**
   - Win+R → `sysdm.cpl` → **Enter**  
   - **Advanced** → **Environment Variables…**  
   - Under **User variables**, select **Path** → **Edit** → **New** → add  
     `C:\Users\<you>\php`  
   - **OK** out of all dialogs.
   - Open a **new** terminal → verify:
     ```cmd
     where php
     php -v
     ```
     `where php` must show `C:\Users\<you>\php\php.exe` first.
4. **Create a real config**
   - Copy `php.ini-development` → rename the copy to **`php.ini`**  
     *(Turn on "File name extensions" in Explorer so you don't create `php.ini.ini`.)*
5. **Edit** `C:\Users\<you>\php\php.ini`
   - Set:
     ```
     extension_dir="ext"
     ```
   - **Uncomment** (remove `;`):
     ```
     extension=curl
     extension=mbstring
     extension=openssl
     extension=pdo_mysql
     extension=fileinfo
     extension=zip
     ```
     *(optional but useful)*
     ```
     extension=intl
     extension=gd
     ```
   - Timezone:
     ```
     date.timezone="Europe/London"
     ```
6. If `php -v` complains about missing DLLs, install **Microsoft Visual C++ 2015–2022 (x64)** and retry.  
   Check:
   ```cmd
   php --ini   # Loaded Configuration File should be C:\Users\<you>\php\php.ini
   ```

---

## 2) Composer 2

1. Run **Composer-Setup.exe**
   - PHP executable → `C:\Users\<you>\php\php.exe`
   - **Developer mode:** OFF
   - **Proxy:** leave unchecked
2. New terminal:
   ```cmd
   composer -V
   ```
   If not 2.x:
   ```cmd
   composer self-update --2
   ```
> If Composer whines about `openssl`/`mbstring`/etc., you didn't enable them in `php.ini`.

---

## 3) Node.js

- Download the **Windows x64 .msi**, install with defaults.  
- New terminal:
  ```cmd
  node -v
  npm -v
  ```

---

## 4) MySQL 8 (Server + Workbench via **MySQL Installer**)

1. Run **MySQL Installer (Community)**
2. **Setup Type:** **Custom**
3. **Add**: **MySQL Server 8.0.44 (x64)** and **MySQL Workbench 8.x** (Shell optional) → **Execute**
4. **Configuration wizard**
   - **Type & Networking:** Development Computer, TCP/IP **3306**, allow firewall  
   - **Authentication:** **Strong Password Encryption (`caching_sha2_password`)**  
   - **Accounts & Roles:** set a **strong root password** (for you; not for the app)  
   - **Windows Service:** Start as a service + auto-start  
   - **Apply/Finish**

*(If Workbench fails to download: click **Back → Execute** again, or use the Offline Installer.)*

---

## 5) Create the project DB + app user (once per machine)

Open **MySQL Workbench** → connect as **root** → new SQL tab → run:

```sql
CREATE DATABASE 9mint
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER 'mint'@'localhost' IDENTIFIED BY 'devpass';

GRANT ALL PRIVILEGES ON 9mint.* TO 'mint'@'localhost';
FLUSH PRIVILEGES;
```

- Root = your private admin login.  
- App login = `mint` / `devpass`, limited to `9mint`.  
- **Do not** delete system schemas (`sys`, `mysql`, etc.).

---

## 6) Project bootstrap (per machine)

**Open a terminal in the project root (folder with `artisan`).**  
If needed:
```cmd
cd /d "C:\Users\<you>\9Mint"
```
**Verify:**
```cmd
dir
```
You should see `artisan`, `composer.json`, `.env.example`, etc.

**Install & create `.env`:**
```cmd
composer install
copy .env.example .env
```

**Edit `.env` (no `#`, no spaces around `=`). If your value has spaces or `#`, wrap in quotes.**
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=9mint
DB_USERNAME=mint
DB_PASSWORD=devpass
APP_URL=http://127.0.0.1:8000
```

**Apply settings & init DB (still in the project folder):**
```cmd
php artisan config:clear
php artisan key:generate
php artisan migrate
```

**Front-end deps:**
```cmd
npm ci   :: or npm install
```

**Run (two terminals, keep both open):**

**Terminal A**
```cmd
php artisan serve
```

**Terminal B**
```cmd
npm run dev
```

Open **http://127.0.0.1:8000**.

---

## Notes

### What is shared vs local
**Shared (commit):** `app/**`, `routes/**`, `resources/**`, `database/migrations/**`, `database/seeders/**`, `database/factories/**`, `composer.json`/`composer.lock`, `package.json`/`package-lock.json`, `vite.config.*`, `README.md`, `docs/**`, `.env.example` (keys listed, no secrets).  
**Local (never commit):** `.env`, `vendor/`, `node_modules/`, `storage/logs/*`, any `.sql` dumps.

### Security baseline (local)
- Root password: **strong & unique** (per dev).  
- App user: **`mint/devpass`**, limited to **`9mint`** (do **not** use root in `.env`).  
- Keep DB bound to **127.0.0.1:3306** (don't expose to LAN).  
- Never put real secrets in Git.

### Daily/Pre-push routine moved to [docs/dev-workflow.md](dev-workflow.md)