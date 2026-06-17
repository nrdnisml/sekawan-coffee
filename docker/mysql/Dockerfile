FROM alpine:3.19

# Install MariaDB (MySQL compatible) and lightweight tools
RUN apk add --no-cache mariadb mariadb-client

# Setup required directories and permissions
RUN mkdir -p /var/run/mysqld /var/lib/mysql && \
    chown -R mysql:mysql /var/run/mysqld /var/lib/mysql

# Expose the default MySQL port
EXPOSE 3306

# Initialize database and start the server
# We use a script to ensure the database is initialized if the volume is empty
RUN echo '#!/bin/sh' > /usr/local/bin/entrypoint.sh && \
    echo 'if [ ! -d "/var/lib/mysql/mysql" ]; then' >> /usr/local/bin/entrypoint.sh && \
    echo '    echo "Initializing database..."' >> /usr/local/bin/entrypoint.sh && \
    echo '    mysql_install_db --user=mysql --datadir=/var/lib/mysql > /dev/null' >> /usr/local/bin/entrypoint.sh && \
    echo 'fi' >> /usr/local/bin/entrypoint.sh && \
    echo 'exec mysqld --user=mysql --skip-name-resolve --skip-networking=0 --bind-address=0.0.0.0 "$@"' >> /usr/local/bin/entrypoint.sh && \
    chmod +x /usr/local/bin/entrypoint.sh

USER mysql
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
