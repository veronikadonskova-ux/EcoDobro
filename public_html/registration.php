<?php

$host = 'localhost';
$dbname = 'nngasu5804_ecodobro';     
$user = 'nngasu5804_ecodobro';       
$password = 'Mu6hgKE5';     

$error_message = '';
$success_message = '';

// Обработка формы регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Подключаемся к базе данных
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        $error_message = "Ошибка подключения к базе данных: " . $e->getMessage();
    }
    
    // Если подключение успешно, обрабатываем регистрацию
    if (empty($error_message)) {
        $login = trim($_POST['login'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password_input = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirmPassword'] ?? '';
        
        $errors = [];
        
        // ========== ВАЛИДАЦИЯ ==========
        
        if (empty($login)) {
            $errors[] = 'Логин не может быть пустым';
        } elseif (strlen($login) < 3) {
            $errors[] = 'Логин должен содержать не менее 3 символов';
        } elseif (!preg_match('/^[a-zA-Z0-9_а-яА-ЯёЁ]+$/u', $login)) {
            $errors[] = 'Логин может содержать только буквы, цифры и знак подчеркивания';
        }
        
        if (empty($email)) {
            $errors[] = 'Email не может быть пустым';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Введите корректный email адрес';
        }
        
        if (empty($password_input)) {
            $errors[] = 'Пароль не может быть пустым';
        } elseif (strlen($password_input) < 6) {
            $errors[] = 'Пароль должен содержать не менее 6 символов';
        } elseif (!preg_match('/[A-ZА-ЯЁ]/', $password_input)) {
            $errors[] = 'Пароль должен содержать хотя бы одну заглавную букву';
        } elseif (!preg_match('/[a-zа-яё]/', $password_input)) {
            $errors[] = 'Пароль должен содержать хотя бы одну строчную букву';
        } elseif (!preg_match('/[0-9]/', $password_input)) {
            $errors[] = 'Пароль должен содержать хотя бы одну цифру';
        }
        
        if (empty($confirm_password)) {
            $errors[] = 'Подтверждение пароля не может быть пустым';
        } elseif ($password_input !== $confirm_password) {
            $errors[] = 'Пароли не совпадают';
        }
        
        // Проверка на существование пользователя
        if (empty($errors)) {
            try {
                // Проверка логина
                $stmt = $pdo->prepare("SELECT id FROM vhod WHERE login = ?");
                $stmt->execute([$login]);
                if ($stmt->fetch()) {
                    $errors[] = 'Пользователь с таким логином уже существует';
                }
                
                // Проверка email
                $stmt = $pdo->prepare("SELECT id FROM vhod WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $errors[] = 'Пользователь с таким email уже существует';
                }
            } catch(PDOException $e) {
                $errors[] = 'Ошибка базы данных: ' . $e->getMessage();
            }
        }
        
        // Если ошибок нет - регистрируем
        // Если ошибок нет - регистрируем
if (empty($errors)) {
    try {
        $hashedPassword = password_hash($password_input, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO vhod (login, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$login, $email, $hashedPassword]);
        $success_message = 'Регистрация прошла успешно!';
        echo '<meta http-equiv="refresh" content="2;url=vhod.php">';
    } catch(PDOException $e) {
        $error_message = 'Ошибка при регистрации: ' . $e->getMessage();
    }
} else {
    $error_message = implode('<br>', $errors);
}
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ЭкоДобро — Регистрация</title>
    <link rel="stylesheet" href="css/css.css">
    <link rel="icon" type="image/png" href="img/logotype.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Arsenal:wght@400;700&family=Montserrat:wght@400;700;800&display=swap" rel="stylesheet">
</head>
<body>

<!-- Фон на всю страницу -->
<div class="hero">
    <img class="hero-bg-img" src="img/background2.jpg" alt="Фон">
    <div class="hero-overlay"></div>
    
    <!-- Контейнер для центрирования формы -->
    <div class="center-container">
        
        <!-- Форма регистрации - по центру экрана -->
        <div class="register-form">
            
            <!-- Заголовок РЕГИСТРАЦИЯ -->
            <h1 class="form-title">РЕГИСТРАЦИЯ</h1>
            
            <!-- Вывод сообщений -->
            <?php if (!empty($error_message)): ?>
                <div style="background: rgba(255,100,100,0.3); color: #fff; padding: 12px; border-radius: 30px; margin-bottom: 20px; text-align: center; font-family: 'Arsenal', sans-serif;"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div style="background: rgba(100,255,100,0.3); color: #fff; padding: 12px; border-radius: 30px; margin-bottom: 20px; text-align: center; font-family: 'Arsenal', sans-serif;"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
            
                <!-- Поле Логин -->
                <div class="input-field">
                    <input type="text" name="login" placeholder="Логин" class="input-login-field" value="<?php echo htmlspecialchars($_POST['login'] ?? ''); ?>">
                </div>
                
                <!-- Поле E-mail -->
                <div class="input-field">
                    <input type="email" name="email" placeholder="E-mail" class="input-email-field" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <!-- Поле Пароль -->
                <div class="input-field password-field-wrapper">
                     <input type="password" name="password" placeholder="Пароль" required id="loginPassword">
                     <button type="button" class="toggle-password-btn" id="togglePasswordBtn">👁️</button>
                </div>
                
                <!-- Поле Подтверждение пароля -->
                <div class="input-field">
                    <input type="password" name="confirmPassword" placeholder="Подтвердить пароль" class="input-confirm-field">
                </div>
                
                <!-- Текст "Есть аккаунт?" и ссылка "Войти" -->
                <div class="login-link">
                    <span>Есть аккаунт?</span>
                    <a href="vhod.php">Войти</a>
                </div>
                
                <!-- Кнопка Зарегистрироваться -->
                <button type="submit" class="register-btn">
                    Зарегистрироваться
                </button>
            </form>
            
        </div>
    </div>
</div>
<script>
    (function() {
        const passwordInput = document.getElementById('loginPassword');
        const toggleBtn = document.getElementById('togglePasswordBtn');
        
        if (passwordInput && toggleBtn) {
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.textContent = type === 'password' ? '👁️' : '🙈';
            });
        }
    })();
</script>
</body>
</html>