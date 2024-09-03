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
  wp core install --url="http://localhost:8000" --title="TicketHub" --admin_user="admin" --admin_password="R8RRM4ee!Yga9@69n(" --admin_email="mmanthey@mondula.com" --path=/var/www/html --allow-root
fi

# Array of plugins to install and activate
plugins=(
  "wp-mail-smtp"
  "loco-translate"
  "plugin-check"
  "wp-crontrol"
)

# Install and activate plugins
for plugin in "${plugins[@]}"; do
  if ! wp plugin is-installed "$plugin" --path=/var/www/html --allow-root; then
    wp plugin install "$plugin" --activate --path=/var/www/html --allow-root
  else
    wp plugin activate "$plugin" --path=/var/www/html --allow-root
  fi
done

# Configure WP Mail SMTP
wp config set WPMS_ON true --raw --path=/var/www/html --allow-root
wp config set WPMS_MAILER smtp --path=/var/www/html --allow-root
wp config set WPMS_SMTP_HOST mailhog --path=/var/www/html --allow-root
wp config set WPMS_SMTP_PORT 1025 --raw --path=/var/www/html --allow-root
wp config set WPMS_SMTP_AUTH false --raw --path=/var/www/html --allow-root

exec "$@"
