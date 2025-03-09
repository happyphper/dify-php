FROM php:8.2-alpine

# 安装必要的 PHP 扩展
RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip

# 安装 Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 安装 PHPUnit
RUN curl -LO https://phar.phpunit.de/phpunit-9.phar \
    && chmod +x phpunit-9.phar \
    && mv phpunit-9.phar /usr/local/bin/phpunit

# 设置工作目录
WORKDIR /app

# 默认命令
CMD ["php", "-v"] 