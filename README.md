# The Pembina Pint and Restaurant - E-Commerce Platform

A modern, secure, and scalable e-commerce website for The Pembina Pint and Restaurant, specializing in authentic African and Nigerian cuisine.

**Live Website:** https://thepembina.ca  
**GitHub Repository:** https://github.com/teelake/pembina.git

## 🚀 Features

### Customer-Facing Store
- ✅ Modern, responsive design with Tailwind CSS
- ✅ Menu browsing with categories
- ✅ Product detail pages
- ✅ Shopping cart functionality
- ✅ Guest checkout support
- ✅ Pickup and Delivery options
- ✅ Canadian tax calculation (GST/PST/HST by province)
- ✅ SEO-optimized pages

### Admin Dashboard
- ✅ Role-based access control (Super Admin, Admin, Data Entry, Customer)
- ✅ Dashboard with statistics
- ✅ Product management (CRUD)
- ✅ Category management
- ✅ Order management
- ✅ User management
- ✅ Content management (editable pages)
- ✅ Settings configuration
- ✅ Audit trail logging

### Payment & Orders
- ✅ Square payment gateway integration
- ✅ Extensible payment gateway architecture
- ✅ Order processing (Pickup/Delivery)
- ✅ DoorDash integration ready
- ✅ Payment status tracking

### Security
- ✅ CSRF protection
- ✅ XSS prevention
- ✅ Rate limiting
- ✅ Input validation
- ✅ SQL injection prevention
- ✅ Secure password hashing
- ✅ Session security

## 📋 Requirements

- PHP >= 7.4
- MySQL >= 5.7 or MariaDB >= 10.2
- Apache with mod_rewrite enabled (or Nginx)
- SSL Certificate (for production)

## 🛠️ Installation

See [INSTALLATION.md](INSTALLATION.md) for detailed installation instructions.

### Quick Start

1. **Clone the repository:**
```bash
git clone https://github.com/teelake/pembina.git
cd pembina
```

2. **Configure database:**
   - Copy `app/config/database.example.php` to `app/config/database.php`
   - Update with your database credentials

3. **Import database schema:**
```bash
mysql -u username -p database_name < database/schema.sql
```

4. **Set permissions:**
```bash
chmod -R 755 public/uploads
chmod -R 755 logs
```

5. **Configure web server** to point to the `public` directory

## 📁 Project Structure

```
pembina/
├── app/
│   ├── config/          # Configuration files
│   ├── core/            # Core framework classes
│   │   ├── Payment/     # Payment gateway classes
│   │   └── Security/     # Security classes
│   ├── controllers/     # Controllers
│   │   └── Admin/       # Admin controllers
│   ├── models/          # Models
│   └── views/           # Views
│       ├── layouts/     # Layout templates
│       ├── admin/       # Admin views
│       └── ...
├── database/            # Database files
│   └── schema.sql       # Database schema
├── public/              # Public assets
│   ├── css/             # Stylesheets
│   ├── js/              # JavaScript
│   ├── images/          # Images
│   └── uploads/         # Uploaded files
├── logs/                # Log files
├── .htaccess           # Apache configuration
├── index.php           # Application entry point
└── README.md          # This file
```

## 🔐 Default Admin Credentials

After installation, create your first admin user through the database:

```sql
INSERT INTO users (first_name, last_name, email, password, role_id, status, email_verified)
VALUES (
    'Admin',
    'User',
    'admin@thepembina.ca',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    1, -- Super Admin
    'active',
    1
);
```

**⚠️ IMPORTANT:** Change the password immediately after first login!

## 🌐 Deployment

See [DEPLOYMENT.md](DEPLOYMENT.md) for detailed deployment instructions.

### Quick Deployment Steps:

1. Push to GitHub
2. Clone on server
3. Configure database
4. Set file permissions
5. Configure web server
6. Set up SSL certificate
7. Configure payment gateway

## 🔧 Configuration

### Environment Settings

Edit `app/config/config.php`:
- Set `APP_ENV` to `production` for live site
- Configure timezone
- Update business information

### Payment Gateway

1. Log in to admin panel
2. Go to Settings > Payment Settings
3. Enter Square credentials:
   - Application ID
   - Access Token
   - Location ID

### Tax Rates

Tax rates are pre-configured for all Canadian provinces. Adjust in:
- Admin Panel > Settings > Tax Settings

## 📝 Documentation

- [INSTALLATION.md](INSTALLATION.md) - Installation guide
- [DEPLOYMENT.md](DEPLOYMENT.md) - Deployment guide
- [QUICK_START.md](QUICK_START.md) - Quick start for local development
- [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md) - Project overview

## 🛡️ Security Features

- CSRF token protection on all forms
- XSS prevention with output escaping
- Rate limiting to prevent abuse
- SQL injection prevention with prepared statements
- Secure password hashing (bcrypt)
- Session security (httponly, secure cookies)
- Input validation and sanitization
- Audit trail for all user actions

## 📊 Admin Features

- **Dashboard:** Statistics, recent orders, top products
- **Products:** Full CRUD, image upload, inventory management
- **Categories:** Hierarchical category management
- **Orders:** View, update status, process orders
- **Users:** User management with role assignment
- **Pages:** Content management with TinyMCE editor
- **Settings:** System configuration, payment, tax settings

## 🎨 Design

- Modern, clean interface
- Brand colors (warm yellow-orange/mustard)
- Responsive design (mobile-friendly)
- Tailwind CSS framework
- Font Awesome icons

## 🔄 Updates

To update from GitHub:

```bash
cd /path/to/pembina
git pull origin main
```

**Always backup before updating!**

## 📞 Support

**Developed by:** [Webspace](https://www.webspace.ng)  
**Email:** info@webspace.ng

---

**The Pembina Pint and Restaurant**  
282 Loren Drive, Morden, Manitoba, Canada

## 📄 License

MIT License

---

**Status:** Production Ready ✅  
**Version:** 1.0.0  
**Last Updated:** 2024
