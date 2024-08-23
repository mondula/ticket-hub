#!/bin/bash
set -euo pipefail

# Ensure WordPress files are present
if ! wp core is-installed --path=/var/www/html --allow-root; then
  wp core download --path=/var/www/html --allow-root
fi

# Create wp-config.php if it doesn't exist
if [ ! -f /var/www/html/wp-config.php ]; then
  wp config create --dbname=wordpress --dbuser=wordpress --dbpass=wordpress --dbhost=db --path=/var/www/html --allow-root
fi

# Wait until the database is ready
until wp db check --path=/var/www/html --allow-root; do
  echo "Waiting for the database to be ready..."
  sleep 3
done

# Install WordPress if not installed
if ! wp core is-installed --path=/var/www/html --allow-root; then
  wp core install --url="http://localhost:8009" --title="WordPress Site" --admin_user="admin" --admin_password="admin_password" --admin_email="admin@example.com" --path=/var/www/html --allow-root
fi

# Install and activate WP Mail SMTP plugin
if ! wp plugin is-installed wp-mail-smtp --path=/var/www/html --allow-root; then
  wp plugin install wp-mail-smtp --activate --path=/var/www/html --allow-root
else
  wp plugin activate wp-mail-smtp --path=/var/www/html --allow-root
fi

# Install and activate Loco Translate plugin
if ! wp plugin is-installed loco-translate --path=/var/www/html --allow-root; then
  wp plugin install loco-translate --activate --path=/var/www/html --allow-root
else
  wp plugin activate loco-translate --path=/var/www/html --allow-root
fi

# Configure WP Mail SMTP
wp config set WP_MAIL_SMTP_HOST mailhog --path=/var/www/html --allow-root
wp config set WP_MAIL_SMTP_PORT 1025 --path=/var/www/html --allow-root
wp config set WP_MAIL_SMTP_AUTH false --path=/var/www/html --allow-root
wp config set WP_MAIL_SMTP_SECURE '' --path=/var/www/html --allow-root

exec "$@"
