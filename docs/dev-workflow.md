# 9Mint — Dev Routine (Daily \& Pre-Push)



This is the small set of things you do **every time** you pull or push. Keep it short, keep it predictable.



---



## After you **clone** (first time only)



1\. Copy env \& install deps:

```cmd

composer install

copy .env.example .env

npm ci

```



2\. Edit `.env` (DB creds, APP\_URL), then apply:

```cmd

php artisan config:clear

php artisan key:generate

php artisan migrate

php artisan storage:link

```



---



## After you **pull** from Git



1\. **Dependencies** (only if lockfiles changed):

```cmd

composer install

npm ci

```



2\. **Config \& DB** (always safe to run):

```cmd

php artisan config:clear

php artisan migrate

```



3\. **Start dev servers** (two terminals):

```cmd

php artisan serve

npm run dev

```

---



## Day-to-day quick checks



**Routes visible:**

```cmd

php artisan route:list --path=/api/v1

```



**App URL:** open `http://127.0.0.1:8000/api/v1/health`



**Storage link OK:**

```cmd

echo ok> storage/app/public/ping.txt

\# visit /storage/ping.txt

```



---



## Protected endpoints during dev (no login UI yet)



Use a Sanctum personal access token.



**Create token (one-time):**

```cmd

php artisan tinker

>>> $u = \\App\\Models\\User::first();

>>> $token = $u->createToken('dev')->plainTextToken;

>>> $token

```



**Call protected routes with header:**

```

Authorization: Bearer <paste-token>

```



*(When you later add SPA login, routes don't change; only the client auth method changes.)*



---



## Before you **push** (PR checklist)



- Compiles / boots locally: `php artisan serve` + `npm run dev`

- DB up to date: `php artisan migrate` (no pending local changes)

- New schema? Add a migration, don't edit old ones

- Run formatters/tests if they exist (e.g. `php artisan test`, `vendor/bin/pint`)

- No secrets in Git (`.env` ignored)



---



## Common commands (copy-paste)



```cmd

php artisan config:clear

php artisan migrate

php artisan migrate:fresh --seed   :: dev only

php artisan route:list --path=/api/v1

php artisan storage:link --force

composer dump-autoload

```



---



## Useful docs



- **Sanctum tokens vs SPA cookies:** https://laravel.com/docs/sanctum

- **Routing \& route:list:** https://laravel.com/docs/routing

- **Migrations:** https://laravel.com/docs/migrations



