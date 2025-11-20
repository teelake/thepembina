# Quick Start Guide - Local Development Server

## Option 1: Install PHP Standalone (Recommended)

### Step 1: Download PHP
1. Go to https://windows.php.net/download/
2. Download PHP 7.4 or higher (Thread Safe version)
3. Extract to `C:\php` (or any folder you prefer)

### Step 2: Add PHP to PATH
1. Press `Win + X` and select "System"
2. Click "Advanced system settings"
3. Click "Environment Variables"
4. Under "System variables", find "Path" and click "Edit"
5. Click "New" and add: `C:\php` (or your PHP folder path)
6. Click OK on all dialogs

### Step 3: Start the Server
1. Open Command Prompt or PowerShell in this project folder
2. Run: `php -S localhost:8000 -t public`
3. Or double-click `START_SERVER.bat`

### Step 4: Access the Site
Open your browser and go to: **http://localhost:8000**

---

## Option 2: Use Laragon (Lightweight Alternative to XAMPP)

1. Download Laragon from: https://laragon.org/download/
2. Install Laragon
3. Copy this project to Laragon's `www` folder (usually `C:\laragon\www\pembina`)
4. Start Laragon
5. Access at: **http://pembina.test** (or http://localhost/pembina)

---

## Option 3: Use WAMP Server

1. Download WAMP from: https://www.wampserver.com/
2. Install WAMP
3. Copy this project to `C:\wamp64\www\pembina`
4. Start WAMP
5. Access at: **http://localhost/pembina**

---

## Database Setup (Required Before Testing)

Before you can test the store, you need to:

1. **Install MySQL/MariaDB** (if not already installed)
   - Or use Laragon/WAMP which includes MySQL

2. **Create Database:**
   ```sql
   CREATE DATABASE pembina_pint CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Import Schema:**
   ```bash
   mysql -u root -p pembina_pint < database/schema.sql
   ```

4. **Configure Database:**
   - Edit `app/config/database.php`
   - Update with your MySQL credentials

---

## Quick Test (Without Database)

If you just want to see the frontend design:

1. Start PHP server: `php -S localhost:8000 -t public`
2. Visit: http://localhost:8000
3. You'll see errors for database connection, but you can see the layout/design

---

## Troubleshooting

### "PHP is not recognized"
- PHP is not installed or not in PATH
- Follow Option 1 above to install PHP

### "Database connection failed"
- Make sure MySQL is running
- Check `app/config/database.php` credentials
- Ensure database `pembina_pint` exists

### "Page not found" or routing errors
- Make sure you're accessing via `public` folder
- Check that `.htaccess` file exists
- For PHP built-in server, routing should work automatically

---

## Recommended: Laragon

For easiest setup, I recommend **Laragon**:
- ✅ Lightweight (much smaller than XAMPP)
- ✅ Includes PHP, MySQL, Apache
- ✅ Auto virtual hosts
- ✅ Easy to use
- ✅ Perfect for local development

Download: https://laragon.org/download/

