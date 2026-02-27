26.02 18:32
<span style="color: #00ff00"></br>МОЙ ЗАПРОС</br></span> 
Мой запрос: Создай докер файл с laravel, MySQL, PhpMyAdmin

<span style="color: #0066ff"></br>**ОТВЕТ ИИ:**</br></span>
```yaml
Dockerfile
version: '3.8'

services:
  mysql:
    image: mysql:8.0
    container_name: laravel_mysql
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: laravel
      MYSQL_ROOT_PASSWORD: root
      MYSQL_PASSWORD: root
      MYSQL_USER: laravel
    ports:
      - "3309:3306"
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - laravel_network

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    container_name: laravel_phpmyadmin
    restart: unless-stopped
    environment:
      PMA_HOST: mysql
      PMA_PORT: 3306
      PMA_USER: root
      PMA_PASSWORD: root
      UPLOAD_LIMIT: 100M
    ports:
      - "8070:80"
    depends_on:
      - mysql
    networks:
      - laravel_network

  laravel:
    build: .
    container_name: laravel_app2
    restart: unless-stopped
    ports:
      - "8080:80"
    volumes:
      - ./laravel:/var/www/html
    depends_on:
      - mysql
    networks:
      - laravel_network

networks:
  laravel_network:
    driver: bridge

volumes:
  mysql_data:
```
```yaml
Dockerfile
FROM php:8.2-apache

# Установка зависимостей
RUN apt-get update && \
    apt-get install -y libzip-dev unzip git && \
    docker-php-ext-install pdo_mysql zip && \
    a2enmod rewrite

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Настройка Apache для Laravel (исправленный формат)
RUN echo "<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog \${APACHE_LOG_DIR}/error.log\n\
    CustomLog \${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>" > /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Скрипт для проверки установки Laravel
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
```
```yaml
docker-entrypoint.sh
#!/bin/bash
set -e

# Проверяем, установлен ли Laravel
if [ ! -f /var/www/html/artisan ]; then
    echo "Laravel not found. Creating new Laravel project..."
    composer create-project --prefer-dist laravel/laravel .
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
else
    echo "Laravel already installed. Running composer install..."
    composer install --no-interaction
fi

# Запускаем Apache
exec "$@"
```
26.02 19:18
<span style="color: #00ff00"></br>МОЙ ЗАПРОС</br></span>
Мой запрос: Сделай миграцию по зданию таблиц
Заявка (Request) должна иметь поля:

`clientName` (обязательно)
`phone` (обязательно)
`address` (обязательно)
`problemText` (обязательно)
`status` (одно из): `new | assigned | in_progress | done | canceled`
`assignedTo` (мастер, может быть пустым)
`createdAt`, `updatedAt`

А также users, с id, fio, login, password, role (dispatcher и master)  timestamp

А также сиды (настраиваемые где мне нужно самому вписать данные) по этим таблицам

<span style="color: #0066ff"></br>**ОТВЕТ ИИ:**</br></span>

```yaml
1. Миграция для users
   database/migrations/2024_01_01_000001_create_users_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('fio');
            $table->string('login')->unique();
            $table->string('password');
            $table->enum('role', ['dispatcher', 'master']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
2. Миграция для requests (заявки)
database/migrations/2024_01_01_000002_create_requests_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->string('clientName');
            $table->string('phone');
            $table->string('address');
            $table->text('problemText');
            $table->enum('status', ['new', 'assigned', 'in_progress', 'done', 'canceled'])->default('new');
            $table->foreignId('assignedTo')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
 ```
