# ytubes

Базовое расширение

## Для миграций
В консольном приложении: console/config/components.php прописать:
```php
'controllerMap' => [
    'migrate' => [
        'class' => 'yii\console\controllers\MigrateController',
           'migrationPath' => [
                '@vendor/ytubes/core/migrations',
        ],
    ],
],
```
## Крон
Также для нормальной работы нужные воркеры для крона:
```
\ytubes\cron\jobs\VisitorsHandler * * * * *
\ytubes\cron\jobs\SitemapBuilder */10 * * * *
```
