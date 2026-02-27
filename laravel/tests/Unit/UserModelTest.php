<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест создания пользователя
     */
    public function test_user_can_be_created(): void
    {
        $user = User::factory()->create([
            'fio' => 'Тестовый Пользователь',
            'login' => 'test_login',
            'role' => 'dispatcher',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'fio' => 'Тестовый Пользователь',
            'login' => 'test_login',
            'role' => 'dispatcher',
        ]);
    }

    /**
     * Тест связи с заявками (User has many Requests)
     */
    public function test_user_has_many_requests(): void
    {
        $master = User::factory()->master()->create();
        
        // Создаем 3 заявки для этого мастера
        Request::factory()->count(3)->create([
            'assignedTo' => $master->id,
        ]);

        // Проверяем, что связь работает
        $this->assertCount(3, $master->assignedRequests);
        $this->assertInstanceOf(Request::class, $master->assignedRequests->first());
    }

    /**
     * Тест ролей пользователя
     */
    public function test_user_has_role(): void
    {
        $dispatcher = User::factory()->dispatcher()->create();
        $master = User::factory()->master()->create();

        $this->assertEquals('dispatcher', $dispatcher->role);
        $this->assertEquals('master', $master->role);
    }
}