Сиды (Seeders)
  ```yaml
1. Users Seeder
database/seeders/UsersTableSeeder.php

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Диспетчеры
        DB::table('users')->insert([
            [
                'fio' => 'Иванов Иван Иванович',
                'login' => 'ivanov',
                'password' => Hash::make('password123'),
                'role' => 'dispatcher',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fio' => 'Петрова Анна Сергеевна',
                'login' => 'petrova',
                'password' => Hash::make('password123'),
                'role' => 'dispatcher',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Мастера
        DB::table('users')->insert([
            [
                'fio' => 'Сидоров Петр Петрович',
                'login' => 'sidorov',
                'password' => Hash::make('master123'),
                'role' => 'master',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fio' => 'Козлов Андрей Андреевич',
                'login' => 'kozlov',
                'password' => Hash::make('master123'),
                'role' => 'master',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fio' => 'Михайлов Сергей Михайлович',
                'login' => 'mikhailov',
                'password' => Hash::make('master123'),
                'role' => 'master',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
2. Requests Seeder
database/seeders/RequestsTableSeeder.php

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequestsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем ID мастеров для назначения
        $masters = DB::table('users')->where('role', 'master')->pluck('id')->toArray();
        
        DB::table('requests')->insert([
            [
                'clientName' => 'Смирнов Алексей Владимирович',
                'phone' => '+7 (999) 123-45-67',
                'address' => 'ул. Ленина, д. 10, кв. 25',
                'problemText' => 'Не работает стиральная машина, не сливает воду',
                'status' => 'new',
                'assignedTo' => null,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'clientName' => 'Кузнецова Елена Дмитриевна',
                'phone' => '+7 (999) 234-56-78',
                'address' => 'пр. Мира, д. 45, кв. 12',
                'problemText' => 'Течет кран на кухне, нужна замена смесителя',
                'status' => 'assigned',
                'assignedTo' => $masters[0] ?? null,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subHours(5),
            ],
            [
                'clientName' => 'Васильев Дмитрий Николаевич',
                'phone' => '+7 (999) 345-67-89',
                'address' => 'ул. Гагарина, д. 78, кв. 5',
                'problemText' => 'Не включается холодильник, моргает лампочка',
                'status' => 'in_progress',
                'assignedTo' => $masters[1] ?? null,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subHours(2),
            ],
            [
                'clientName' => 'Николаева Татьяна Павловна',
                'phone' => '+7 (999) 456-78-90',
                'address' => 'ул. Советская, д. 23, кв. 41',
                'problemText' => 'Сломалась микроволновая печь, искрит внутри',
                'status' => 'done',
                'assignedTo' => $masters[2] ?? null,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(1),
            ],
            [
                'clientName' => 'Морозов Сергей Александрович',
                'phone' => '+7 (999) 567-89-01',
                'address' => 'пр. Победы, д. 15, кв. 33',
                'problemText' => 'Заказчик передумал, отмена заявки',
                'status' => 'canceled',
                'assignedTo' => null,
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(3),
            ],
            [
                'clientName' => 'Волкова Ольга Игоревна',
                'phone' => '+7 (999) 678-90-12',
                'address' => 'ул. Кирова, д. 56, кв. 18',
                'problemText' => 'Требуется установка посудомоечной машины',
                'status' => 'new',
                'assignedTo' => null,
                'created_at' => now()->subHours(6),
                'updated_at' => now()->subHours(6),
            ],
        ]);
    }
}
3. Главный Database Seeder
database/seeders/DatabaseSeeder.php

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
            RequestsTableSeeder::class,
        ]);
    }
}
```
26.02 19:35
<span style="color: #00ff00"></br>МОЙ ЗАПРОС</br></span>
Мой запрос: Сделай миграцию по зданию таблиц
Заявка (Request) должна иметь поля:

`clientName` (обязательно)
`phone` (обязательно)
`address` (обязательно)
`problemText` (обязательно)
`status` (одно из): `new | assigned | in_progress | done | canceled`
`assignedTo` (мастер, может быть пустым)
`createdAt`, `updatedAt`

А также users, с id, fio, login, password, role (dispatcher и master)  timestamp

А также сиды (настраиваемые где мне нужно самому вписать данные) по этим таблицам

<span style="color: #0066ff"></br>**ОТВЕТ ИИ:**</br></span>

