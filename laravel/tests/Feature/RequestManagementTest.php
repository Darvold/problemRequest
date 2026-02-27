<?php

namespace Tests\Feature;

use App\Models\Request;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RequestManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Тест создания заявки через форму
     */
    public function test_dispatcher_can_create_request_via_form(): void
    {
        // Создаем и авторизуем диспетчера
        $dispatcher = User::factory()->dispatcher()->create();
        $this->actingAs($dispatcher);

        // Получаем форму создания
        $response = $this->get(route('requests.create'));
        $response->assertStatus(200);

        // Отправляем данные формы
        $response = $this->post(route('requests.store'), [
            'clientName' => 'Тестовый Клиент',
            'phone' => '+7 (999) 999-99-99',
            'address' => 'ул. Тестовая, д. 1',
            'problemText' => 'Тестовая проблема для проверки',
        ]);

        $response->assertRedirect(route('dispatcher.dashboard'));

        // Проверяем, что заявка создана со статусом 'new'
        $this->assertDatabaseHas('requests', [
            'clientName' => 'Тестовый Клиент',
            'phone' => '+7 (999) 999-99-99',
            'address' => 'ул. Тестовая, д. 1',
            'status' => 'new',
            'assignedTo' => null,
        ]);
    }
}
