# MTI_SMS - Total Stock Management System

A comprehensive PHP-based stock management system for college administration.

## Features

- **Role-based Access Control**: STOCK_ADMIN, HOD, DEPT_IN_CHARGE, STAFF
- **User Management**: Add, activate/deactivate users
- **Category Management**: Categories and subcategories
- **Item Register**: Complete inventory management
- **Stock In**: Add stock with audit logs
- **View Stock**: Item-wise and department-wise views
- **Old Stock**: Track damaged/obsolete items
- **Dispatched**: Stock out with dispatch codes
- **Item Requests**: Request and approval workflow

## System Requirements

- PHP 5.5 or higher
- MySQL 5.5 or higher
- Apache/Nginx web server

## Installation

### 1. Database Setup

1. Create a MySQL database named `mti_sms`
2. Import the SQL file:
   ```bash
   mysql -u root -p mti_sms < database/mti_sms.sql
   ```
   Or use phpMyAdmin to import `database/mti_sms.sql`

### 2. Configure Database Connection

Edit `config/database.php` and update the credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'mti_sms');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 3. Deploy to Web Server

Copy all files to your web server's document root (e.g., `htdocs`, `www`, or `public_html`).

### 4. Access the Application

Open your browser and navigate to:
```
http://localhost/mti_sms/
```
or
```
http://your-domain.com/
```

## Demo Credentials

| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | STOCK_ADMIN |
| hod_cs | hod123 | HOD |
| dept_mech | dept123 | DEPT_IN_CHARGE |
| staff1 | staff123 | STAFF |

## File Structure

```
public/
├── index.php              # Login page
├── dashboard.php          # Home/Dashboard
├── users.php              # User management
├── categories.php         # Category management
├── items.php              # Item register
├── stock-in.php           # Stock in operations
├── view-stock.php         # View stock
├── old-stock.php          # Old stock management
├── dispatched.php         # Dispatch/Stock out
├── requests.php           # Item requests
├── logout.php             # Logout handler
├── config/
│   ├── database.php       # Database configuration
│   └── session.php        # Session management
├── includes/
│   ├── header.php         # Common header with sidebar
│   └── footer.php         # Common footer
├── assets/
│   └── css/
│       └── style.css      # Complete stylesheet
├── database/
│   └── mti_sms.sql        # Database schema & data
└── README.md              # This file
```

## Role Permissions

| Feature | STOCK_ADMIN | HOD | DEPT_IN_CHARGE | STAFF |
|---------|-------------|-----|----------------|-------|
| Dashboard | ✓ | ✓ | ✓ | ✓ |
| Add User | ✓ | ✗ | ✗ | ✗ |
| Categories | ✓ | ✓ | ✗ | ✗ |
| Item Register | ✓ | ✓ | ✓ | ✗ |
| Stock In | ✓ | ✗ | ✓ | ✗ |
| View Stock | ✓ | ✓ | ✓ | ✓ |
| Old Stock | ✓ | ✓ | ✓ | ✗ |
| Dispatched | ✓ | ✗ | ✓ | ✗ |
| Item Requests | ✓ | ✓ | ✓ | ✓ |
| Approve Requests | ✓ | ✓ | ✗ | ✗ |

## Security Notes

⚠️ **For Production Use:**

1. Use `password_hash()` for storing passwords
2. Enable HTTPS
3. Implement CSRF protection
4. Use prepared statements (already implemented)
5. Sanitize all user inputs
6. Change default credentials immediately

## Technology Stack

- **Frontend**: HTML5, CSS3, Font Awesome 6.4
- **Backend**: PHP 5.5+
- **Database**: MySQL 5.5+
- **Design**: Navy & Mustard color theme

## License

This project is developed for educational purposes for MTI College.

---

**Version**: 2.0.0 (PHP)
**Last Updated**: 2024