```yaml
Контроллеры
app/Http/Controllers/AuthController.php
  <?php

  namespace App\Http\Controllers;

  use App\Models\User;
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\Hash;
  use Illuminate\Support\Facades\Validator;

  class AuthController extends Controller
  {
    /**
    * Показать форму регистрации
    */
    public function showRegisterForm()
    {
      return view('auth.register');
    }

    /**
    * Обработка регистрации
    */
    public function register(Request $request)
    {
      $validator = Validator::make($request->all(), [
      'fio' => 'required|string|max:255',
      'login' => 'required|string|max:255|unique:users',
      'password' => 'required|string|min:6|confirmed',
      'role' => 'required|in:dispatcher,master',
    ]);

      if ($validator->fails()) {
      return redirect()->back()
      ->withErrors($validator)
      ->withInput();
    }

      $user = User::create([
      'fio' => $request->fio,
      'login' => $request->login,
      'password' => Hash::make($request->password),
      'role' => $request->role,
      'email' => $request->login . '@example.com', // временный email
    ]);

      Auth::login($user);

      return redirect()->intended($this->redirectTo());
    }

    /**
    * Показать форму входа
    */
    public function showLoginForm()
    {
      return view('auth.login');
    }

    /**
    * Обработка входа
    */
    public function login(Request $request)
    {
      $credentials = $request->validate([
      'login' => 'required|string',
      'password' => 'required|string',
    ]);

      if (Auth::attempt($credentials)) {
      $request->session()->regenerate();

      return redirect()->intended($this->redirectTo());
    }

      return back()->withErrors([
      'login' => 'Неверный логин или пароль.',
    ])->onlyInput('login');
    }

    /**
    * Выход из системы
    */
    public function logout(Request $request)
    {
      Auth::logout();

      $request->session()->invalidate();
      $request->session()->regenerateToken();

      return redirect('/login');
    }

    /**
    * Определить редирект после входа на основе роли
    */
    protected function redirectTo()
    {
      $user = Auth::user();

      if ($user->role === 'dispatcher') {
      return '/dispatcher/dashboard';
    } elseif ($user->role === 'master') {
      return '/master/dashboard';
    }

      return '/dashboard';
    }
  }
```
app/Http/Middleware/CheckRole.php
```yaml
 
  <?php

  namespace App\Http\Middleware;

  use Closure;
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\Auth;

  class CheckRole
  {
    /**
    * Handle an incoming request.
    */
    public function handle(Request $request, Closure $next, ...$roles)
    {
      if (!Auth::check()) {
      return redirect('/login');
    }

      $user = Auth::user();

      if (!in_array($user->role, $roles)) {
      abort(403, 'У вас нет доступа к этой странице.');
    }

      return $next($request);
    }
  }
```
app/Http/Controllers/DispatcherController.php
```yaml
<?php

namespace App\Http\Controllers;

use App\Models\Request;
use App\Models\User;
use Illuminate\Http\Request as HttpRequest;

class DispatcherController extends Controller
{
    /**
     * Показать дашборд диспетчера
     */
    public function dashboard()
    {
        $stats = [
            'new_requests' => Request::where('status', 'new')->count(),
            'assigned_requests' => Request::where('status', 'assigned')->count(),
            'in_progress_requests' => Request::where('status', 'in_progress')->count(),
            'total_requests' => Request::count(),
        ];

        $recentRequests = Request::with('master')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $masters = User::where('role', 'master')->get();

        return view('dispatcher.dashboard', compact('stats', 'recentRequests', 'masters'));
    }

    /**
     * Назначить мастера на заявку
     */
    public function assignMaster(HttpRequest $request, $requestId)
    {
        $requestData = Request::findOrFail($requestId);
        $requestData->assignedTo = $request->master_id;
        $requestData->status = 'assigned';
        $requestData->save();

        return redirect()->back()->with('success', 'Мастер назначен на заявку');
    }
}
```
app/Http/Controllers/MasterController.php
```yaml
<?php

namespace App\Http\Controllers;

use App\Models\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;

class MasterController extends Controller
{
    /**
     * Показать дашборд мастера
     */
    public function dashboard()
    {
        $myRequests = Request::where('assignedTo', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'assigned' => $myRequests->where('status', 'assigned')->count(),
            'in_progress' => $myRequests->where('status', 'in_progress')->count(),
            'done' => $myRequests->where('status', 'done')->count(),
            'total' => $myRequests->count(),
        ];

        return view('master.dashboard', compact('myRequests', 'stats'));
    }

    /**
     * Изменить статус заявки
     */
    public function updateStatus(HttpRequest $request, $requestId)
    {
        $requestData = Request::where('id', $requestId)
            ->where('assignedTo', Auth::id())
            ->firstOrFail();

        $requestData->status = $request->status;
        $requestData->save();

        return redirect()->back()->with('success', 'Статус заявки обновлен');
    }

    /**
     * Просмотр деталей заявки
     */
    public function showRequest($requestId)
    {
        $requestData = Request::where('id', $requestId)
            ->where('assignedTo', Auth::id())
            ->firstOrFail();

        return view('master.request-details', compact('requestData'));
    }
}
```

