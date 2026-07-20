# Environment Setup — Worker Prompt (Copy & Paste)

## Step 1: PHP Extensions

```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install -y \
    php-zip \
    php-mbstring \
    php-openssl \
    php-mysql \
    php-fileinfo \
    php-curl \
    php-xml \
    unzip \
    curl \
    git \
    mysql-client

# Verify
php -m | grep -E 'mbstring|openssl|pdo_mysql|fileinfo|zip|curl'
```

## Step 2: Composer Packages

```bash
cd /path/to/project
composer require webklex/php-imap
composer require spatie/laravel-activitylog
composer require laravel/ui  # optional, auth scaffolding
```

## Step 3: SnappyMail Core

```bash
cd public
wget https://snappymail.eu/release/latest.zip
unzip -o latest.zip -d webmail
rm latest.zip

# Ensure data directory is writable
chmod -R 775 webmail/data
chown -R www-data:www-data webmail/data
```

## Step 4: SnappyMail Plugins (verify)

```bash
ls -la public/webmail/plugins/
# Expected: at least default plugins + place for custom ones
```

## Step 5: Node/NPM (optional, for frontend build)

```bash
node -v   # expect v18+
npm -v    # expect 9+
# If missing:
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs
```

## Step 6: Final Verification

```bash
php -v
php -m | grep -E 'mbstring|openssl|pdo_mysql|fileinfo|zip|curl'
mysql --version
git --version
curl --version
composer --version
node -v
npm -v
```

## Evidence to Capture

Paste ALL of the following into your response:

1. Output of `php -m`
2. Output of `composer show webklex/php-imap` and `composer show spatie/laravel-activitylog`
3. Output of `ls -la public/webmail/`
4. Output of `ls public/webmail/plugins/`
5. Output of `node -v && npm -v` (or "N/A" if skipped)
6. Output of final verification block
