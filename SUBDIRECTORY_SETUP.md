# Subdirectory Deployment Setup

## Current Setup

Your application is deployed at: `/beta/pembina/`

## Access URLs

The application should be accessed via:
- **Main URL:** https://thepembina.ca/beta/pembina/
- **Public folder:** https://thepembina.ca/beta/pembina/public/

## Web Server Configuration

### Option 1: Point Document Root to `/beta/pembina/public/` (Recommended)

If you can configure your web server, point the document root for this subdirectory to the `public` folder:

**Apache Virtual Host:**
```apache
<VirtualHost *:443>
    ServerName thepembina.ca
    DocumentRoot /path/to/beta/pembina/public
    
    <Directory /path/to/beta/pembina/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    Alias /beta/pembina /path/to/beta/pembina/public
</VirtualHost>
```

Then access via: https://thepembina.ca/beta/pembina/

### Option 2: Use Root index.php (Current Setup)

If you're accessing via `/beta/pembina/`, you need to ensure the root `index.php` is being executed.

**Create/Update `.htaccess` in `/beta/pembina/` folder:**
```apache
RewriteEngine On
RewriteBase /beta/pembina/

# Redirect /beta/pembina/public to /beta/pembina
RewriteCond %{REQUEST_URI} ^/beta/pembina/public/(.*)$
RewriteRule ^public/(.*)$ /beta/pembina/$1 [R=301,L]

# Route all requests to index.php
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### Option 3: Create Redirect in Root

Create a file at `/beta/pembina/index.php` that redirects:

```php
<?php
// Redirect to public folder
header('Location: /beta/pembina/public/');
exit;
```

## Testing

After setup, test these URLs:
1. https://thepembina.ca/beta/pembina/ - Should show homepage
2. https://thepembina.ca/beta/pembina/menu - Should show menu
3. https://thepembina.ca/beta/pembina/admin - Should show admin login

## Troubleshooting

### 404 Errors
- Check that `.htaccess` is in the root folder
- Verify `mod_rewrite` is enabled in Apache
- Check file permissions (755 for directories, 644 for files)

### Base URL Issues
- The BASE_URL is auto-detected from the script path
- Check `php-error.log` for routing errors
- Verify the subdirectory path is correct

### Database Connection
- Verify `app/config/database.php` exists on server
- Check database credentials are correct
- Test database connection separately

## Quick Fix

If you're still getting 404 errors, try accessing:
- https://thepembina.ca/beta/pembina/index.php

If that works, the issue is with `.htaccess` rewrite rules.

