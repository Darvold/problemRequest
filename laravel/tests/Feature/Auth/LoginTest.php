<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест успешного входа диспетчера
     */
    public function test_dispatcher_can_login(): void
    {
        // Создаем диспетчера
        $user = User::factory()->dispatcher()->create([
            'login' => 'dispatcher_test',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login'), [
            'login' => 'dispatcher_test',
            'password' => 'password123',
        ]);

        // Проверяем редирект на дашборд диспетчера
        $response->assertRedirect(route('dispatcher.dashboard'));

        // Проверяем, что пользователь авторизован
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Тест успешного входа мастера
     */
    public function test_master_can_login(): void
    {
        // Создаем мастера
        $user = User::factory()->master()->create([
            'login' => 'master_test',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login'), [
            'login' => 'master_test',
            'password' => 'password123',
        ]);

        // Проверяем редирект на дашборд мастера
        $response->assertRedirect(route('master.dashboard'));

        // Проверяем, что пользователь авторизован
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Тест входа с неверным паролем
     */
    public function test_user_cannot_login_with_invalid_password(): void
    {
        // Создаем пользователя
        User::factory()->create([
            'login' => 'test_user',
            'password' => bcrypt('correct_password'),
        ]);

        $response = $this->post(route('login'), [
            'login' => 'test_user',
            'password' => 'wrong_password',
        ]);

        // Проверяем, что есть ошибка
        $response->assertSessionHasErrors(['login']);

        // Проверяем, что пользователь не авторизован
        $this->assertGuest();
    }

    /**
     * Тест входа с несуществующим логином
     */
    public function test_user_cannot_login_with_nonexistent_login(): void
    {
        $response = $this->post(route('login'), [
            'login' => 'nonexistent_user',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['login']);
        $this->assertGuest();
    }

    /**
     * Тест выхода из системы
     */
    public function test_user_can_logout(): void
    {
        // Создаем и авторизуем пользователя
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
