<?php

namespace App\Http\Controllers;

use App\Models\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
     * Изменить статус заявки (с защитой от гонок)
     * Безопасно при параллельных запросах
     */
    public function updateStatus(HttpRequest $request, $requestId)
    {
        $masterId = Auth::id();
        $newStatus = $request->status;

        // Валидация статуса
        if (!in_array($newStatus, ['assigned', 'in_progress', 'done', 'canceled'])) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Недопустимый статус'], 400);
            }
            return redirect()->back()->with('error', 'Недопустимый статус');
        }

        // Используем транзакцию с блокировкой для защиты от гонок
        return DB::transaction(function () use ($requestId, $masterId, $newStatus, $request) {
            // Блокируем строку для обновления (SELECT ... FOR UPDATE)
            $requestModel = Request::where('id', $requestId)
                ->where('assignedTo', $masterId)
                ->lockForUpdate()
                ->first();

            if (!$requestModel) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Заявка не найдена'], 404);
                }
                return redirect()->back()->with('error', 'Заявка не найдена');
            }

            // Проверяем возможность перехода в новый статус
            if (!$this->canTransition($requestModel->status, $newStatus)) {
                $error = "Невозможно изменить статус с '{$requestModel->status}' на '{$newStatus}'";

                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => $error,
                        'current_status' => $requestModel->status
                    ], 409); // 409 Conflict
                }

                return redirect()->back()->with('error', $error);
            }

            // Обновляем статус
            $requestModel->status = $newStatus;
            $requestModel->save();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => 'Статус заявки обновлен',
                    'request' => $requestModel
                ], 200);
            }

            return redirect()->back()->with('success', 'Статус заявки обновлен');
        }, 5); // Таймаут транзакции 5 секунд
    }
    public function getRequestStatus($requestId)
    {
        $request = Request::where('id', $requestId)
            ->where('assignedTo', Auth::id())
            ->first();

        if (!$request) {
            return response()->json(['error' => 'Заявка не найдена'], 404);
        }

        return response()->json([
            'status' => $request->status
        ]);
    }
    /**
     * Проверка возможности перехода между статусами
     */
    private function canTransition($currentStatus, $newStatus)
    {
        // Если статус не меняется - разрешаем
        if ($currentStatus === $newStatus) {
            return true;
        }

        $allowedTransitions = [
            'assigned' => ['in_progress', 'canceled'],
            'in_progress' => ['assigned', 'done', 'canceled'],
            'new' => [],
            'done' => [],
            'canceled' => [],
        ];

        return in_array($newStatus, $allowedTransitions[$currentStatus] ?? []);
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
