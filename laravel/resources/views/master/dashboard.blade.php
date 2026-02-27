<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель мастера</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #f7fafc;
        }
        .navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .nav-brand {
            font-size: 20px;
            font-weight: 600;
            color: #2d3748;
        }
        .nav-user {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .logout-btn {
            background: #e53e3e;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .logout-btn:hover {
            background: #c53030;
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #2d3748;
        }
        .stat-label {
            color: #718096;
            margin-top: 5px;
        }
        .requests-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        .request-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .client-name {
            font-weight: 600;
            color: #2d3748;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-assigned { background: #bee3f8; color: #2c5282; }
        .status-in_progress { background: #feebc8; color: #c05621; }
        .status-done { background: #c6f6d5; color: #276749; }
        .status-canceled { background: #fed7d7; color: #9b2c2c; }
        .request-detail {
            margin: 10px 0;
            color: #4a5568;
        }
        .request-detail strong {
            color: #2d3748;
        }
        .status-form {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        .status-form select {
            flex: 1;
            padding: 8px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }
        .status-form button {
            padding: 8px 16px;
            background: #4299e1;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .status-form button:hover {
            background: #3182ce;
        }
        .phone, .address {
            font-size: 14px;
            color: #718096;
        }
        h2 {
            margin-bottom: 20px;
            color: #2d3748;
        }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="nav-brand">Панель мастера</div>
    <div class="nav-user">
        <span>{{ Auth::user()->fio }} ({{ Auth::user()->role === 'dispatcher' ? 'Диспетчер' : 'Мастер' }})</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn">Выйти</button>
        </form>
    </div>
</nav>

<div class="container">
    <h2>Моя статистика</h2>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $stats['assigned'] }}</div>
            <div class="stat-label">Назначено</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['in_progress'] }}</div>
            <div class="stat-label">В работе</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['done'] }}</div>
            <div class="stat-label">Выполнено</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">Всего заявок</div>
        </div>
    </div>

    <h2>Мои заявки</h2>
    <div class="requests-grid">
        @forelse($myRequests as $request)
            <div class="request-card">
                <div class="request-header">
                    <span class="client-name">{{ $request->clientName }}</span>
                    <span class="status-badge status-{{ $request->status }}">
                        @switch($request->status)
                            @case('assigned') Назначена @break
                            @case('in_progress') В работе @break
                            @case('done') Выполнена @break
                            @case('canceled') Отменена @break
                        @endswitch
                    </span>
                </div>

                <div class="request-detail">
                    <strong>Телефон:</strong>
                    <span class="phone">{{ $request->phone }}</span>
                </div>

                <div class="request-detail">
                    <strong>Адрес:</strong>
                    <span class="address">{{ $request->address }}</span>
                </div>

                <div class="request-detail">
                    <strong>Проблема:</strong><br>
                    {{ $request->problemText }}
                </div>

                @if($request->status !== 'done' && $request->status !== 'canceled')
                    <form class="status-form" method="POST" action="{{ route('master.update-status', $request->id) }}">
                        @csrf
                        <select name="status" required>
                            <option value="assigned" {{ $request->status === 'assigned' ? 'selected' : '' }}>Назначена</option>
                            <option value="in_progress" {{ $request->status === 'in_progress' ? 'selected' : '' }}>В работе</option>
                            <option value="done">Выполнена</option>
                        </select>
                        <button type="submit">Обновить</button>
                    </form>
                @endif
            </div>
        @empty
            <p style="grid-column: 1/-1; text-align: center; color: #718096;">У вас пока нет назначенных заявок</p>
        @endforelse
    </div>
</div>
</body>
</html>
