# Deployment Guide

## Desenvolvimento

```bash
# Setup inicial
make setup
make install-dev
make db-create
make db-fixtures

# Servidor local
make serve
```

## Produção

```bash
# Deploy
make prod-deploy

# Verificações
make check-syntax
make cs-check
make phpstan
```

## Docker

```bash
# Subir ambiente
make docker-up

# Build imagens
make docker-build

# Parar ambiente
make docker-down
```

## CI/CD

O projeto usa GitHub Actions para:
- Verificação de estilo (PHP-CS-Fixer)
- Análise estática (PHPStan)
- Testes automatizados (PHPUnit)

## Comandos Úteis

```bash
# Qualidade de código
composer cs-fix          # Corrige estilo
composer cs-check        # Verifica estilo
composer phpstan         # Análise estática
composer quality         # Executa todas verificações

# Banco de dados
make db-reset            # Reseta banco
make db-fixtures         # Carrega dados teste
make migration-generate  # Gera migration
make migration-migrate   # Executa migrations
```