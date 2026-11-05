#!/bin/bash
set -e

# Run initial vhost generation
/scripts/generate-vhosts.sh

# Function to monitor directory changes in background
start_watcher() {
    (
        LAST_STATE=""
        while true; do
            # Compute lightweight fingerprint of project directories and .php-version files
            CURRENT_STATE=$(find /var/www/html -maxdepth 2 -name ".php-version" -o -maxdepth 1 -type d 2>/dev/null | sort | md5sum | awk '{print $1}')
            
            if [ "$CURRENT_STATE" != "$LAST_STATE" ] && [ -n "$LAST_STATE" ]; then
                echo "[LocalDevServer] Detected project change in /var/www/html. Updating VirtualHosts..."
                /scripts/generate-vhosts.sh
                if httpd -t >/dev/null 2>&1; then
                    httpd -k graceful 2>/dev/null || true
                    echo "[LocalDevServer] VirtualHosts reloaded successfully."
                else
                    echo "[LocalDevServer] Error in Apache configuration. Reload skipped."
                fi
            fi
            
            LAST_STATE="$CURRENT_STATE"
            sleep 2
        done
    ) &
}

# Start the background watcher
start_watcher

# Execute CMD passed to docker container
exec "$@"

