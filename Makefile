FILES ?= src tests

.SILENT:

.DEFAULT_GOAL := help

help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

##
## This console application is made to manage project.

##
## LINT
php-stan: ## Run PHP Stan analyse
	@echo "Executing PHP Stan analyze... on $(FILES)"
	@composer phpstan

ecs: ## Runs Easy Coding Standard tool
	echo "Executing easy coding standard tool..."
	@./vendor/bin/ecs --clear-cache check $(FILES)

ecs-fix: ## Runs Easy Coding Standard tool to fix issues
	echo "Executing ecs fixer..."
	@./vendor/bin/ecs --clear-cache --fix check $(FILES)

check-code-quality: php-stan ecs ecs-fix ## Check Code Quality

.PHONY: php-stan ecs ecs-fix check-code-quality

##
## TESTS
tests: ## Run tests.
	@APP_ENV=test ./vendor/bin/phpunit tests --testdox --colors=always

.PHONY: tests