<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание новой заявки</title>
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
        .nav-user span {
            color: #4a5568;
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
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
        }
        h2 {
            color: #2d3748;
            margin-bottom: 30px;
            font-size: 24px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 15px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #4a5568;
            font-weight: 500;
        }
        .required::after {
            content: " *";
            color: #e53e3e;
        }
        input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        input:focus, textarea:focus {
            outline: none;
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        .error {
            color: #e53e3e;
            font-size: 14px;
            margin-top: 5px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        .btn-primary {
            background: #4299e1;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-primary:hover {
            background: #3182ce;
        }
        .btn-secondary {
            background: #cbd5e0;
            color: #2d3748;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: background 0.3s;
        }
        .btn-secondary:hover {
            background: #a0aec0;
        }
        .info-box {
            background: #ebf8ff;
            border-left: 4px solid #4299e1;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 4px;
        }
        .info-box p {
            color: #2c5282;
            font-size: 14px;
        }
        .info-box strong {
            color: #2b6cb0;
        }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="nav-brand">Система управления заявками</div>
    <div class="nav-user">
        <span>{{ Auth::user()->fio }} ({{ Auth::user()->role === 'dispatcher' ? 'Диспетчер' : 'Мастер' }})</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn">Выйти</button>
        </form>
    </div>
</nav>

<div class="container">
    <div class="card">
        <h2>📝 Создание новой заявки</h2>

        <div class="info-box">
            <p><strong>ℹ️ Информация:</strong> После создания заявка автоматически получит статус "Новая" и будет доступна для назначения мастерам.</p>
        </div>

        @if ($errors->any())
            <div style="background: #fed7d7; border-left: 4px solid #e53e3e; padding: 15px; margin-bottom: 25px; border-radius: 4px;">
                <ul style="color: #c53030; margin-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('requests.store') }}">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="clientName" class="required">ФИО клиента</label>
                    <input type="text" id="clientName" name="clientName" value="{{ old('clientName') }}"
                           placeholder="Иванов Иван Иванович" required>
                    @error('clientName')
                    <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone" class="required">Телефон</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                           placeholder="+7 (999) 123-45-67" required>
                    @error('phone')
                    <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="address" class="required">Адрес</label>
                <input type="text" id="address" name="address" value="{{ old('address') }}"
                       placeholder="ул. Ленина, д. 10, кв. 25" required>
                @error('address')
                <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="problemText" class="required">Описание проблемы</label>
                <textarea id="problemText" name="problemText" placeholder="Подробно опишите проблему..." required>{{ old('problemText') }}</textarea>
                @error('problemText')
                <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Статус (будет установлен автоматически)</label>
                <input type="text" value="Новая заявка" disabled style="background: #f7fafc; color: #718096;">
            </div>

            <div class="button-group">
                <button type="submit" class="btn-primary">✅ Создать заявку</button>
                <a href="{{ route('dispatcher.dashboard') }}" class="btn-secondary">↩️ Отмена</a>
            </div>
        </form>
    </div>
</div>

<script>
    // Простая маска для телефона (опционально)
    document.getElementById('phone').addEventListener('input', function(e) {
        let x = e.target.value.replace(/\D/g, '').match(/(\d{0,1})(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);
        e.target.value = !x[2] ? x[1] : '+7 (' + x[2] + ') ' + x[3] + (x[4] ? '-' + x[4] : '') + (x[5] ? '-' + x[5] : '');
    });
</script>
</body>
</html>
