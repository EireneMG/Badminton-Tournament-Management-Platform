# BadminTour
Badminton tournament platform built with Laravel 12, Vite, Tailwind, and Alpine.js. Deployed at [badmintourph.com](https://badmintourph.com).

## Table of Contents
- [Introduction](#introduction)
- [Key Features](#key-features)
- [Project Structure](#project-structure)
- [Database Structure](#database-structure)
- [Installation Guide](#installation-guide)
- [Usage](#usage)
- [API](#api)
- [Environment Variables](#environment-variables)
- [Contribution Guide](#contribution-guide)
- [Support & Contact](#support--contact)

## Introduction
BadminTour is a Laravel 12 + Vite/Tailwind/Alpine web app for managing badminton tournaments end-to-end. It supports managers (club organizers) and players for registrations, scheduling, round-robin and single-elimination brackets, match results, rankings, withdrawals, and notifications.

## Key Features
- Role-based dashboards for managers and players.
- Tournament creation with categories, fees, venues, schedules; supports round-robin and single-elimination brackets.
- Match generation, round normalization, score entry, walkovers, rescheduling, and winner highlighting.
- Player registrations with partner invitations, approvals, cancellations, and withdrawals.
- Rankings/ELO per category and division; stats export for managers/clubs.
- Email notifications (Resend) and responsive UI via Tailwind + Alpine bundled by Vite.

## Installation Guide
**Deployed:** [https://badmintourph.com](https://badmintourph.com)

**Local setup**
```bash
git clone https://github.com/your-org/badmintour.git
cd badmintour
cp .env.example .env
composer install
npm install
php artisan key:generate
php artisan migrate --seed --class=ProductionSeeder
npm run build        # or npm run dev for hot reload
php artisan serve
```

## Usage
- Visit `http://localhost:8000` (or the deployed site).
- Log in with the test users below.
- Managers: create tournaments, set categories/fees/venues, generate brackets, manage registrations, record scores/walkovers/reschedules.
- Players: register for categories, invite partners, view brackets and match history, withdraw when allowed.

## API
This app is UI-first (Blade views). Web routes live in `routes/web.php`:
- Auth: register/login/password flows (Laravel Breeze)
- Manager: tournaments, registrations, matches, withdrawals, exports
- Player: tournament discovery, registrations, brackets, match history
Extend `routes/api.php` if you need a public API.

## Environment Variables
Set in `.env`:
```env
APP_NAME=BadmintourPH
APP_ENV=local
APP_KEY=base64:...
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=badmintour
DB_USERNAME=root
DB_PASSWORD=secret

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@badmintourph.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Project Structure
- `app/` — Laravel app code (models, controllers, services, helpers)
- `resources/views/` — Blade templates for manager/player dashboards and brackets
- `routes/web.php` — Web routes (auth, manager, player flows)
- `database/seeders/ProductionSeeder.php` — Demo data and test accounts
- `public/` — Public assets and entry point
- `vite.config.js`, `tailwind.config.js`, `postcss.config.js` — Frontend tooling
- `composer.json`, `package.json` — PHP and JS dependencies

## Database Structure
High-level tables:
- `users` — players and managers with verification flags
- `clubs`, `club_players` — club ownership and memberships
- `tournaments` — tournament metadata (status, bracket type, venue, dates, fees)
- `tournament_categories` — per-tournament divisions (singles/doubles/mixed)
- `tournament_registrations` — entries per category (with partner where needed)
- `tournament_matches` — generated matches with rounds, times, courts, winners
- `match_results` — set scores, walkovers, and outcomes
- `elo_ratings` / `ranking_histories` — rating snapshots per category

## Test Users
All test accounts use the password `Password123!`.

| Role | Email | Password |
| --- | --- | --- |
| Club Manager 1 | manager.real1@badmintourph.com | Password123! |
| Club Manager 2 | manager.real2@badmintourph.com | Password123! |
| Player 1 | player.test1@badmintourph.com | Password123! |
| Player 2 | player.test2@badmintourph.com | Password123! |

Additional seeded players: `player.test3@badmintourph.com` through `player.test32@badmintourph.com` (same password).

## Contribution Guide
- Fork, create a feature branch, add tests where possible, open a PR.
- Keep changes focused; follow Laravel/PHP-CS conventions. For larger proposals, open an issue first.

## Support & Contact
- **Project Team (4 members - Full Stack Developers):**
  - Eirene Gratuito - eirenegratuito@gmail.com
  - Claudine Moneek Mejorada - mejoradac45@gmail.com
  - Princes Angelie Subido - princesubido8@gmail.com
  - Andrea Laganas - andrea.laganas@gmail.com
- **Issue Reporting:** Please open an issue on our repository for bugs or feature requests with steps to reproduce and screenshots.