<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель диспетчера</title>
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
        .requests-table {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .requests-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .requests-table th {
            background: #f7fafc;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #2d3748;
            border-bottom: 2px solid #e2e8f0;
        }
        .requests-table td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-new { background: #fefcbf; color: #97550e; }
        .status-assigned { background: #bee3f8; color: #2c5282; }
        .status-in_progress { background: #feebc8; color: #c05621; }
        .status-done { background: #c6f6d5; color: #276749; }
        .status-canceled { background: #fed7d7; color: #9b2c2c; }
        .assign-form {
            display: flex;
            gap: 10px;
        }
        .assign-form select {
            padding: 6px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }
        .assign-form button {
            padding: 6px 12px;
            background: #4299e1;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .assign-form button:hover {
            background: #3182ce;
        }
        h2 {
            margin-bottom: 20px;
            color: #2d3748;
        }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="nav-brand">Диспетчерская панель</div>
    <div class="nav-user">
        <span>{{ Auth::user()->fio }} ({{ Auth::user()->role === 'dispatcher' ? 'Диспетчер' : 'Мастер' }})</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn">Выйти</button>
        </form>
    </div>
</nav>

<div class="container">
    <h2>Статистика</h2>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $stats['new_requests'] }}</div>
            <div class="stat-label">Новых заявок</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['assigned_requests'] }}</div>
            <div class="stat-label">Назначено мастерам</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['in_progress_requests'] }}</div>
            <div class="stat-label">В работе</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total_requests'] }}</div>
            <div class="stat-label">Всего заявок</div>
        </div>
    </div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Последние заявки</h2>
        <a href="{{ route('requests.create') }}" style="
        background: #48bb78;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: background 0.3s;
    " onmouseover="this.style.background='#38a169'" onmouseout="this.style.background='#48bb78'">
            ➕ Создать заявку
        </a>
    </div>
    <div class="requests-table">
        <table>
            <thead>
            <tr>
                <th>Клиент</th>
                <th>Телефон</th>
                <th>Адрес</th>
                <th>Проблема</th>
                <th>Статус</th>
                <th>Мастер</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            @foreach($recentRequests as $request)
                <tr>
                    <td>{{ $request->clientName }}</td>
                    <td>{{ $request->phone }}</td>
                    <td>{{ $request->address }}</td>
                    <td>{{ Str::limit($request->problemText, 30) }}</td>
                    <td>
                            <span class="status-badge status-{{ $request->status }}">
                                @switch($request->status)
                                    @case('new') Новая @break
                                    @case('assigned') Назначена @break
                                    @case('in_progress') В работе @break
                                    @case('done') Выполнена @break
                                    @case('canceled') Отменена @break
                                @endswitch
                            </span>
                    </td>
                    <td>{{ $request->master->fio ?? 'Не назначен' }}</td>
                    <td>
                        @if($request->status === 'new')
                            <form class="assign-form" method="POST" action="{{ route('dispatcher.assign', $request->id) }}">
                                @csrf
                                <select name="master_id" required>
                                    <option value="">Выбрать мастера</option>
                                    @foreach($masters as $master)
                                        <option value="{{ $master->id }}">{{ $master->fio }}</option>
                                    @endforeach
                                </select>
                                <button type="submit">Назначить</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
