# Map Suggest

## Prerequisites
```
Docker
Docker Compose
```

## Project setup
```
git clone https://github.com/wizard-x/map-suggest.git
```

### Start dev-server
```
docker-compose up
```

### Update packages
```
docker-compose exec backend composer install
docker-compose exec backend npm install
docker-compose exec backend npm run build
```

### Database migrations
```
docker-compose exec backend /bin/sh -c "php ./bin/console doctrine:migrations:diff && php ./bin/console doctrine:migrations:migrate --no-interaction"
```

### Run unit tests
```
docker-compose exec backend  /bin/sh -c "php ./bin/console --env=test doctrine:database:create && php ./bin/console --env=test doctrine:schema:create"

docker-compose exec backend ./bin/phpunit
```

### View tests coverage
```
docker-compose exec backend ./bin/phpunit --coverage-text
```

### Shell
```
docker-compose exec backend /bin/sh
```

### Access to ...
```
web (vue)   - http://127.0.0.1:8000
```
```
php (proxy) - http://127.0.0.1:9000
```
```
pgadmin     - http://127.0.0.1:8080
login: yar@yar.yar
pass: yar
```
```
psql        - 127.0.0.1:5432

docker-compose exec database psql -Uyar

PGPASSWORD=yar psql -Uyar -h127.0.0.1
```