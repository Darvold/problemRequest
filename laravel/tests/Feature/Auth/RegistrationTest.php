<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Тест успешной регистрации диспетчера
     */
    public function test_dispatcher_can_register(): void
    {
        $response = $this->post(route('register'), [
            'fio' => 'Иванов Иван Иванович',
            'login' => 'ivanov_test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'dispatcher',
        ]);

        // Проверяем редирект на дашборд диспетчера
        $response->assertRedirect(route('dispatcher.dashboard'));
        
        // Проверяем, что пользователь создан в базе
        $this->assertDatabaseHas('users', [
            'login' => 'ivanov_test',
            'fio' => 'Иванов Иван Иванович',
            'role' => 'dispatcher',
        ]);

        // Проверяем, что пользователь авторизован
        $this->assertAuthenticated();
    }

    /**
     * Тест успешной регистрации мастера
     */
    public function test_master_can_register(): void
    {
        $response = $this->post(route('register'), [
            'fio' => 'Петров Петр Петрович',
            'login' => 'petrov_test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'master',
        ]);

        // Проверяем редирект на дашборд мастера
        $response->assertRedirect(route('master.dashboard'));
        
        // Проверяем, что пользователь создан в базе
        $this->assertDatabaseHas('users', [
            'login' => 'petrov_test',
            'fio' => 'Петров Петр Петрович',
            'role' => 'master',
        ]);
    }

    /**
     * Тест валидации при регистрации
     */
    public function test_registration_requires_all_fields(): void
    {
        // Отправляем пустую форму
        $response = $this->post(route('register'), []);

        // Проверяем, что есть ошибки валидации
        $response->assertSessionHasErrors(['fio', 'login', 'password', 'role']);
    }

    /**
     * Тест уникальности логина
     */
    public function test_login_must_be_unique(): void
    {
        // Создаем пользователя
        User::factory()->create([
            'login' => 'existing_user',
        ]);

        // Пытаемся зарегистрироваться с таким же логином
        $response = $this->post(route('register'), [
            'fio' => 'Тестовый Тест',
            'login' => 'existing_user',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'dispatcher',
        ]);

        // Проверяем ошибку валидации
        $response->assertSessionHasErrors(['login']);
    }

    /**
     * Тест подтверждения пароля
     */
    public function test_password_must_be_confirmed(): void
    {
        $response = $this->post(route('register'), [
            'fio' => 'Тестовый Тест',
            'login' => 'test_user',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
            'role' => 'dispatcher',
        ]);

        $response->assertSessionHasErrors(['password']);
    }
}