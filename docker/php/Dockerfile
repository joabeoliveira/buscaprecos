# Use a imagem oficial do PHP 8.2 com FPM e base Alpine
FROM php:8.2-fpm-alpine

# Define o diretório de trabalho dentro do contêiner
WORKDIR /var/www/app

# Passo 1: Instalar as dependências do sistema operacional ANTES de instalar as extensões do PHP.
RUN apk add --no-cache \
    curl-dev \
    libpng-dev \
    jpeg-dev \
    freetype-dev \
    libzip-dev

# Passo 2: Agora, com as bibliotecas no lugar, instalar as extensões do PHP.
RUN docker-php-ext-install pdo pdo_mysql curl gd zip

# Instala o Composer (gerenciador de dependências do PHP) globalmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copia os arquivos do Composer da raiz do projeto para o diretório de trabalho
# A instrução 'COPY . .' no final copiará o resto dos arquivos, incluindo estes.
# Fazer isso em duas etapas permite que o Docker use o cache de forma mais eficiente.
COPY ../composer.json ../composer.lock ./

# Instala as dependências do Composer, ignorando as de desenvolvimento para uma imagem menor
RUN composer install --no-dev --optimize-autoloader

# Copia o restante dos arquivos do projeto
COPY . .

# Expõe a porta padrão do PHP-FPM
EXPOSE 9000