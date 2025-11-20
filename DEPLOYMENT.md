# Deployment Guide - The Pembina Pint E-Commerce Platform

## GitHub Repository Setup

Repository: https://github.com/teelake/pembina.git

## Pre-Deployment Checklist

- [ ] Database schema imported
- [ ] Configuration files updated
- [ ] Environment variables set
- [ ] File permissions configured
- [ ] SSL certificate installed
- [ ] Payment gateway configured

## Step 1: Push to GitHub

```bash
# Initialize git (if not already done)
git init

# Add remote repository
git remote add origin https://github.com/teelake/pembina.git

# Add all files
git add .

# Commit
git commit -m "Initial commit - Pembina Pint E-Commerce Platform"

# Push to GitHub
git push -u origin main
```

## Step 2: Server Requirements

### Minimum Requirements:
- PHP >= 7.4
- MySQL >= 5.7 or MariaDB >= 10.2
- Apache with mod_rewrite enabled
- SSL Certificate (for production)

### PHP Extensions Required:
- PDO
- PDO_MySQL
- mbstring
- openssl
- curl
- gd (for image processing)
- json

## Step 3: Server Configuration

### Apache Configuration

Create or update your virtual host:

```apache
<VirtualHost *:80>
    ServerName thepembina.ca
    ServerAlias www.thepembina.ca
    DocumentRoot /path/to/pembina/public
    
    <Directory /path/to/pembina/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Redirect to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>

<VirtualHost *:443>
    ServerName thepembina.ca
    ServerAlias www.thepembina.ca
    DocumentRoot /path/to/pembina/public
    
    SSLEngine on
    SSLCertificateFile /path/to/ssl/certificate.crt
    SSLCertificateKeyFile /path/to/ssl/private.key
    
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
    server_name thepembina.ca www.thepembina.ca;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name thepembina.ca www.thepembina.ca;
    root /path/to/pembina/public;
    index index.php;

    ssl_certificate /path/to/ssl/certificate.crt;
    ssl_certificate_key /path/to/ssl/private.key;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }
}
```

## Step 4: Deploy to Server

### Option A: Git Clone (Recommended)

```bash
# SSH into your server
ssh user@your-server.com

# Navigate to web root
cd /var/www/html  # or your web root directory

# Clone repository
git clone https://github.com/teelake/pembina.git

# Set permissions
chmod -R 755 pembina
chmod -R 775 pembina/public/uploads
chmod -R 775 pembina/logs
```

### Option B: Upload via FTP/SFTP

1. Upload all files to your server
2. Ensure file permissions are correct

## Step 5: Database Setup

```bash
# SSH into server
ssh user@your-server.com

# Access MySQL
mysql -u root -p

# Create database
CREATE DATABASE pembina_pint CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create user (recommended)
CREATE USER 'pembina_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON pembina_pint.* TO 'pembina_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import schema
mysql -u pembina_user -p pembina_pint < /path/to/pembina/database/schema.sql
```

## Step 6: Configuration

### Update Database Configuration

Edit `app/config/database.php`:

```php
return [
    'host' => 'localhost',
    'dbname' => 'pembina_pint',
    'username' => 'pembina_user',
    'password' => 'your_strong_password',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
```

### Update Application Configuration

Edit `app/config/config.php`:

```php
// Change to production
define('APP_ENV', 'production');

// Update timezone if needed
date_default_timezone_set('America/Winnipeg');

// Update session cookie for HTTPS
ini_set('session.cookie_secure', 1);
```

### Update Base URL

The BASE_URL is automatically detected, but you can hardcode it in `index.php` if needed:

```php
define('BASE_URL', 'https://thepembina.ca');
```

## Step 7: File Permissions

```bash
# Set directory permissions
find /path/to/pembina -type d -exec chmod 755 {} \;

# Set file permissions
find /path/to/pembina -type f -exec chmod 644 {} \;

# Special permissions for uploads and logs
chmod -R 775 /path/to/pembina/public/uploads
chmod -R 775 /path/to/pembina/logs

# Make sure PHP can write to these directories
chown -R www-data:www-data /path/to/pembina/public/uploads
chown -R www-data:www-data /path/to/pembina/logs
```

## Step 8: Create First Admin User

```sql
-- Access MySQL
mysql -u pembina_user -p pembina_pint

-- Insert admin user (change password!)
INSERT INTO users (first_name, last_name, email, password, role_id, status, email_verified)
VALUES (
    'Admin',
    'User',
    'admin@thepembina.ca',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    1, -- Super Admin role
    'active',
    1
);
```

**IMPORTANT:** Change the password immediately after first login!

## Step 9: Configure Payment Gateway

1. Log in to admin panel: https://thepembina.ca/admin
2. Go to Settings > Payment Settings
3. Enter Square credentials:
   - Application ID
   - Access Token
   - Location ID
   - Disable Sandbox mode for production

## Step 10: Security Checklist

- [ ] Change default admin password
- [ ] Update `app/config/config.php` to production mode
- [ ] Enable HTTPS (SSL certificate)
- [ ] Set secure session cookies
- [ ] Configure firewall rules
- [ ] Set up regular backups
- [ ] Enable error logging (but hide errors from users)
- [ ] Review file permissions
- [ ] Set up monitoring

## Step 11: Post-Deployment

1. **Test the site:**
   - Visit https://thepembina.ca
   - Test product browsing
   - Test cart functionality
   - Test checkout process
   - Test admin panel

2. **Import menu items:**
   - Log in to admin
   - Go to Products > Import Excel
   - Upload your menu Excel file

3. **Configure settings:**
   - Business information
   - Tax rates (if different from defaults)
   - Delivery settings
   - Email settings

4. **Set up backups:**
   ```bash
   # Database backup script
   mysqldump -u pembina_user -p pembina_pint > backup_$(date +%Y%m%d).sql
   ```

## Troubleshooting

### 500 Internal Server Error
- Check file permissions
- Check PHP error log: `php-error.log`
- Verify .htaccess is working
- Check Apache/Nginx error logs

### Database Connection Error
- Verify database credentials
- Check MySQL service is running
- Verify database exists
- Check user permissions

### Routes Not Working
- Ensure mod_rewrite is enabled (Apache)
- Check .htaccess file exists
- Verify web server configuration
- Check BASE_URL is correct

### Permission Denied
- Check file/directory ownership
- Verify uploads directory is writable
- Check logs directory is writable

## Maintenance

### Regular Tasks:
- Database backups (daily recommended)
- File backups (weekly recommended)
- Update dependencies
- Monitor error logs
- Review security updates

### Update from GitHub:

```bash
cd /path/to/pembina
git pull origin main
```

**Note:** Always backup before updating!

## Support

For deployment issues, contact: info@webspace.ng

---

**Website:** https://thepembina.ca  
**Developed by:** [Webspace](https://www.webspace.ng)

