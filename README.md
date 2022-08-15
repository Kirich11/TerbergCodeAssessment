## Terberg assessment

### Run app
    docker compose run up -d
    docker exec -it terberg-web-1 bash
    cd terberg
    php bin/console do:mi:mi

### Testing
create a test db and run tests
    
    docker exec -it terberg-web-1 bash
    cd terberg
    php bin/console --env=test do:da:cr
    php bin/console --env=test do:mi:mi
    ./vendor/bin/phpunit
    ./vendor/bin/behat

### Available API

GET /cars
returns list of cars

POST /cars
{ 'make': 'test', 'model': 'test', 'catalogPrice': 10}

GET /cars/lease/{duration}/{mileage}
e.g. /cars/lease/12/100

### Command
Load mercedez cars from [Mercedez-Bebz Car Configurator](https://developer.mercedes-benz.com/products/car_configurator)
    `php bin/console run mercedez:load`
