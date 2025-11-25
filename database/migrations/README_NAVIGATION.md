# Navigation Menu Migration Guide

## 🎯 Hybrid Navigation System

The navigation system now supports:
- ✅ **Categories** - Link to product categories
- ✅ **Pages** - Link to content pages (About, Contact, etc.)
- ✅ **Custom Links** - Link to any URL (internal or external)

## 📋 Migration Steps

### Step 1: Run the Navigation Menu Table Migration

```bash
mysql -u username -p database_name < database/migrations/create_navigation_menu_table.sql
```

Or via phpMyAdmin:
1. Select your database
2. Go to SQL tab
3. Copy and paste the contents of `database/migrations/create_navigation_menu_table.sql`
4. Click "Go"

### Step 2: Verify Migration

The migration will:
- Create `navigation_menu_items` table
- Automatically migrate existing category-based navigation items
- Set up proper foreign keys

### Step 3: Access Navigation Management

1. Go to **Admin → Navigation**
2. You'll see existing menu items (migrated from categories)
3. Click **"Add Menu Item"** to add new items

## 🎨 How to Use

### Adding a Category Link
1. Click "Add Menu Item"
2. Enter Label (e.g., "Food")
3. Select Type: **Category**
4. Choose category from dropdown
5. Set Order (lower = first)
6. Save

### Adding a Page Link
1. Click "Add Menu Item"
2. Enter Label (e.g., "About Us")
3. Select Type: **Page**
4. Choose page from dropdown
5. Set Order
6. Save

### Adding a Custom Link
1. Click "Add Menu Item"
2. Enter Label (e.g., "Facebook")
3. Select Type: **Custom Link**
4. Enter URL (e.g., "https://facebook.com/thepembina" or "/contact")
5. Choose target (Same Window or New Tab)
6. Set Order
7. Save

## 📝 Features

- **Flexible Ordering**: Set order numbers (0-100) to control display
- **Icons**: Add Font Awesome icons (optional)
- **Link Targets**: Open in same window or new tab
- **Status**: Activate/deactivate menu items
- **Auto-migration**: Existing category navigation is preserved

## 🔄 Migration from Old System

The migration automatically:
- Finds categories marked `show_in_nav = 1`
- Creates menu items for them
- Preserves their order (`nav_order`)

Your existing navigation will continue working!

## ⚠️ Important Notes

- Maximum recommended: 5-7 menu items for best UX
- Categories not in navigation appear in "More" dropdown
- Custom links can be internal (`/page/about`) or external (`https://...`)
- Icons are optional but recommended for better UX

