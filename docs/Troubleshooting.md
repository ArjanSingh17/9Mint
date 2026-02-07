# Troubleshooting — 9Mint

### `php` not found
- You didn't add `C:\Users\<you>\php` to **user PATH**, or you didn't open a **new** terminal.  
  Check: `where php`

### Composer wants `openssl`/`mbstring`/…
- You didn't uncomment them in `php.ini`. Fix that, then run `composer -V` again.

### `SQLSTATE[HY000] [1049] Unknown database '9mint'`
- You didn't create it. Run the SQL from `docs/sql/create-dev-db.sql`. Then:
  ```cmd
  php artisan config:clear && php artisan migrate
  ```

### `SQLSTATE[HY000] [1045] Access denied`
- `.env` creds don't match the MySQL user you created. Use `mint/devpass` (or re‑grant). Then:
  ```cmd
  php artisan config:clear && php artisan migrate
  ```

### Unstyled page / "Vite manifest not found" / React components not loading
- Run `npm ci` (or `npm install`) to install React and all dependencies
- Keep `npm run dev` running for development, or run `npm run build` for production
- Ensure Node.js 20 LTS is installed: `node -v`

### Port already in use
- PHP server: `php artisan serve --port=8001`  
- Vite: `npm run dev -- --port=5174`  
- MySQL: stop other MySQL/MariaDB/XAMPP using 3306.

### Workbench asks to save script
- Click **Don't Save**; execute with the **lightning** icon or **Ctrl+Shift+Enter**.

### `.env` gotchas
- Lines starting with `#` are **comments** → remove `#`.  
- No spaces around `=`.  
- Wrap values with spaces or `#` in quotes.

**After changing `.env`:**
```cmd
php artisan config:clear && php artisan migrate
```

### Route [login] not defined (hitting protected API in browser)
- You're unauthenticated and Laravel tried to redirect to the named `login` route.  
- **Fix:** add `Accept: application/json` to get a 401 JSON, or add a dummy `GET /login` that returns 401 JSON, or authenticate (cookie/token).

### The [public/storage] link already exists.
- Symlink is already there. If broken, run:
  ```cmd
  php artisan storage:link --force
  ```

### 419 / CSRF errors on login
- SPA flow: `GET /sanctum/csrf-cookie` then `POST /login` with `X-XSRF-TOKEN` header.  
- Or use a Bearer token during dev (no CSRF required).

### Add [slug] to fillable / MassAssignmentException
- Add the field to `$fillable` (or use `$guarded=[]` in dev). Then retry the create.

### NFT Discovery Board not showing up
- **No NFTs in database:** Run `php "dev tools/seed-collections-and-nfts.php"` to populate initial data
- **Missing price columns:** Run `php artisan migrate` to apply pending migrations (especially `2026_01_21_000001_add_size_prices_to_nfts_table.php`)
- **Vite assets not loading:** 
  - Development: Ensure `npm run dev` is running in a separate terminal
  - Production: Run `npm run build` to compile assets
- **Check NFT count:** `php artisan tinker --execute="echo App\Models\Nft::where('is_active', true)->where('editions_remaining', '>', 0)->count();"`