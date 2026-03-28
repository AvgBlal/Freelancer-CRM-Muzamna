

<h1 align="center">Freelancer CRM</h1>

<p align="center">
  <strong>A self-hosted CRM built for freelancers and small agencies.</strong><br>
  Manage clients, services, projects, tasks, finances, and notes — all in one place.
</p>

<p align="center">
  <a href="https://freelancer-crm.muzamna.com/">🔗 Live Demo</a> &nbsp;·&nbsp;
  <a href="https://github.com/AvgBlal/Freelancer-CRM-Muzamna">📦 GitHub</a> &nbsp;·&nbsp;
  <a href="https://crm.muzamna.com/freelance-crm-muzamna.zip">⬇️ Download ZIP</a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.1+-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.1+">
  <img src="https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL 8.0+">
  <img src="https://img.shields.io/badge/License-MIT-green?style=flat-square" alt="License">
  <img src="https://img.shields.io/badge/Framework-None_(Native_PHP)-orange?style=flat-square" alt="Native PHP">
</p>

---

<p align="center">
  <img src="https://crm.muzamna.com/demo.jpg" alt="FCM Dashboard" width="100%" style="border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
</p>

---

## Why Freelancer CRM?

Most CRMs are bloated SaaS products that charge monthly, require cloud accounts, and don't speak your language. **Freelancer CRM** is different:

- **100% self-hosted** — your data stays on your server
- **Native PHP** — no Laravel, no Symfony, no framework overhead
- **Arabic-first UI** — full RTL support out of the box
- **Zero recurring costs** — install once, use forever
- **Web installer** — no terminal needed, set up from your browser

---

## Features

| Module | What it does |
|--------|-------------|
| **Clients** | Full client profiles with tags, contact info, and history |
| **Services** | Track active services, renewals, and expiry alerts |
| **Service Types** | Categorize services (hosting, design, development, etc.) |
| **Projects** | Manage projects with status tracking and deadlines |
| **Tasks** | Assign tasks, set priorities, track completion |
| **Dues** | Track money owed to you — per client, per service |
| **Expenses** | Log business expenses with categories |
| **Unpaid Tasks** | Dedicated view for work delivered but not yet paid |
| **Quotes** | Generate and send price quotes to clients |
| **Invoices** | Create professional invoices from quotes or manually |
| **Notes** | Attach notes to clients, projects, or anything |
| **Activity Log** | Full audit trail of every action in the system |
| **Reports** | Financial summaries and project status reports |
| **Safe Items** | Securely store passwords, API keys, and credentials |
| **Notifications** | Email + WhatsApp alerts for expiry, overdue, and reminders |
| **Multi-role** | Admin, Manager, and Employee roles with permissions |
| **Cron Automation** | Daily checks for expiring services, overdue tasks, and dues |

---

## Requirements

- PHP 8.1+
- MySQL 8.0+
- Apache with `mod_rewrite`

---

## Quick Start (Web Installer)

### 1. Upload & Configure Apache

```bash
git clone https://github.com/AvgBlal/Freelancer-CRM-Muzamna.git
cd Freelancer-CRM-Muzamna
```

Point Apache to the project root:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/Freelancer-CRM-Muzamna

    <Directory /path/to/Freelancer-CRM-Muzamna>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Enable `mod_rewrite`:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 2. Run the Installer

Open your browser:

```
http://your-domain.com/install/install.php
```

The web installer will handle:
- Database connection & creation
- Running all migrations
- Creating your admin account
- Optionally loading demo data

A `config.local.php` file is generated automatically.

### 3. Set Up the Cron Job

One cron job handles everything — expiring services, overdue dues, overdue tasks, and reminders:

```bash
0 9 * * * /usr/bin/php /path/to/Freelancer-CRM-Muzamna/cron/daily_check.php >> /path/to/Freelancer-CRM-Muzamna/storage/logs/cron.log 2>&1
```

On cPanel shared hosting, add this via **Cron Jobs** in your control panel.

### 4. Configure Notifications (Optional)

Go to **Settings** → configure:
- **Email**: SMTP host, port, credentials, sender address
- **WhatsApp**: WhatsPie API key and sender number

---

## Manual Installation

<details>
<summary>Click to expand manual setup steps</summary>

### 1. Create Database

```bash
mysql -u root -p -e "CREATE DATABASE fcm_native CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 2. Run Migrations
Done Already

### 3. Configure Credentials

Set environment variables or edit `app/Config/config.php`:

```bash
export DB_HOST=localhost
export DB_DATABASE=fcm_native
export DB_USERNAME=your_user
export DB_PASSWORD=your_password
export APP_URL=http://your-domain.com
```

### 4. Create Admin User

```bash
php -r "echo password_hash('your_password', PASSWORD_DEFAULT) . PHP_EOL;"

mysql -u root -p fcm_native -e "
INSERT INTO users (name, email, password_hash, role, is_active, created_at)
VALUES ('Admin', 'admin@example.com', 'PASTE_HASH_HERE', 'admin', 1, NOW());
"
```

### 5. Load Demo Data (Optional)

```bash
mysql -u root -p fcm_native < dev/migrations/seed_demo.sql
```

Demo accounts:
| Email | Role | Password |
|-------|------|----------|
| `admin@demo.com` | Admin | `password` |
| `manager@demo.com` | Manager | `password` |
| `employee@demo.com` | Employee | `password` |

</details>

---

## Dev Dependencies (Optional)

Only needed for running tests:

```bash
composer install
./vendor/bin/phpunit
```

---

## Troubleshooting

| Issue | Fix |
|-------|-----|
| 403 Forbidden | Ensure `AllowOverride All` is set and `mod_rewrite` is enabled |
| 500 Error | Check `storage/logs/` for errors; verify PHP 8.1+ |
| Cron not running | Verify PHP path with `which php`; check `storage/logs/cron.log` |
| Blank page | Set `APP_DEBUG=true` or check PHP error log |
| Installer not showing | Ensure Apache points to the project root, not a subdirectory |

---

## Tech Stack

- **Backend**: Native PHP 8.1+ (no framework)
- **Database**: MySQL 8.0+ with utf8mb4
- **Frontend**: Vanilla JS + CSS (no jQuery, no build tools)
- **Notifications**: SMTP Email + WhatsPie WhatsApp API
- **PDF**: mPDF for invoice/quote generation
- **Architecture**: MVC pattern, clean routing, prepared statements

---

## License

MIT License — free for personal and commercial use.

---

<p align="center">
  Built with ❤️ by <a href="https://muzamna.com">Muzamna Technical Solutions</a><br>
  <sub>نعمل بحب</sub>
</p>
