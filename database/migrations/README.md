# Database Migrations

## Navigation Management Migration

### Description
This migration adds navigation management fields to the `categories` table, allowing admins to control which categories appear in the main website navigation.

### Fields Added
- `show_in_nav` (tinyint): Whether to show category in main navigation (0=no, 1=yes)
- `nav_order` (int): Order in navigation (lower numbers appear first)

### How to Run

**Option 1: Via MySQL Command Line**
```bash
mysql -u your_username -p your_database_name < database/migrations/add_navigation_fields_to_categories.sql
```

**Option 2: Via phpMyAdmin or Database Tool**
1. Open phpMyAdmin or your database management tool
2. Select your database
3. Go to the SQL tab
4. Copy and paste the contents of `database/migrations/add_navigation_fields_to_categories.sql`
5. Click "Go" or "Execute"

**Option 3: Via cPanel MySQL**
1. Log into cPanel
2. Go to "MySQL Databases"
3. Click on "phpMyAdmin"
4. Select your database
5. Click on the "SQL" tab
6. Paste the migration SQL
7. Click "Go"

### What Happens
- Adds two new columns to the `categories` table
- Automatically sets the top 3 active categories (by sort_order) to show in navigation
- Sets their `nav_order` to match their `sort_order`

### After Migration
1. Go to Admin → Categories
2. Edit any category
3. You'll see a new "Navigation Settings" section
4. Check "Show in Main Navigation" and set "Navigation Order" for categories you want in the header
5. Only the top 3 categories (by nav_order) will appear in the main navigation

### Notes
- Maximum 3 categories will appear in main navigation
- Categories are ordered by `nav_order` (ascending), then `sort_order`, then name
- If no categories are marked for navigation, the system falls back to the top 3 by `sort_order`

