
# BadminTour
Badminton tournament platform built with Laravel 12, Vite, Tailwind, and Alpine.js.

## Table of Contents
- [About](#about)
- [Live Demo](#live-demo)
- [Getting Started](#getting-started)
- [Credentials](#credentials)
- [User Roles](#user-roles)
- [Usage Guide](#usage-guide)
- [API Docs](#api-docs)
- [Contributing](#contributing)
- [Security](#security)
- [License](#license)
- [Contact](#contact)

## About
BadminTour is a Laravel 12 + Vite/Tailwind/Alpine web app for managing badminton tournaments end-to-end. It supports managers (club organizers) and players for registrations, scheduling, round-robin and single-elimination brackets, match results, rankings, withdrawals, and notifications.

## Live Demo
[https://badmintourph.com](https://badmintourph.com)

## Getting Started
Clone the repository and set up your environment:

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

Visit `http://localhost:8000` (or the deployed site) to access the application.

### Environment Variables
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

## Credentials
All test accounts use the password `Password123!`.

| Role | Email | Password |
| --- | --- | --- |
| Club Manager 1 | manager.real1@badmintourph.com | Password123! |
| Club Manager 2 | manager.real2@badmintourph.com | Password123! |
| Player 1 | player.test1@badmintourph.com | Password123! |
| Player 2 | player.test2@badmintourph.com | Password123! |

Additional seeded players: `player.test3@badmintourph.com` through `player.test32@badmintourph.com` (same password).

## User Roles
- **Managers:** Create tournaments, set categories/fees/venues, generate brackets, manage registrations, record scores, walkovers, and reschedules.
- **Players:** Register for categories, invite partners, view brackets and match history, withdraw when allowed.

## Usage Guide
- Role-based dashboards for managers and players
- Tournament creation with categories, fees, venues, schedules; supports round-robin and single-elimination brackets
- Match generation, round normalization, score entry, walkovers, rescheduling, and winner highlighting
- Player registrations with partner invitations, approvals, cancellations, and withdrawals
- Rankings/ELO per category and division; stats export for managers/clubs
- Email notifications (Resend) and responsive UI via Tailwind + Alpine bundled by Vite

## API Docs
This app is UI-first (Blade views). Web routes live in `routes/web.php`:
- Auth: register/login/password flows (Laravel Breeze)
- Manager: tournaments, registrations, matches, withdrawals, exports
- Player: tournament discovery, registrations, brackets, match history
Extend `routes/api.php` if you need a public API.

## Contributing
- Fork, create a feature branch, add tests where possible, open a PR.
- Keep changes focused; follow Laravel/PHP-CS conventions. For larger proposals, open an issue first.

## Security
If you discover any security vulnerabilities, please report them by opening an issue or contacting the project team directly. We take security seriously and will address issues promptly.

## License
All rights reserved.

## Contact
**Project Team (4 members - Full Stack Developers):**
- Eirene Gratuito - eirenegratuito@gmail.com
- Claudine Moneek Mejorada - mejoradac45@gmail.com
- Princes Angelie Subido - princesubido8@gmail.com
- Andrea Laganas - andrea.laganas@gmail.com

**Issue Reporting:** Please open an issue on our repository for bugs or feature requests with steps to reproduce and screenshots.
