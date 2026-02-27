<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #5a67d8;
        }
        .error {
            color: #e53e3e;
            font-size: 14px;
            margin-top: 5px;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .role-description {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
<div class="register-container">
    <h2>Регистрация</h2>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="form-group">
            <label for="fio">ФИО</label>
            <input type="text" id="fio" name="fio" value="{{ old('fio') }}" required>
            @error('fio')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="login">Логин</label>
            <input type="text" id="login" name="login" value="{{ old('login') }}" required>
            @error('login')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Пароль</label>
            <input type="password" id="password" name="password" required>
            @error('password')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">Подтверждение пароля</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required>
        </div>

        <div class="form-group">
            <label for="role">Роль</label>
            <select id="role" name="role" required>
                <option value="">Выберите роль</option>
                <option value="dispatcher" {{ old('role') == 'dispatcher' ? 'selected' : '' }}>Диспетчер</option>
                <option value="master" {{ old('role') == 'master' ? 'selected' : '' }}>Мастер</option>
            </select>
            <div class="role-description">
                <strong>Диспетчер:</strong> принимает заявки и назначает мастеров<br>
                <strong>Мастер:</strong> выполняет заявки и меняет их статус
            </div>
            @error('role')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit">Зарегистрироваться</button>
    </form>

    <div class="login-link">
        Уже есть аккаунт? <a href="{{ route('login') }}">Войти</a>
    </div>
</div>
</body>
</html>
