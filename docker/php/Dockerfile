# Use a imagem oficial do PHP 8.2 com FPM e base Alpine
FROM php:8.2-fpm-alpine

# Define o diretório de trabalho dentro do contêiner
WORKDIR /var/www/html

# --- CORREÇÃO APLICADA AQUI ---
# Passo 1: Instalar as dependências do sistema operacional ANTES de instalar as extensões do PHP.
# Adicionamos as bibliotecas de desenvolvimento (-dev) que as extensões do PHP precisam para compilar.
RUN apk add --no-cache \
    curl-dev \
    libpng-dev \
    jpeg-dev \
    freetype-dev \
    libzip-dev

# Passo 2: Agora, com as bibliotecas no lugar, instalar as extensões do PHP.
RUN docker-php-ext-install pdo pdo_mysql curl gd zip

# --- FIM DA CORREÇÃO ---

# Instala o Composer (gerenciador de dependências do PHP) globalmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apk add --no-cache $PHPIZE_DEPS && pecl install redis && docker-php-ext-enable redis

# Expõe a porta padrão do PHP-FPM
EXPOSE 9000