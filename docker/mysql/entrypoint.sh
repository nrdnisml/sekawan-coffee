#!/bin/sh
set -eu

MYSQL_ROOT_PASSWORD="${MYSQL_ROOT_PASSWORD:-}"
MYSQL_DATABASE="${MYSQL_DATABASE:-}"
MYSQL_USER="${MYSQL_USER:-}"
MYSQL_PASSWORD="${MYSQL_PASSWORD:-}"
BOOTSTRAP_SOCKET="/tmp/mysql-bootstrap.sock"

if [ ! -d "/var/lib/mysql/mysql" ]; then
    if [ -z "$MYSQL_ROOT_PASSWORD" ]; then
        echo "MYSQL_ROOT_PASSWORD is required for first-time initialization" >&2
        exit 1
    fi

    if { [ -n "$MYSQL_USER" ] && [ -z "$MYSQL_PASSWORD" ]; } || { [ -z "$MYSQL_USER" ] && [ -n "$MYSQL_PASSWORD" ]; }; then
        echo "MYSQL_USER and MYSQL_PASSWORD must be provided together" >&2
        exit 1
    fi

    echo "Initializing database..."
    mysql_install_db --user=mysql --datadir=/var/lib/mysql --auth-root-authentication-method=normal > /dev/null

    echo "Starting temporary MariaDB server..."
    mysqld --user=mysql --skip-name-resolve --skip-networking --socket="$BOOTSTRAP_SOCKET" --bind-address=127.0.0.1 &
    bootstrap_pid="$!"

    ready=0
    for _ in $(seq 1 60); do
        if mariadb-admin --protocol=socket --socket="$BOOTSTRAP_SOCKET" ping >/dev/null 2>&1; then
            ready=1
            break
        fi
        sleep 1
    done

    if [ "$ready" -ne 1 ]; then
        echo "Temporary MariaDB server failed to start" >&2
        wait "$bootstrap_pid" || true
        exit 1
    fi

    mariadb --protocol=socket --socket="$BOOTSTRAP_SOCKET" -uroot <<EOF
ALTER USER 'root'@'localhost' IDENTIFIED BY '${MYSQL_ROOT_PASSWORD}';
EOF

    if [ -n "$MYSQL_DATABASE" ]; then
        mariadb --protocol=socket --socket="$BOOTSTRAP_SOCKET" -uroot --password="$MYSQL_ROOT_PASSWORD" <<EOF
CREATE DATABASE IF NOT EXISTS \
\`${MYSQL_DATABASE}\`;
EOF
    fi

    if [ -n "$MYSQL_USER" ]; then
        mariadb --protocol=socket --socket="$BOOTSTRAP_SOCKET" -uroot --password="$MYSQL_ROOT_PASSWORD" <<EOF
CREATE USER IF NOT EXISTS '${MYSQL_USER}'@'%' IDENTIFIED BY '${MYSQL_PASSWORD}';
EOF
    fi

    if [ -n "$MYSQL_USER" ] && [ -n "$MYSQL_DATABASE" ]; then
        mariadb --protocol=socket --socket="$BOOTSTRAP_SOCKET" -uroot --password="$MYSQL_ROOT_PASSWORD" <<EOF
GRANT ALL PRIVILEGES ON \
\`${MYSQL_DATABASE}\`.* TO '${MYSQL_USER}'@'%';
FLUSH PRIVILEGES;
EOF
    fi

    mariadb-admin --protocol=socket --socket="$BOOTSTRAP_SOCKET" -uroot --password="$MYSQL_ROOT_PASSWORD" shutdown
    wait "$bootstrap_pid"
fi

exec mysqld --user=mysql --skip-name-resolve --skip-networking=0 --bind-address=0.0.0.0 "$@"
