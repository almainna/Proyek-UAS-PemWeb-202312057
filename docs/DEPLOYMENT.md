# 🚀 Deployment Guide - Klinik Alma Sehat

**Proyek Ujian Akhir Semester - Pemrograman Web**  
**Alma Inna (202312057) - Sekolah Tinggi Teknologi Bontang**  
**Email: flowerrdaaisy@gmail.com**

---

## System Requirements

### Server Requirements
- **Web Server:** Apache 2.4+ atau Nginx 1.18+
- **PHP:** Version 7.4 atau lebih tinggi
- **Database:** MySQL 5.7+ atau MariaDB 10.5+
- **Memory:** Minimum 1GB RAM
- **Storage:** Minimum 500MB disk space

### PHP Extensions Required
```bash
# Required PHP extensions
php-pdo
php-pdo-mysql
php-session
php-json
php-mbstring
php-openssl
php-curl
php-gd
```

## Local Development Setup

### 1. XAMPP Installation (Windows)
```bash
# Download XAMPP dari https://www.apachefriends.org/
# Install dan jalankan Apache + MySQL
```

### 2. Project Setup
```bash
# Clone project ke htdocs
cd C:\xampp\htdocs\
git clone <repository-url> alma
cd alma
```

### 3. Database Setup
```bash
# Buka phpMyAdmin (http://localhost/phpmyadmin)
# Buat database baru: klinik_alma
# Import file: database/klinik_alma.sql
```

### 4. Configuration
```php
// Edit config/database.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'klinik_alma');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 5. Access Application
```
http://localhost/alma/
```

## Production Deployment

### 1. Server Preparation

#### Ubuntu/Debian
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Apache
sudo apt install apache2 -y

# Install PHP 7.4+
sudo apt install php7.4 php7.4-mysql php7.4-mbstring php7.4-xml php7.4-curl php7.4-gd -y

# Install MySQL
sudo apt install mysql-server -y
```

#### CentOS/RHEL
```bash
# Update system
sudo yum update -y

# Install Apache
sudo yum install httpd -y

# Install PHP 7.4+
sudo yum install php php-mysql php-mbstring php-xml php-curl php-gd -y

# Install MySQL
sudo yum install mysql-server -y
```

### 2. Web Server Configuration

#### Apache Configuration
```apache
# /etc/apache2/sites-available/alma.conf
<VirtualHost *:80>
    ServerName alma.yourdomain.com
    DocumentRoot /var/www/html/alma
    
    <Directory /var/www/html/alma>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/alma_error.log
    CustomLog ${APACHE_LOG_DIR}/alma_access.log combined
</VirtualHost>
```

```bash
# Enable site
sudo a2ensite alma.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
```

#### Nginx Configuration
```nginx
# /etc/nginx/sites-available/alma
server {
    listen 80;
    server_name alma.yourdomain.com;
    root /var/www/html/alma;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
}
```

### 3. Database Setup (Production)

```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
mysql -u root -p
```

```sql
CREATE DATABASE klinik_alma CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'alma_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON klinik_alma.* TO 'alma_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

```bash
# Import schema
mysql -u alma_user -p klinik_alma < database/klinik_alma.sql
```

### 4. File Permissions

```bash
# Set proper ownership
sudo chown -R www-data:www-data /var/www/html/alma/

# Set proper permissions
sudo find /var/www/html/alma/ -type d -exec chmod 755 {} \;
sudo find /var/www/html/alma/ -type f -exec chmod 644 {} \;

# Make specific directories writable
sudo chmod -R 775 /var/www/html/alma/uploads/
sudo chmod -R 775 /var/www/html/alma/logs/
```

### 5. Security Configuration

#### Environment Variables
```php
// Create config/environment.php
<?php
return [
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'name' => $_ENV['DB_NAME'] ?? 'klinik_alma',
        'user' => $_ENV['DB_USER'] ?? 'alma_user',
        'pass' => $_ENV['DB_PASS'] ?? 'your_password'
    ],
    'app' => [
        'debug' => $_ENV['APP_DEBUG'] ?? false,
        'url' => $_ENV['APP_URL'] ?? 'https://alma.yourdomain.com'
    ]
];
```

#### SSL Certificate (Let's Encrypt)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache -y

# Get SSL certificate
sudo certbot --apache -d alma.yourdomain.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

## Monitoring and Maintenance

### Log Monitoring
```bash
# Apache logs
sudo tail -f /var/log/apache2/alma_error.log
sudo tail -f /var/log/apache2/alma_access.log

