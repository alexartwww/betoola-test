# Betoola test

## Installation

### Linux

I've tested this on Ubuntu 20.04

### Docker + docker-compose

```bash
sudo apt-get remove docker docker-engine docker.io
sudo apt-get install apt-transport-https ca-certificates curl software-properties-common
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -
sudo apt-key fingerprint 0EBFCD88
sudo add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable"
sudo apt-get update
sudo apt-get install docker-ce
sudo usermod -aG docker $USER
docker run hello-world
sudo curl -L "https://github.com/docker/compose/releases/download/1.22.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
```

### Make

```bash
sudo apt-get install make
```

### Install project

```bash
git clone ... 
cd ./betoola-test
cp ./.env.dev ./.env
ver=1.0.0 make build up
sleep 10
ver=1.0.0 make restore
```

### Test

Open http://localhost:10000/ You should see Hello world! and current datetime.

Open `./tests/requests.http` and play with API

### Developing

Open `docker-compose.yml` and uncomment this lines:

```yml
#    volumes:
#      - .:/var/www/src
#      - ./.docker/php:/etc/php
```

```bash
make restart
make shell
composer install
```

### Debugging

For xdebug check config in `.docker/php/8.1/fpm/conf.d/20-xdebug.ini`

```ini
zend_extension=xdebug.so

xdebug.idekey=betoola
xdebug.mode=debug,develop
xdebug.start_with_request=yes
xdebug.client_host=172.17.0.1
xdebug.client_port=9000
xdebug.connect_timeout_ms=1000
```

In PHPStorm go to Run -> Edit configurations... -> [+] -> PHP Remote debug

* Set `name` to `betoola`
* Check `Filter debug connection by IDE key`
* Set `IDE key(session id)` to `betoola`
* Server -> [...]
  * Set `name` and `host` to `betoola`
  * Check `Use path mappings`
  * Add mapping for project directory to `/var/www/src`

Select configuration and hit debug button. IDE should listen for connections. Set breakpooints and chec the code.

## Make commands

Anytime you can pass version in `ver` environment variable to run or build specific version

* `make up` or `make start` starts the project
* `make down` or `make stop` stops the project
* `make restart` restars the project
* `make build` builds docker image
* `make restore` restores db from backup
* `make backup` creates db backup
* `make mysql` opens mysql console
* `make mysql-root` opens mysql console under root user
* `make shell` opens bash console for project image

## TODO

1. Tests
2. OpenAPI
3. Swagger UI
4. Validation framework
5. Filters/sorting/pagination for lists
6. Other CRUD methods
7. Authorization
8. Models, Domain and many other OOP stuff
9. Users, Brands, Storages, More Categories, Logistics, Payments, Backoffice, etc ecom stuff
10. Monitoring
11. Backups
12. Caching
13. Master-slave db config
14. Load balancer
15. Kubernetes
16. Blue-green deployment
17. Sky is the limit

# You awesome!