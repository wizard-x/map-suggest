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
```

### Run unit tests
```
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
php (proxy) - http://127.0.0.1:9000
pgadmin     - http://127.0.0.1:8080
psql        - 127.0.0.1:5432
```