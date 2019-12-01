# Запуск проекта

Проект можно запустить через docker-compose, для этого нужно выполнить

`cd .docker && make up`

Другие команды можно посмотреть выполнив

`cd .docker && make`

# Генерация стартовых данных

Продукты

docker-compose exec php ./vendor/bin/phinx seed:run -s 'App\Db\Seeds\Products'

Заказы

docker-compose exec php ./vendor/bin/phinx seed:run -s 'App\Db\Seeds\Orders'
