FROM php:8.4-cli-alpine AS php_base

LABEL maintainer="Ulrich Geraud AHOGLA <developer@osirisgate.com>"

FROM php_base AS php_with_dependencies

RUN apk --no-cache add --update \
        sudo \
        linux-headers \
        bash \
        autoconf \
        g++ \
        make \
        curl \
        git \
        python3 \
        py3-pip

FROM composer:2.8.6 AS final

COPY --from=php_with_dependencies /usr/local/lib/php /usr/local/lib/php
COPY --from=php_with_dependencies /usr/local/etc/php /usr/local/etc/php

WORKDIR /var/www/html
CMD ["tail", "-f", "/dev/null"]
