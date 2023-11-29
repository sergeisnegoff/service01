# App

### Первоначальная установка

- Скопируйте и настройте переменные окружения .env:
    `cp .env.dist .env`
  
- Запустите контейнеры:
  `docker-compose up -d`
  
- Установите PHP зависимости:
  `docker-compose exec backend composer install`
     
---  

### Настройка рабочей среды

- [PhpStorm (backend)](app/docs/php-storm-backend.md) 
- [PhpStorm (frontend)](app/docs/php-storm-frontend.md) 

---
  
### Работа с локальной базой данных
1. В файле .env замените переменные 
    ```
    COMPOSE_FILE=docker-compose.yaml:docker-compose.override.yaml:docker-compose.db.yaml
    DATABASE_HOST=db
   ```
2. Остановите и запустите контейнеры заново
3. Загрузите SQL-дамп базы
   ```
   docker-compose exec -T db-szh sh -c 'exec mysql -uroot -p"$MYSQL_ROOT_PASSWORD" $MYSQL_DATABASE' < dump.sql
   ```
   **Где dump.sql — название файла с дампом**
  
---  
  
### Работа с docker-compose
- Запустить контейнеры:
  `docker-compose up -d`
  
- Остановить контейнеры:
  `docker-compose down`
  
---  
  
### Работа с проектом

#### Propel миграция
- Собираем модель:
  `docker-compose exec backend bin/console propel:model:build`
  
- Создаем миграцию:
  `docker-compose exec backend bin/console propel:migration:diff`
  
- Проверяем запросы в созданном файле миграции, удаляем все лишнее
  
- Выполняем запросы миграции:
  `docker-compose exec backend bin/console propel:migration:migrate`