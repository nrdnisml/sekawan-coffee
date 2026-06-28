FROM alpine:3.19

# Install MariaDB (MySQL compatible) and lightweight tools
RUN apk add --no-cache mariadb mariadb-client

# Setup required directories and permissions
RUN mkdir -p /var/run/mysqld /var/lib/mysql && \
    chown -R mysql:mysql /var/run/mysqld /var/lib/mysql

# Expose the default MySQL port
EXPOSE 3306

# Initialize database and start the server
# The custom entrypoint bootstraps root/database/app user only on first init.
COPY docker/mysql/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER mysql
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
