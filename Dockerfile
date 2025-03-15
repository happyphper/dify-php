FROM php:8.2-cli

# 安装依赖
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip

# 安装Redis扩展
RUN pecl install redis && docker-php-ext-enable redis

# 直接下载安装Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 设置工作目录
WORKDIR /app

# 设置环境变量
ENV COMPOSER_ALLOW_SUPERUSER=1

# 复制composer.json和composer.lock
COPY composer.json composer.lock ./

# 安装依赖
RUN composer install --no-scripts --no-autoloader

# 复制项目文件
COPY . .

# 生成autoloader
RUN composer dump-autoload --optimize

# 设置入口点
ENTRYPOINT ["php"]