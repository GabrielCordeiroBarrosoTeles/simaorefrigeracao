.PHONY: help install setup db-create db-migrate db-fixtures db-reset serve test clean

# Cores para output
GREEN=\033[0;32m
YELLOW=\033[1;33m
RED=\033[0;31m
NC=\033[0m # No Color

help: ## Mostra esta ajuda
	@echo "$(GREEN)Sistema Simão Refrigeração - Comandos Disponíveis$(NC)"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "$(YELLOW)%-20s$(NC) %s\n", $$1, $$2}'

install: ## Instala dependências do projeto
	@echo "$(GREEN)Instalando dependências...$(NC)"
	composer install --no-dev --optimize-autoloader
	@echo "$(GREEN)✓ Dependências instaladas$(NC)"

install-dev: ## Instala dependências de desenvolvimento
	@echo "$(GREEN)Instalando dependências de desenvolvimento...$(NC)"
	composer install
	@echo "$(GREEN)✓ Dependências de desenvolvimento instaladas$(NC)"

setup: ## Configuração inicial do projeto
	@echo "$(GREEN)Configurando projeto...$(NC)"
	cp .env.example .env
	@echo "$(YELLOW)Configure o arquivo .env com suas credenciais$(NC)"
	@echo "$(GREEN)✓ Projeto configurado$(NC)"

db-create: ## Cria o banco de dados
	@echo "$(GREEN)Criando banco de dados...$(NC)"
	php bin/doctrine orm:schema-tool:create
	@echo "$(GREEN)✓ Banco de dados criado$(NC)"

db-update: ## Atualiza schema do banco
	@echo "$(GREEN)Atualizando schema...$(NC)"
	php bin/doctrine orm:schema-tool:update --force
	@echo "$(GREEN)✓ Schema atualizado$(NC)"

db-validate: ## Valida schema do banco
	@echo "$(GREEN)Validando schema...$(NC)"
	php bin/doctrine orm:validate-schema

db-fixtures: ## Carrega dados fictícios
	@echo "$(GREEN)Carregando fixtures...$(NC)"
	php bin/console fixtures:load
	@echo "$(GREEN)✓ Fixtures carregadas$(NC)"

db-reset: ## Reseta banco e recarrega fixtures
	@echo "$(YELLOW)Resetando banco de dados...$(NC)"
	php bin/doctrine orm:schema-tool:drop --force
	php bin/doctrine orm:schema-tool:create
	php bin/console fixtures:load
	@echo "$(GREEN)✓ Banco resetado e fixtures carregadas$(NC)"

serve: ## Inicia servidor de desenvolvimento
	@echo "$(GREEN)Iniciando servidor...$(NC)"
	php -S localhost:8000 -t public/

serve-api: ## Inicia servidor apenas para API
	@echo "$(GREEN)Iniciando servidor API...$(NC)"
	php -S localhost:8001 -t public/ public/api.php

test: ## Executa testes
	@echo "$(GREEN)Executando testes...$(NC)"
	./vendor/bin/phpunit

test-coverage: ## Executa testes com coverage
	@echo "$(GREEN)Executando testes com coverage...$(NC)"
	./vendor/bin/phpunit --coverage-html coverage/

cache-clear: ## Limpa cache
	@echo "$(GREEN)Limpando cache...$(NC)"
	rm -rf var/cache/*
	@echo "$(GREEN)✓ Cache limpo$(NC)"

logs-clear: ## Limpa logs
	@echo "$(GREEN)Limpando logs...$(NC)"
	rm -rf var/log/*
	@echo "$(GREEN)✓ Logs limpos$(NC)"

clean: cache-clear logs-clear ## Limpa cache e logs
	@echo "$(GREEN)✓ Limpeza completa$(NC)"

docker-up: ## Sobe containers Docker
	@echo "$(GREEN)Subindo containers...$(NC)"
	docker-compose up -d
	@echo "$(GREEN)✓ Containers rodando$(NC)"

docker-down: ## Para containers Docker
	@echo "$(GREEN)Parando containers...$(NC)"
	docker-compose down
	@echo "$(GREEN)✓ Containers parados$(NC)"

docker-build: ## Constrói imagens Docker
	@echo "$(GREEN)Construindo imagens...$(NC)"
	docker-compose build
	@echo "$(GREEN)✓ Imagens construídas$(NC)"

migration-generate: ## Gera nova migration
	@echo "$(GREEN)Gerando migration...$(NC)"
	php bin/doctrine migrations:generate

migration-migrate: ## Executa migrations
	@echo "$(GREEN)Executando migrations...$(NC)"
	php bin/doctrine migrations:migrate --no-interaction

migration-status: ## Status das migrations
	@echo "$(GREEN)Status das migrations:$(NC)"
	php bin/doctrine migrations:status

prod-deploy: ## Deploy para produção
	@echo "$(GREEN)Fazendo deploy...$(NC)"
	composer install --no-dev --optimize-autoloader
	php bin/doctrine orm:schema-tool:update --force
	$(MAKE) cache-clear
	@echo "$(GREEN)✓ Deploy concluído$(NC)"

check-syntax: ## Verifica sintaxe PHP
	@echo "$(GREEN)Verificando sintaxe...$(NC)"
	find src -name "*.php" -exec php -l {} \;
	@echo "$(GREEN)✓ Sintaxe OK$(NC)"

cs-fix: ## Corrige estilo de código
	@echo "$(GREEN)Corrigindo estilo...$(NC)"
	vendor/bin/php-cs-fixer fix
	@echo "$(GREEN)✓ Estilo corrigido$(NC)"

cs-check: ## Verifica estilo de código
	@echo "$(GREEN)Verificando estilo...$(NC)"
	vendor/bin/php-cs-fixer fix --dry-run --diff
	@echo "$(GREEN)✓ Estilo verificado$(NC)"

phpstan: ## Análise estática
	@echo "$(GREEN)Executando PHPStan...$(NC)"
	vendor/bin/phpstan analyse src --level=5
	@echo "$(GREEN)✓ Análise concluída$(NC)"

quality: cs-check phpstan test ## Executa todas verificações de qualidade
	@echo "$(GREEN)✓ Verificações de qualidade concluídas$(NC)"

autoload: ## Regenera autoload
	@echo "$(GREEN)Regenerando autoload...$(NC)"
	composer dump-autoload -o
	@echo "$(GREEN)✓ Autoload regenerado$(NC)"

fixtures-load: ## Carrega fixtures usando arquivos PHP
	@echo "$(GREEN)Carregando fixtures...$(NC)"
	php bin/fixtures
	@echo "$(GREEN)✓ Fixtures carregadas$(NC)"

clean-legacy: ## Remove arquivos legados
	@echo "$(YELLOW)Removendo arquivos legados...$(NC)"
	rm -rf legacy/
	@echo "$(GREEN)✓ Arquivos legados removidos$(NC)"

test-unit: ## Executa testes unitários
	@echo "$(GREEN)Executando testes unitários...$(NC)"
	vendor/bin/phpunit --testsuite=Unit
	@echo "$(GREEN)✓ Testes unitários concluídos$(NC)"

test-integration: ## Executa testes de integração
	@echo "$(GREEN)Executando testes de integração...$(NC)"
	vendor/bin/phpunit --testsuite=Integration
	@echo "$(GREEN)✓ Testes de integração concluídos$(NC)"