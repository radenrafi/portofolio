# AGENTS: Laravel Backend (Critispace)

This file guides agents and contributors working in this repository. Its scope is the entire repo.

## Overview
- Framework: Laravel 12 (PHP ^8.2)
- Purpose: Backend API/app for Critispace
- Key dirs: `app/`, `routes/`, `database/`, `config/`, `public/`

## Prerequisites
- PHP 8.2+ with common extensions (OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, Fileinfo, BCMath)
- Composer (for dependency management)
- Node.js 18+ and npm (for Vite/Tailwind during local dev)
- Database: MySQL (default) or SQLite

## First-Time Setup
1. Environment file
   - If missing, copy: `cp .env.example .env`
   - Ensure `APP_KEY` is set, else run: `php artisan key:generate`
2. Database
   - MySQL (default): set `DB_*` in `.env` accordingly.
   - SQLite (optional): set in `.env`:
     - `DB_CONNECTION=sqlite`
     - `DB_DATABASE=database/database.sqlite`
     - Ensure the file exists: `touch database/database.sqlite`
3. Install dependencies
   - PHP: `composer install` (skip if `vendor/` is already present)
   - JS: `npm install` (only needed if running Vite/Tailwind locally)
4. Initialize app
   - `php artisan optimize:clear`
   - `php artisan migrate --force`
   - `php artisan db:seed --force`
   - `php artisan storage:link`
   - If using DB-backed features and tables are missing:
     - `php artisan session:table && php artisan queue:table && php artisan migrate`

## Running
- PHP server: `php artisan serve` (default `http://localhost:8000`)
- Full dev loop (PHP + queue + logs + Vite): `composer run dev`
  - Requires `npm install` and opens multiple processes via `concurrently`.

## Testing
- Run tests: `php artisan test`
- Alt via Composer: `composer test` (clears config cache then runs tests)

## Useful Commands
- Cache/optimize: `php artisan optimize`, `php artisan optimize:clear`
- DB reset + seed: `php artisan migrate:fresh --seed`
- Tinker REPL: `php artisan tinker`

## Conventions
- Follow Laravel conventions and PSR-12 style.
- Prefer migrations, seeders, and factories for schema/data changes.
- Keep changes minimal and focused; avoid unrelated refactors.
- Do not edit files under `vendor/`.
- Store framework configs in `config/` and app logic under `app/`.

## Notes for Agents
- Respect this AGENTS.md for all files in this repo.
- Before running anything, ensure `.env` is configured and the DB is reachable.
- If you see “Migration already exists” for sessions/jobs, proceed; tables may already be present.
- On Windows, if `storage:link` fails, rerun in an elevated shell or create a junction manually.

## Security
- Never commit secrets. `.env` should not be committed.
- Ensure `APP_KEY` is present in non-testing environments.

