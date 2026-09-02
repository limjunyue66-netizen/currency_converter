# Currency Converter

A complete currency converter web application built with PHP 8+, MySQL, HTML5, CSS3, and vanilla JavaScript. Features live exchange rates, historical charts, searchable currency picker, A-Z rates directory, and English/Chinese language support.

## Screenshots

| Converter | Rates Directory | Historical Chart |
|-----------|-----------------|------------------|
| ![Converter](docs/screenshots/converter.svg) | ![Rates Directory](docs/screenshots/rates-directory.svg) | ![Historical Chart](docs/screenshots/historical-chart.svg) |

## Features

- **Currency Converter** — Convert amounts with AJAX, swap currencies, view live rates and last updated time
- **Live Exchange Rates** — Fetches from `open.er-api.com` with `frankfurter.app` fallback
- **MySQL Caching** — Rates cached for 1 hour; stale cache used when APIs are unavailable
- **Currency Search** — Search by code, currency name, or country
- **Historical Rates Chart** — Canvas chart for 7D, 30D, 90D, 1Y, 5Y, and MAX periods
- **A-Z Rates Directory** — Browse all currencies alphabetically on `rates.php`
- **Multi-language** — English and Chinese (saved in `localStorage`)
- **Security** — PDO prepared statements, input validation, XSS protection, rate limiting
- **Responsive Dark Theme** — Modern UI without CSS frameworks

## Requirements

- PHP 8.0 or higher
- MySQL 5.7+ / MariaDB 10.3+
- Apache with `mod_rewrite` (optional, for `.htaccess`)
- PHP extensions: `pdo_mysql`, `json`, `mbstring`, `openssl`

## Installation

### 1. Clone or copy files

Place the project in your web server directory:

```
C:\xampp\htdocs\Currency_converter\   (XAMPP on Windows)
/var/www/html/Currency_converter/     (Linux)
```

### 2. Database setup

**Option A — phpMyAdmin**

1. Open phpMyAdmin (`http://localhost/phpmyadmin`)
2. Import `includes/schema.sql`

**Option B — Command line**

```bash
mysql -u root -p < includes/schema.sql
```

**Option C — PHP setup script**

```bash
php setup_db.php
```

### 3. Configuration

Copy the example database config and edit your credentials:

```bash
cp includes/database.example.php includes/config.local.php
```

Or copy the local config example:

```bash
cp includes/config.local.example.php includes/config.local.php
```

Edit `includes/config.local.php`:

```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'currency_converter');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('APP_URL', '/Currency_converter');  // Use '' if at web root
```

| Setting | Description |
|---------|-------------|
| `DB_HOST` | MySQL host (use `127.0.0.1` if `localhost` fails) |
| `DB_NAME` | Database name |
| `DB_USER` | MySQL username |
| `DB_PASS` | MySQL password |
| `APP_URL` | URL path prefix (e.g. `/Currency_converter`) |
| `FORCE_HTTPS` | Set `true` on production with SSL |
| `CACHE_TTL` | Rate cache duration in seconds (default: 3600) |

### 4. Start the server

**XAMPP:** Start Apache and MySQL from the XAMPP Control Panel.

**PHP built-in server (development only):**

```bash
php -S localhost:8000
```

Visit: `http://localhost/Currency_converter/`

## Project Structure

```
/
├── api/
│   ├── convert.php      # Currency conversion endpoint
│   ├── currencies.php   # Currency list/search
│   ├── rates.php        # All rates for a base currency
│   └── history.php      # Historical rate data
├── assets/
│   ├── css/style.css
│   └── js/
│       ├── app.js       # Main application logic
│       └── i18n.js      # Translations (EN/ZH)
├── includes/
│   ├── config.php              # App configuration (no secrets)
│   ├── config.local.example.php
│   ├── database.example.php    # Database credentials template
│   ├── db.php                  # PDO connection
│   ├── helpers.php      # Utilities & security
│   ├── layout.php       # Shared page layout
│   ├── rates.php        # Rate fetching & caching logic
│   └── schema.sql       # Database schema & seed data
├── index.php            # Currency converter
├── rates.php            # A-Z rates directory
├── manual.php           # User manual
└── setup_db.php         # Database setup helper
```

