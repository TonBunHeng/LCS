FROM httpd:2.4-alpine

# Install bash, curl, and inotify-tools for dynamic vhost watching
RUN apk add --no-cache bash curl inotify-tools

ARG PUID=1000
ARG PGID=1000

# Configure user permissions
RUN sed -i 's/User daemon/User www-data/g' /usr/local/apache2/conf/httpd.conf && \
    sed -i 's/Group daemon/Group www-data/g' /usr/local/apache2/conf/httpd.conf || true

# Create necessary directories
RUN mkdir -p /etc/apache2/vhosts.d \
    /var/log/apache2 \
    /var/www/html \
    /var/www/dashboard \
    /scripts

# Copy Apache configuration
COPY httpd.conf /usr/local/apache2/conf/httpd.conf
COPY vhosts/ /etc/apache2/vhosts.d/

# Copy dashboard
COPY dashboard/ /var/www/dashboard/

# Copy entrypoint and generator scripts
COPY scripts/ /scripts/
RUN chmod +x /scripts/*.sh

EXPOSE 80

ENTRYPOINT ["/scripts/docker-entrypoint.sh"]
CMD ["httpd", "-D", "FOREGROUND"]

