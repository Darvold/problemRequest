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
