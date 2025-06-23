## Как запустить проект у себя?

1. **Клонировать репозиторий**
2. **Установить зависимости** (composer/npm)
3. **Копировать .env** (если что, я оставил '.env.temp' (то, как было у меня) в корне - для ориентировки, можно копировать его)
4. **Установить Sail** (https://laravel.com/docs/12.x/sail) - php artisan sail:install (нужен Docker)
5. **Во время установки Sail выбираем MySQL и всё** - это должно внести изменения в '.env' (если использовался стандартный 'env.example')
6. **Поднять проект с помощью Sail** (https://laravel.com/docs/12.x/sail) - ./vendor/bin/sail up -d (настроенный 'docker-compose.yml' лежит в корне)
7. **Провести миграции** - ./vendor/bin/sail artisan migrate
8. **Генерируем ключ** - php artisan key:generate
9. **Зарегистрировать пользователя (администратора) для админки Filament** - ./vendor/bin/sail artisan make:filament-user
10. **php artisan storage:link** (будем сохранять "эталонные" скриншоты сайтов для отображения на них кликов)
11. **Зайти на 'http://localhost/' и 'http://localhost/admin/'** (JS скрипт "вшит" в главную страницу + админка на Filament)
12. **В корне проекта будет лежать папка с картинками (1_instruction_pictures)** - это инструкция использования

## Внимание! Если используете Sail, для многих команд artisan (особенно для работы с БД) лучше использовать './vendor/bin/sail artisan ...', а не 'php artisan ...'
