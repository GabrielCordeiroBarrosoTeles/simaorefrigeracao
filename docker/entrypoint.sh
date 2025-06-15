#!/bin/sh

# Aguardar o MySQL iniciar
echo "Aguardando o MySQL..."
while ! nc -z db 3306; do
  sleep 1
done
echo "MySQL iniciado"

# Instalar dependências do Composer se vendor não existir
if [ ! -d "vendor" ]; then
  echo "Instalando dependências do Composer..."
  composer install
fi

# Executar migrations
echo "Executando migrations..."
php vendor/bin/doctrine-migrations migrations:migrate --no-interaction

# Iniciar o PHP-FPM
php-fpm