routes/web.php
```yaml
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DispatcherController;
use App\Http\Controllers\MasterController;
use Illuminate\Support\Facades\Route;

// Публичные маршруты
Route::get('/', function () {
    return view('welcome');
});

// Маршруты авторизации
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Маршруты для диспетчера
Route::middleware(['auth', 'role:dispatcher'])->prefix('dispatcher')->group(function () {
    Route::get('/dashboard', [DispatcherController::class, 'dashboard'])->name('dispatcher.dashboard');
    Route::post('/requests/{id}/assign', [DispatcherController::class, 'assignMaster'])->name('dispatcher.assign');
});

// Маршруты для мастера
Route::middleware(['auth', 'role:master'])->prefix('master')->group(function () {
    Route::get('/dashboard', [MasterController::class, 'dashboard'])->name('master.dashboard');
    Route::get('/requests/{id}', [MasterController::class, 'showRequest'])->name('master.request');
    Route::post('/requests/{id}/status', [MasterController::class, 'updateStatus'])->name('master.update-status');
});

// Общий дашборд (для теста)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth');
```
bootstrap/app.php
добавить withMiddleware(function (Middleware $middleware) {
```yaml
 $middleware->alias([
            'role' => CheckRole::class,
        ]);
```
app/Http/Controller/RequestController.php
```yaml
<?php

namespace App\Http\Controllers;

use App\Models\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    /**
     * Показать форму создания заявки
     */
    public function create()
    {
        // Только диспетчер может создавать заявки
        if (Auth::user()->role !== 'dispatcher') {
            abort(403, 'Только диспетчер может создавать заявки');
        }

        return view('requests.create');
    }

    /**
     * Сохранить новую заявку
     */
    public function store(HttpRequest $request)
    {
        // Только диспетчер может создавать заявки
        if (Auth::user()->role !== 'dispatcher') {
            abort(403, 'Только диспетчер может создавать заявки');
        }

        $validated = $request->validate([
            'clientName' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'problemText' => 'required|string',
        ]);

        Request::create([
            'clientName' => $validated['clientName'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'problemText' => $validated['problemText'],
            'status' => 'new', // Статус new по умолчанию
            'assignedTo' => null,
        ]);

        return redirect()->route('dispatcher.dashboard')
            ->with('success', 'Заявка успешно создана');
    }

    /**
     * Просмотр деталей заявки
     */
    public function show($id)
    {
        $request = Request::with('master')->findOrFail($id);
        
        // Проверка прав доступа
        $user = Auth::user();
        if ($user->role === 'master' && $request->assignedTo !== $user->id) {
            abort(403, 'У вас нет доступа к этой заявке');
        }

        return view('requests.show', compact('request'));
    }
}
```
}
А также он создал все страницы blade для диспетчера, мастера и форма создание заявок...

26.02 20:53
<span style="color: #00ff00"></br>МОЙ ЗАПРОС</br></span>
Мой запрос: Напиши Минимум несколько авто-тестов, если что у меня версия laravel 12

<span style="color: #0066ff"></br>**ОТВЕТ ИИ:**</br></span>
он написал все тесты, ниже файлы которые он создал и код в них
```yaml
tests/
├── Feature/
│   ├── Auth/
│   │   ├── RegistrationTest.php
│   │   └── LoginTest.php
│   └── RequestManagementTest.php
└── Unit/
    └── UserModelTest.php
```

26.02 22:11
<span style="color: #00ff00"></br>МОЙ ЗАПРОС</br></span>
Мой запрос: Напиши тест программу с SQL запросами по проверке "гонки", то есть:
Действие “Взять в работу” должно быть безопасным при параллельных запросах: если два запроса пришли одновременно, заявка не должна “сломаться”.

Корректное поведение: один запрос успешен, второй получает отказ (например, `409 Conflict`) или понятный ответ, что заявка уже взята.

<span style="color: #0066ff"></br>**ОТВЕТ ИИ:**</br></span>
test-race.php
```yaml
<?php

/**
 * ТЕСТ ЗАЩИТЫ ОТ ГОНОК - ПРЯМЫЕ SQL ЗАПРОСЫ
 *
 * Запуск: php test-race-direct-sql.php
 */

class RaceConditionTest
{
    private $requestId = 6;
    private $originalStatus = null; // Для сохранения исходного статуса
    private $dbConfig = [
        'host' => '127.0.0.1',
        'port' => '3309',        // Порт из docker-compose
        'database' => 'laravel',
        'username' => 'root',
        'password' => 'root'
    ];

    public function run()
    {
        $this->printHeader();

        // Проверяем соединение с БД
        $pdo = $this->getDatabaseConnection();
        if (!$pdo) {
            $this->printError("Не удалось подключиться к базе данных");
            return;
        }

        // Проверяем существование заявки и сохраняем исходный статус
        if (!$this->checkRequestExists($pdo)) {
            $this->printError("Заявка ID {$this->requestId} не найдена");
            return;
        }

        // Запускаем параллельные SQL запросы
        $this->parallelSqlRequests();

        // Проверяем финальный статус
        $this->checkFinalStatus($pdo);

        // ВОССТАНАВЛИВАЕМ ИСХОДНЫЙ СТАТУС
        $this->restoreOriginalStatus($pdo);
    }

    private function printHeader()
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════╗\n";
        echo "║     ТЕСТ ЗАЩИТЫ ОТ ГОНОК - ПРЯМЫЕ SQL ЗАПРОСЫ          ║\n";
        echo "╚══════════════════════════════════════════════════════════╝\n\n";
    }

    private function printError($message) { echo "❌ $message\n"; }
    private function printSuccess($message) { echo "✅ $message\n"; }
    private function printInfo($message) { echo "ℹ️ $message\n"; }
    private function printWarning($message) { echo "⚠️ $message\n"; }

    /**
     * Получить соединение с БД
     */
    private function getDatabaseConnection()
    {
        $this->printInfo("Подключение к БД...");

        try {
            $pdo = new PDO(
                "mysql:host={$this->dbConfig['host']};port={$this->dbConfig['port']};dbname={$this->dbConfig['database']}",
                $this->dbConfig['username'],
                $this->dbConfig['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            $this->printSuccess("Подключение к БД успешно");
            return $pdo;
        } catch (PDOException $e) {
            $this->printError("Ошибка подключения: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Проверить существование заявки и сохранить исходный статус
     */
    private function checkRequestExists($pdo)
    {
        $stmt = $pdo->prepare("SELECT id, status FROM requests WHERE id = :id");
        $stmt->execute(['id' => $this->requestId]);
        $request = $stmt->fetch();

        if ($request) {
            $this->originalStatus = $request['status'];
            $this->printInfo("Заявка найдена, исходный статус: {$this->originalStatus}");
            return true;
        } else {
            $this->printError("Заявка ID {$this->requestId} не найдена в БД");
            return false;
        }
    }

    /**
     * Восстановить исходный статус
     */
    private function restoreOriginalStatus($pdo)
    {
        echo "\n🔄 Восстановление исходного статуса...\n";

        if ($this->originalStatus === null) {
            $this->printWarning("Исходный статус неизвестен, пропускаем");
            return;
        }

        try {
            $stmt = $pdo->prepare("UPDATE requests SET status = :status WHERE id = :id");
            $stmt->execute([
                'status' => $this->originalStatus,
                'id' => $this->requestId
            ]);

            $affected = $stmt->rowCount();
            if ($affected > 0) {
                $this->printSuccess("Статус восстановлен на '{$this->originalStatus}'");
            } else {
                // Проверяем текущий статус
                $checkStmt = $pdo->prepare("SELECT status FROM requests WHERE id = :id");
                $checkStmt->execute(['id' => $this->requestId]);
                $currentStatus = $checkStmt->fetchColumn();

                if ($currentStatus === $this->originalStatus) {
                    $this->printInfo("Статус уже '{$this->originalStatus}' (не требовал изменений)");
                } else {
                    $this->printWarning("Не удалось восстановить статус. Текущий: {$currentStatus}");
                }
            }
        } catch (Exception $e) {
            $this->printError("Ошибка при восстановлении статуса: " . $e->getMessage());
        }
    }

    /**
     * Сбросить статус на assigned (для теста)
     */
    private function resetToAssigned($pdo)
    {
        $this->printInfo("Сброс статуса на 'assigned' для теста...");

        $stmt = $pdo->prepare("UPDATE requests SET status = 'assigned' WHERE id = :id");
        $stmt->execute(['id' => $this->requestId]);

        $affected = $stmt->rowCount();
        if ($affected > 0) {
            $this->printSuccess("Статус сброшен на assigned");
        } else {
            $this->printWarning("Статус не изменился (возможно уже assigned)");
        }

        sleep(1);
    }

    /**
     * Запуск параллельных SQL запросов
     */
    private function parallelSqlRequests()
    {
        echo "\n🚀 Запуск параллельных SQL запросов...\n";
        echo "   Заявка ID: {$this->requestId}\n";
        echo "   Целевой статус: assigned → in_progress\n";
        echo "   Количество запросов: 5\n\n";

        // Сначала сбрасываем на assigned для чистоты теста
        $pdo = $this->getDatabaseConnection();
        $this->resetToAssigned($pdo);

        $processes = [];
        $tempFiles = [];

        // Создаем 5 параллельных процессов
        for ($i = 0; $i < 5; $i++) {
            $tempScript = __DIR__ . "/temp_sql_{$i}.php";
            $tempFiles[] = $tempScript;

            // Создаем PHP скрипт с прямым SQL запросом
            $scriptContent = '<?php
                $dbConfig = ' . var_export($this->dbConfig, true) . ';
                $requestId = ' . $this->requestId . ';

                try {
                    $pdo = new PDO(
                        "mysql:host={$dbConfig[\'host\']};port={$dbConfig[\'port\']};dbname={$dbConfig[\'database\']}",
                        $dbConfig[\'username\'],
                        $dbConfig[\'password\'],
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );

                    // НАЧИНАЕМ ТРАНЗАКЦИЮ
                    $pdo->beginTransaction();

                    // ПЕРВЫЙ ЗАПРОС: получаем текущий статус с блокировкой
                    $stmt = $pdo->prepare("SELECT status FROM requests WHERE id = :id FOR UPDATE");
                    $stmt->execute([\'id\' => $requestId]);
                    $currentStatus = $stmt->fetchColumn();

                    // Имитация небольшой задержки для создания гонки
                    usleep(rand(100000, 300000)); // 0.1-0.3 сек

                    // ВТОРОЙ ЗАПРОС: обновляем статус, только если он "assigned"
                    if ($currentStatus === "assigned") {
                        $updateStmt = $pdo->prepare("UPDATE requests SET status = \'in_progress\' WHERE id = :id AND status = \'assigned\'");
                        $updateStmt->execute([\'id\' => $requestId]);

                        if ($updateStmt->rowCount() > 0) {
                            $pdo->commit();
                            echo "SUCCESS";
                        } else {
                            $pdo->rollBack();
                            echo "CONFLICT (no rows updated)";
                        }
                    } else {
                        $pdo->rollBack();
                        echo "CONFLICT (current: $currentStatus)";
                    }

                } catch (Exception $e) {
                    if ($pdo && $pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    echo "ERROR: " . $e->getMessage();
                }
            ';

            file_put_contents($tempScript, $scriptContent);
            $processes[$i] = popen("php $tempScript", 'r');
        }

        // Собираем результаты
        $results = [];
        foreach ($processes as $i => $pipe) {
            $results[$i] = fread($pipe, 1024);
            pclose($pipe);
        }

        // Анализируем результаты
        $stats = [
            'SUCCESS' => 0,
            'CONFLICT' => 0,
            'ERROR' => 0
        ];

        foreach ($results as $i => $result) {
            $result = trim($result);

            if (strpos($result, 'SUCCESS') !== false) {
                $stats['SUCCESS']++;
                echo "   Запрос " . ($i + 1) . ": ✅ УСПЕХ\n";
            } elseif (strpos($result, 'CONFLICT') !== false) {
                $stats['CONFLICT']++;
                echo "   Запрос " . ($i + 1) . ": ⚠️ КОНФЛИКТ\n";
            } else {
                $stats['ERROR']++;
                echo "   Запрос " . ($i + 1) . ": ❌ ОШИБКА ($result)\n";
            }
        }

        // Выводим статистику
        echo "\n📊 ИТОГИ ТЕСТИРОВАНИЯ:\n";
        echo "   ──────────────────────\n";
        echo "   ✅ Успешных запросов: {$stats['SUCCESS']}\n";
        echo "   ⚠️  Конфликтов: {$stats['CONFLICT']}\n";
        echo "   ❌ Ошибок: {$stats['ERROR']}\n";

        // Анализ
        echo "\n🔍 АНАЛИЗ РЕЗУЛЬТАТА:\n";
        if ($stats['SUCCESS'] === 1 && $stats['CONFLICT'] === 4) {
            $this->printSuccess("ТЕСТ ПРОЙДЕН! Блокировка FOR UPDATE работает");
        } elseif ($stats['SUCCESS'] > 1) {
            $this->printError("ТЕСТ НЕ ПРОЙДЕН! Обнаружена гонка данных");
        } elseif ($stats['SUCCESS'] === 0 && $stats['CONFLICT'] === 5) {
            $this->printWarning("Все запросы получили конфликт");
        }

        // Удаляем временные файлы
        foreach ($tempFiles as $file) {
            @unlink($file);
        }
    }

    /**
     * Проверка финального статуса
     */
    private function checkFinalStatus($pdo)
    {
        echo "\n🔍 Проверка финального статуса...\n";

        $stmt = $pdo->prepare("SELECT status FROM requests WHERE id = :id");
        $stmt->execute(['id' => $this->requestId]);
        $status = $stmt->fetchColumn();

        $this->printInfo("Финальный статус в БД: {$status}");

        if ($status === 'in_progress') {
            $this->printSuccess("Статус успешно изменен на in_progress");
        } elseif ($status === 'assigned') {
            $this->printWarning("Статус не изменился, остался assigned");
        }
    }
}

// Запуск теста
$test = new RaceConditionTest();
$test->run();

echo "\n";

```