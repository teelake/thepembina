# Production Configuration Notes

## Database Configuration

**File:** `app/config/database.php` (NOT in Git - create on server)

```php
return [
    'host' => 'localhost',
    'dbname' => 'thepembi_db',
    'username' => 'thepembi_user',
    'password' => 'oY_[}q(KN[CL4(cj',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
```

## Email Configuration

**File:** `app/config/config.php` (Update on server)

Update these lines in `app/config/config.php`:

```php
define('SMTP_HOST', 'mail.thepembina.ca'); // Update if different
define('SMTP_PORT', 587);
define('SMTP_USER', 'no-reply@thepembina.ca');
define('SMTP_PASS', 'Temp_Pass123'); // Add password here
define('SMTP_FROM_EMAIL', 'no-reply@thepembina.ca');
define('SMTP_FROM_NAME', 'The Pembina Pint and Restaurant');
```

## Production Settings

**File:** `app/config/config.php`

Already configured:
- ✅ `APP_ENV` set to `production`
- ✅ `session.cookie_secure` enabled for HTTPS
- ✅ Error display disabled
- ✅ Error logging enabled

## Quick Setup on Server

1. **Clone repository:**
```bash
git clone https://github.com/teelake/pembina.git
cd pembina
```

2. **Create database config:**
```bash
cp app/config/database.example.php app/config/database.php
# Edit database.php with credentials above
```

3. **Update email config:**
```bash
# Edit app/config/config.php
# Add email password: Temp_Pass123
```

4. **Import database:**
```bash
mysql -u thepembi_user -p thepembi_db < database/schema.sql
```

5. **Set permissions:**
```bash
chmod -R 755 .
chmod -R 775 public/uploads
chmod -R 775 logs
```

6. **Test connection:**
- Visit your site
- Check php-error.log for any errors

---

**Note:** Database and email passwords are stored locally and NOT committed to Git for security.

