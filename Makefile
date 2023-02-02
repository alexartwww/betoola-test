include .env

SQL_DIR=.docker/sql
BACKUPS_DIR=.docker/backups

BACKUPS_DIR_DB=${BACKUPS_DIR}/db

BACKUPS_DIR_CURRENT_DB=${BACKUPS_DIR_DB}/$(shell date +%Y-%m-%d-%H-%M-%S)
RESTORE_DIR_CURRENT_DB=${BACKUPS_DIR_DB}/$(shell ls -1r ${BACKUPS_DIR_DB} | head -1)

MYSQL_DUMP=docker-compose run mysql mysqldump "${MYSQL_DB}" -u"${MYSQL_USER}" --password="${MYSQL_PASSWORD}" -h"${MYSQL_HOST}" --lock-tables=false
MYSQL_RESTORE_ROOT=docker-compose run mysql mysql -uroot --password="${MYSQL_ROOT_PASSWORD}" -h"${MYSQL_HOST}"
MYSQL_RESTORE=docker-compose run mysql mysql -f "${MYSQL_DB}" -u"${MYSQL_USER}" --password="${MYSQL_PASSWORD}" -h"${MYSQL_HOST}"
MYSQL_DUMP_SKIP=Using a password on the command line interface can be insecure
MYSQL_PROMPT="\u@\h [\d] > "

env ?= dev
ver ?= latest

HELP_FUN = \
		%help; \
		while(<>) { push @{$$help{$$2 // 'options'}}, [$$1, $$3] if /^(\w+)\s*:.*\#\#(?:@(\w+))?\s(.*)$$/ }; \
		print "usage: make [target]\n\n"; \
	for (keys %help) { \
		print "$$_:\n"; $$sep = " " x (20 - length $$_->[0]); \
		print "  $$_->[0]$$sep$$_->[1]\n" for @{$$help{$$_}}; \
		print "\n"; }

help:           ##@miscellaneous Show this help.
	@perl -e '$(HELP_FUN)' $(MAKEFILE_LIST)

up: ## Starts the app
	docker-compose up -d
	docker-compose ps

down: ## Stops the app
	docker-compose ps
	docker-compose down

backup: ## Backups the app
	mkdir -p ${BACKUPS_DIR_CURRENT_DB}

	ver=${ver} ${DB_DUMP}\
	 | grep -v "${DB_DUMP_SKIP}"\
	 | gzip -9 > ${BACKUPS_DIR_CURRENT_DB}/betoola.sql.gz

restore: ## Restores the app
	@echo "Restoring from "${BACKUPS_DIR_CURRENT_DB}
	@echo "Restoring from "${DB_DIR}"/init.sql"
	cat ${SQL_DIR}/init.sql | ver=${ver} ${DB_RESTORE_ROOT}
	@echo "Restoring from "${BACKUPS_DIR_CURRENT_DB}/betoola.sql.gz
	zcat ${RESTORE_DIR_CURRENT_DB}/betoola.sql.gz | grep -v "${DB_DUMP_SKIP}" | ver=${ver} ${DB_RESTORE}
	@echo "Done"

mysql:
	docker-compose run mysql mysql ${MYSQL_DB} -u"${MYSQL_USER}" --password="${MYSQL_PASSWORD}" -h"${MYSQL_HOST}" --prompt=${MYSQL_PROMPT}

mysql-root:
	docker-compose run mysql mysql -uroot --password="${MYSQL_ROOT_PASSWORD}" -h"${MYSQL_HOST}" --prompt=${MYSQL_PROMPT}

start: up

stop: down

restart: stop start

logs:
	ver=${ver} docker-compose logs -f

shell: ## Bash
	ver=${ver} docker-compose run app bash

build: ## Build docker
	ver=${ver} docker build --rm -t betoola:latest -t betoola:${ver} .