# MySQL logs
sudo tail -f /var/log/mysql/error.log
```

### Performance Monitoring
```bash
# Check server resources
htop
df -h
free -m

# MySQL performance
mysql -u root -p -e "SHOW PROCESSLIST;"
mysql -u root -p -e "SHOW STATUS LIKE 'Slow_queries';"
```

### Backup Strategy
```bash
#!/bin/bash
# backup.sh - Daily backup script

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/alma"
DB_NAME="klinik_alma"
DB_USER="alma_user"
DB_PASS="alma_password"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_$DATE.sql

# Files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/html/alma/

# Keep only last 7 days
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

```bash
# Add to crontab
sudo crontab -e
# Add: 0 2 * * * /path/to/backup.sh
```

## Troubleshooting

### Common Issues

#### 1. Database Connection Error
```bash
# Check MySQL service
sudo systemctl status mysql

# Check database credentials
mysql -u alma_user -p klinik_alma
```

#### 2. Permission Denied
```bash
# Fix file permissions
sudo chown -R www-data:www-data /var/www/html/alma/
sudo chmod -R 755 /var/www/html/alma/
```

#### 3. PHP Errors
```bash
# Check PHP error log
sudo tail -f /var/log/apache2/error.log

# Enable PHP error reporting (development only)
# Add to php.ini:
display_errors = On
error_reporting = E_ALL
```

## Performance Optimization

### PHP Configuration
```ini
# php.ini optimizations
memory_limit = 256M
max_execution_time = 60
upload_max_filesize = 10M
post_max_size = 10M
session.gc_maxlifetime = 3600
opcache.enable = 1
opcache.memory_consumption = 128
```

### MySQL Optimization
```sql
-- Add indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_bookings_patient_id ON bookings(patient_id);
CREATE INDEX idx_transactions_patient_id ON transactions(patient_id);
```

### Apache Optimization
```apache
# Enable compression
LoadModule deflate_module modules/mod_deflate.so
<Location />
    SetOutputFilter DEFLATE
    SetEnvIfNoCase Request_URI \
        \.(?:gif|jpe?g|png)$ no-gzip dont-vary
    SetEnvIfNoCase Request_URI \
        \.(?:exe|t?gz|zip|bz2|sit|rar)$ no-gzip dont-vary
</Location>

# Enable caching
LoadModule expires_module modules/mod_expires.so
ExpiresActive On
ExpiresByType text/css "access plus 1 month"
ExpiresByType application/javascript "access plus 1 month"
ExpiresByType image/png "access plus 1 month"
ExpiresByType image/jpg "access plus 1 month"
ExpiresByType image/jpeg "access plus 1 month"
```

## Security Best Practices

### 1. File Protection
```apache
# .htaccess in config folder
Order Deny,Allow
Deny from all
```

### 2. Database Security
```sql
-- Remove test databases
DROP DATABASE IF EXISTS test;

-- Secure user accounts
DELETE FROM mysql.user WHERE User='';
FLUSH PRIVILEGES;
```

### 3. Regular Updates
```bash
# Keep system updated
sudo apt update && sudo apt upgrade

# Update application dependencies
composer update (if using Composer)
```

## Deployment Checklist

Before going live:

- [ ] Database configured and tested
- [ ] File permissions set correctly
- [ ] Error logging enabled
- [ ] Backup system configured
- [ ] SSL certificate installed
- [ ] Performance monitoring setup
- [ ] Security configurations applied
- [ ] Default passwords changed
- [ ] Testing completed

## Support

For deployment issues:

1. Check server logs
2. Verify system requirements
3. Test database connections
4. Validate file permissions
5. Contact system administrator if needed
