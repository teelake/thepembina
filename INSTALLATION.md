# Installation Guide

## Prerequisites

- PHP >= 7.4
- MySQL >= 5.7 or MariaDB >= 10.2
- Apache with mod_rewrite enabled
- Composer (optional, for autoloading)

## Step 1: Database Setup

1. Create a new MySQL database:
```sql
CREATE DATABASE pembina_pint CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import the database schema:
```bash
mysql -u username -p pembina_pint < database/schema.sql
```

## Step 2: Configuration

1. Copy the database configuration file:
```bash
cp app/config/database.example.php app/config/database.php
```

2. Edit `app/config/database.php` with your database credentials:
```php
return [
    'host' => 'localhost',
    'dbname' => 'pembina_pint',
    'username' => 'your_username',
    'password' => 'your_password',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
```

3. Update `app/config/config.php` if needed (timezone, email settings, etc.)

## Step 3: File Permissions

Set proper permissions:
```bash
chmod -R 755 public/uploads
chmod -R 755 logs
```

## Step 4: Web Server Configuration

### Apache Configuration

Point your document root to the `public` directory:

```apache
<VirtualHost *:80>
    ServerName pembinapint.local
    DocumentRoot /path/to/pembina/public
    
    <Directory /path/to/pembina/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name pembinapint.local;
    root /path/to/pembina/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## Step 5: Create First Admin User

You can create the first admin user directly in the database:

```sql
INSERT INTO users (first_name, last_name, email, password, role_id, status, email_verified)
VALUES (
    'Admin',
    'User',
    'admin@pembinapint.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    1, -- Super Admin role
    'active',
    1
);
```

**Important:** Change the password immediately after first login!

## Step 6: Configure Payment Gateway (Square)

1. Log in to the admin panel
2. Go to Settings > Payment Settings
3. Enter your Square credentials:
   - Application ID
   - Access Token
   - Location ID
   - Enable/Disable Sandbox mode

## Step 7: Configure Tax Rates

Tax rates are pre-configured for all Canadian provinces. You can adjust them in:
- Admin Panel > Settings > Tax Settings

## Step 8: Upload Logo

Place your logo file at:
```
public/images/logo.png
```

## Troubleshooting

### 500 Error
- Check file permissions
- Enable error display in `app/config/config.php` (set `APP_ENV` to 'development')
- Check Apache/Nginx error logs

### Database Connection Error
- Verify database credentials in `app/config/database.php`
- Ensure MySQL service is running
- Check database user permissions

### Routes Not Working
- Ensure mod_rewrite is enabled (Apache)
- Check `.htaccess` file exists
- Verify web server configuration

## Next Steps

1. Import menu items from Excel (Admin > Products > Import)
2. Configure business settings (Admin > Settings)
3. Customize pages (Admin > Pages)
4. Test payment gateway
5. Set up email notifications

## Support

For issues or questions, contact: info@webspace.ng

