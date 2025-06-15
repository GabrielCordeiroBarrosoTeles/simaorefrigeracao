.PHONY: up down build composer migrate setup-db seed-db shell restart

# Iniciar todos os containers
up:
	docker-compose up -d

# Parar todos os containers
down:
	docker-compose down

# Construir os containers
build:
	docker-compose build

# Executar composer install
composer:
	docker-compose run --rm composer install

# Configurar banco de dados
setup-db:
	docker-compose exec -T app php setup-db.php

# Alimentar banco de dados com dados iniciais
seed-db:
	docker-compose exec -T app php seed-db.php

# Reiniciar os containers
restart:
	docker-compose down
	docker-compose up -d

# Acessar o shell do container PHP
shell:
	docker-compose exec app bash