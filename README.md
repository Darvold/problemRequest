Запуск контейнера:
docker-compose up -d --build

 URL сервисов
После запуска контейнеров будут доступны следующие URL:

Laravel приложение	http://localhost:8080	-	Главная страница (форма входа)
phpMyAdmin	http://localhost:8070	root / root	Управление базой данных
MySQL	localhost:3309	root / root	Прямое подключение к БД

# Внутри контейнера laravel_app2
php artisan migrate

# Запуск всех сидов
php artisan db:seed

Для проверки теста "гонки"
Нужно сначала раскоментировать код в файле laravel\bootstrap\app.php
Код:

        /*Раскоментируйте ниже код, для проверки теста "Гонки"*/
       // $middleware->validateCsrfTokens(except: [
         //   'login',
          //  '/',
          //  'master/requests/*',
          //  'master/requests/*/status',
      //  ]);

После в папке laravel в консоль ввести:
php test-race.php

Доступные сиды пользователи:
          // Диспетчеры
          
            [
                'fio' => 'Иванов Иван Иванович',
                'login' => 'ivanov',
                'password' => Hash::make('1234'),
                'role' => 'dispatcher',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fio' => 'Петрова Анна Сергеевна',
                'login' => 'petrova',
                'password' => Hash::make('1234'),
                'role' => 'dispatcher',
                'created_at' => now(),
                'updated_at' => now(),
            ],


        // Мастера
            [
                'fio' => 'Сидоров Петр Петрович',
                'login' => 'sidorov',
                'password' => Hash::make('1234'),
                'role' => 'master',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fio' => 'Козлов Андрей Андреевич',
                'login' => 'kozlov',
                'password' => Hash::make('1234'),
                'role' => 'master',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fio' => 'Михайлов Сергей Михайлович',
                'login' => 'mikhailov',
                'password' => Hash::make('1234'),
                'role' => 'master',
                'created_at' => now(),
                'updated_at' => now(),
            ],
