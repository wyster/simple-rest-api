# Запуск проекта

Проект можно запустить через docker-compose, для этого нужно

* Перейти в директорию `.docker` : `cd .docker`

* Запустить команду `make setup`

* Отредактировать `.env`, а именно порты по которым будет доступен веб сервер и бд

```
HTTP_PORT=80
DB_PORT=543
````

* Запустить контейнеры

`make up`

Подьём может занят время, устанавливаются пакеты composer, выполняются миграции, 
отследить процесс можно через `make logs`

* После того как все контейнеры запущены проверить работу можно вызвав 

`curl -v http://localhost:$HTTP_PORT`

либо в браузере, статус ответа 200, в теле `Hello world!`

* К базе можно подключиться по `localhost:$DB_PORT`, login: `root`, password: `1`

* В корне проекта доступен файл `.env` в котором

`APP_DEBUG=1` - режим дебага

`CURRENCY=USD` - используемая валюта

`URL_FOR_PAY_POSSIBILITY_CHECK=http://ya.ru` - урл по которому проверяет возможность совершения платежа

Другие доступные команды можно посмотреть выполнив `make`

# Доступные ресурсы и примеры запросов

* Список всех товаров

```
curl -X GET \
  http://localhost/product \
  -H 'Content-Type: application/json'
```

* Создание заказа

```
curl -X PUT \
  http://localhost/order \
  -H 'Content-Type: application/json' \
  -d '{"products": [$PRODUCT_IDS]}'
```

Где `$PRODUCT_IDS` - список идентификаторов (тип integer) через запятую


* Оплата заказа

```
curl -X PUT \
  http://localhost/order/pay \
  -H 'Content-Type: application/json' \
  -d '{"id": $ID, "amount": $AMOUNT}'
```

Где `$ID` - идентификатор заказа, тип integer

Где `$AMOUNT` - сумма заказа, тип integer



# Генерация стартовых данных

Продукты

`docker-compose exec php ./vendor/bin/phinx seed:run -s 'App\Db\Seeds\Products'`

Заказы

`docker-compose exec php ./vendor/bin/phinx seed:run -s 'App\Db\Seeds\Orders'`
