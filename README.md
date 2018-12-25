Бот парсит https://freelansim.ru/user_rss_tasks/6Fpi1p32eMAPheTrxdyh и отправляет новые записи в телеграм канал.

Команды для запуска:
```
php bin/console app:parse-freelancing-job fl --dry_run
php bin/console app:parse-freelancing-job freelansim --dry_run
php bin/console app:parse-freelancing-job fl
php bin/console app:parse-freelancing-job freelansim 
```

Необходимо настроить приложение:

* В файле ".env.dist" поправить DATABASE_URL=mysql://root:123456@db:3306/fl
* В файле "config/packages/doctrine.yaml" поправить "server_version: '8.0'"
```
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```


1. Создать бота
2. Создать канал и добавить бота в админы
4. В файле "src/Model/Config/ConfigTelegram.php" установить свои значения бота


