FROM mysql:8.4

ARG MYSQL_SANDBOX_DATABASE

COPY docker/mysql-sandbox/init.sql.template /tmp/init.sql.template

RUN sed -i "s/__MYSQL_SANDBOX_DATABASE__/${MYSQL_SANDBOX_DATABASE}/g" /tmp/init.sql.template && \
    mv /tmp/init.sql.template /docker-entrypoint-initdb.d/init.sql
