# GitHub Setup Guide

## Initial Setup

### 1. Initialize Git Repository

```bash
# Navigate to project directory
cd c:\web-projects\pembina

# Initialize git (if not already done)
git init

# Add all files
git add .

# Create initial commit
git commit -m "Initial commit - Pembina Pint E-Commerce Platform"
```

### 2. Add Remote Repository

```bash
# Add GitHub remote
git remote add origin https://github.com/teelake/pembina.git

# Verify remote
git remote -v
```

### 3. Push to GitHub

```bash
# Push to main branch
git branch -M main
git push -u origin main
```

## Important Notes

### Files NOT Committed (in .gitignore):
- ✅ `app/config/database.php` - Contains sensitive database credentials
- ✅ `php-error.log` - Error logs
- ✅ `vendor/` - Composer dependencies (if using)
- ✅ Uploaded files in `public/uploads/`

### Files That ARE Committed:
- ✅ `app/config/database.example.php` - Example configuration
- ✅ All source code
- ✅ Database schema
- ✅ Documentation

## After Pushing to GitHub

### On Your Server:

1. **Clone the repository:**
```bash
cd /var/www/html  # or your web root
git clone https://github.com/teelake/pembina.git
```

2. **Create database configuration:**
```bash
cd pembina
cp app/config/database.example.php app/config/database.php
# Edit database.php with your server credentials
```

3. **Set permissions:**
```bash
chmod -R 755 .
chmod -R 775 public/uploads
chmod -R 775 logs
```

4. **Import database:**
```bash
mysql -u username -p database_name < database/schema.sql
```

## Updating from GitHub

### On Your Local Machine:

```bash
# Make changes
# ... edit files ...

# Commit changes
git add .
git commit -m "Description of changes"

# Push to GitHub
git push origin main
```

### On Your Server:

```bash
cd /path/to/pembina
git pull origin main
```

**⚠️ Always backup before pulling updates!**

## Branch Strategy (Optional)

For production deployments, consider using branches:

```bash
# Create development branch
git checkout -b development

# Work on development
# ... make changes ...

# Merge to main when ready
git checkout main
git merge development
git push origin main
```

## Security Reminders

1. **Never commit:**
   - Database passwords
   - API keys
   - SSL certificates
   - Personal information

2. **Always use:**
   - `.gitignore` for sensitive files
   - Example files (`.example.php`) for configuration templates
   - Environment variables for sensitive data (if implementing)

3. **Review before committing:**
   ```bash
   git status
   git diff
   ```

## Troubleshooting

### "Repository not found"
- Check repository URL is correct
- Verify you have access to the repository
- Check authentication (username/password or SSH key)

### "Permission denied"
- Check file permissions on server
- Verify git user has write access
- Check SSH key is set up correctly

### "Merge conflicts"
- Resolve conflicts manually
- Use `git status` to see conflicted files
- Edit files to resolve conflicts
- Commit resolved files

## Next Steps

After pushing to GitHub:

1. ✅ Set up server deployment (see DEPLOYMENT.md)
2. ✅ Configure production database
3. ✅ Set up SSL certificate
4. ✅ Configure payment gateway
5. ✅ Test all functionality
6. ✅ Set up backups

---

**Repository:** https://github.com/teelake/pembina.git