## API Endpoints

All endpoints return JSON.

### `GET /api/convert.php`

Convert an amount between currencies.

| Parameter | Required | Description |
|-----------|----------|-------------|
| `amount`  | Yes      | Amount to convert |
| `from`    | Yes      | Source currency code (e.g. USD) |
| `to`      | Yes      | Target currency code (e.g. MYR) |

Example: `/api/convert.php?amount=100&from=USD&to=MYR`

### `GET /api/currencies.php`

List or search currencies.

| Parameter | Description |
|-----------|-------------|
| `q`       | Search query (code, name, country) |
| `letter`  | Filter by first letter (A-Z) |

### `GET /api/rates.php`

Get all rates for a base currency.

| Parameter | Description |
|-----------|-------------|
| `base`    | Base currency code (default: USD) |
| `q`       | Search filter |
| `letter`  | A-Z letter filter |

### `GET /api/history.php`

Get historical exchange rates.

| Parameter | Description |
|-----------|-------------|
| `from`    | Source currency |
| `to`      | Target currency |
| `period`  | `7d`, `30d`, `90d`, `1y`, `5y`, `max` |

## Exchange Rate Data Flow

1. Check MySQL cache (valid for 1 hour)
2. If expired, fetch from `open.er-api.com`
3. If primary fails, try `frankfurter.app`
4. If both fail, use stale cached rates
5. If no cache exists, return a friendly error

## Local Development

1. Ensure XAMPP Apache and MySQL are running
2. Import the database schema
3. Configure `includes/config.local.php`
4. Open `http://localhost/Currency_converter/`
5. Test conversion: enter amount, select currencies, click Convert
6. Check the historical chart and rates directory

## iFastNet Deployment

1. Upload all files via FTP/File Manager to `public_html` or a subfolder
2. Create a MySQL database in iFastNet control panel
3. Import `includes/schema.sql` via phpMyAdmin
4. Create `includes/config.local.php` with iFastNet database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_ifastnet_dbname');
define('DB_USER', 'your_ifastnet_dbuser');
define('DB_PASS', 'your_ifastnet_dbpass');
define('APP_URL', '');  // or '/subfolder' if not in root
define('FORCE_HTTPS', true);
```

5. Ensure PHP 8+ is selected in hosting control panel
6. Enable SSL/HTTPS for your domain
7. Verify `api/` endpoints are accessible

## Troubleshooting

### Database connection failed

- Check `DB_HOST`, `DB_USER`, `DB_PASS` in `config.local.php`
- Try `127.0.0.1` instead of `localhost`
- Ensure MySQL service is running
- Verify the database exists and schema is imported

### Exchange rates unavailable

- Check server can make outbound HTTPS requests (`allow_url_fopen` enabled)
- Verify `openssl` PHP extension is installed
- Check if API rate limit is reached (wait and retry)
- Cached rates will be used automatically when APIs are down

### CSS/JS not loading

- Verify `APP_URL` matches your install path
- Check browser console for 404 errors
- Clear browser cache

### Blank page or 500 error

- Check Apache/PHP error logs
- Never enable `DEBUG_MODE` on production
- Ensure all PHP files uploaded completely

### Chinese characters not displaying

- Database must use `utf8mb4` charset (included in schema)
- Ensure HTML charset is UTF-8 (already set in layout)

## Security Notes

- **Never commit** `includes/config.local.php` — it is listed in `.gitignore`
- Use `includes/database.example.php` as a template only (no real passwords)
- Keep `DEBUG_MODE` set to `false` in production
- Enable `FORCE_HTTPS` when SSL is available
- The `includes/` directory is blocked via `.htaccess`

## License

This project is licensed under the [MIT License](LICENSE).
