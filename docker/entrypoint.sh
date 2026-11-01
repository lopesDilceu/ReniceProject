#!/bin/sh
# Prepares the app with the runtime environment, then runs the role's command
# (apache2-foreground, or an artisan command for queue/scheduler/reverb).
set -e

# Config, routes, views and events cached from the real environment variables.
php artisan optimize

# Only one container should migrate: the web role sets RUN_MIGRATIONS=true.
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

exec "$@"
