<?php
session_start();

$host = 'localhost';
$dbname = 'nngasu5804_ecodobro';
$user = 'nngasu5804_ecodobro';
$pass = 'Mu6hgKE5';

$message = '';
$error = '';
$action = isset($_GET['action']) ? $_GET['action'] : 'login';

if (isset($_SESSION['user_id'])) {
    header('Location: lk.php');
    exit();
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // ========== 1. ВХОД ==========
    if ($action == 'login' && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_action'])) {
        $login    = trim($_POST['login']);
        $email    = trim($_POST['email']);
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM vhod WHERE (login = ? OR email = ?) LIMIT 1");
        $stmt->execute([$login, $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['full_name']  = $user['full_name'];
            $_SESSION['login']      = $user['login'];
            $_SESSION['email']      = $user['email'];
            header("Location: lk.php");
            exit;
        } else {
            $error = "Неверный логин, email или пароль!";
        }
    }
    
    // ========== 2. ЗАПРОС ВОССТАНОВЛЕНИЯ ==========
    if ($action == 'forgot' && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['forgot_action'])) {
        $email = trim($_POST['email']);
        
        $stmt = $pdo->prepare("SELECT id FROM vhod WHERE email = ?");
        $stmt->execute([$email]);
        $userExists = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userExists) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $stmt = $pdo->prepare("UPDATE vhod SET reset_token = ?, reset_expires = ? WHERE email = ?");
            $stmt->execute([$token, $expires, $email]);
            
            // ВРЕМЕННО: показываем ссылку на экране (для теста)
            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "?action=reset&token=" . $token;
            $message = "<span class='reset-message-text'>Ссылка для сброса пароля:</span> <a href='$resetLink' class='reset-password-link'>$resetLink</a>";
        } else {
            $error = "Email не найден!";
        }
    }
    
// ========== 3. СБРОС ПАРОЛЯ ==========
if ($action == 'reset' && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_action'])) {
    $token = $_POST['token'];
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Проверки сложности пароля (КАК В РЕГИСТРАЦИИ)
    $passwordErrors = [];
    
    if (empty($newPassword)) {
        $passwordErrors[] = 'Пароль не может быть пустым';
    } elseif (strlen($newPassword) < 6) {
        $passwordErrors[] = 'Пароль должен содержать не менее 6 символов';
    } elseif (!preg_match('/[A-ZА-ЯЁ]/', $newPassword)) {
        $passwordErrors[] = 'Пароль должен содержать хотя бы одну заглавную букву';
    } elseif (!preg_match('/[a-zа-яё]/', $newPassword)) {
        $passwordErrors[] = 'Пароль должен содержать хотя бы одну строчную букву';
    } elseif (!preg_match('/[0-9]/', $newPassword)) {
        $passwordErrors[] = 'Пароль должен содержать хотя бы одну цифру';
    }
    
    if ($newPassword !== $confirmPassword) {
        $error = "Пароли не совпадают!";
    } elseif (!empty($passwordErrors)) {
        $error = implode("<br>", $passwordErrors);
    } else {
        $stmt = $pdo->prepare("SELECT email FROM vhod WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE vhod SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?");
            $stmt->execute([$hashedPassword, $token]);
            $message = "<div class='success-message'><span class='success-text'>Пароль изменён!</span> <a href='" . $_SERVER['SCRIPT_NAME'] . "' class='success-link'>Войти</a></div>";
        } else {
            $error = "Недействительная ссылка!";
        }
    }
}
    
} catch (PDOException $e) {
    $error = "Ошибка сервера: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>ЭкоДобро</title>
    <link rel="stylesheet" href="css/css.css">
    <link rel="icon" type="image/png" href="img/logotype.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Arsenal:wght@400;700&family=Montserrat:wght@400;700;800&display=swap" rel="stylesheet">
    <style>
        .forgot-link { text-align: center; margin: 10px 0 5px; }
        .forgot-link a { color: #4CAF50; text-decoration: none; font-size: 14px; }
        .back-link { text-align: center; margin-top: 15px; }
        .message { color: #27ae60; text-align: center; margin-bottom: 15px; }
        .error { color: #e74c3c; text-align: center; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="hero">
    <img class="hero-bg-img" src="img/background2.jpg" alt="Фон">
    <div class="hero-overlay"></div>
    
    <div class="center-container">
        <div class="login-form">
            
            <?php if ($action == 'login'): ?>
                <!-- ФОРМА ВХОДА -->
                <h1 class="form-title">ВХОД</h1>
                
                <?php if ($error): ?>
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
                
                <form method="POST" action="?action=login">
                    <input type="hidden" name="login_action" value="1">
                    
                    <div class="input-field">
                        <input type="text" name="login" placeholder="Логин" required>
                    </div>
                    
                    <div class="input-field">
                        <input type="email" name="email" placeholder="E-mail" required>
                    </div>
                    
                    <div class="input-field password-field-wrapper">
                     <input type="password" name="password" placeholder="Пароль" required id="loginPassword">
                     <button type="button" class="toggle-password-btn" id="togglePasswordBtn">👁️</button>
                    </div>
                    
                    <div class="register-link">
                        <span>Нет аккаунта?</span>
                        <a href="registration.php">Регистрация</a>
                    </div>

                    <div class="forgot-link-wrapper">
                        <a href="?action=forgot" class="forgot-link">Забыли пароль?</a>
                    </div>
                    
                    <button type="submit" class="login-btn">Войти</button>
                </form>
                
            <?php elseif ($action == 'forgot'): ?>
                <!-- ФОРМА ВОССТАНОВЛЕНИЯ -->
                <h1 class="form-title">ВОССТАНОВЛЕНИЕ</h1>
                
                <?php if ($message): ?>
                    <div class="message"><?= $message ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
                
                <form method="POST" action="?action=forgot">
                    <input type="hidden" name="forgot_action" value="1">
                    
                    <div class="input-field">
                        <input type="email" name="email" placeholder="Ваш E-mail" required>
                    </div>
                    
                    <button type="submit" class="login-btn_1">Отправить ссылку</button>
                    
                    <div class="back-link">
                        <a href="?action=login">← Вернуться ко входу</a>
                    </div>
                </form>
                
            <?php elseif ($action == 'reset'): ?>
                <!-- ФОРМА СБРОСА ПАРОЛЯ -->
                <h1 class="form-title">НОВЫЙ ПАРОЛЬ</h1>
                
                <?php 
                $valid = false;
                $token = isset($_GET['token']) ? $_GET['token'] : (isset($_POST['token']) ? $_POST['token'] : '');
                
                if ($token && !$message) {
                    try {
                        $stmt = $pdo->prepare("SELECT email FROM vhod WHERE reset_token = ? AND reset_expires > NOW()");
                        $stmt->execute([$token]);
                        if ($stmt->fetch()) {
                            $valid = true;
                        } else {
                            $error = "Недействительная или просроченная ссылка!";
                        }
                    } catch (PDOException $e) {
                        $error = "Ошибка проверки токена";
                    }
                }
                ?>
                
                <?php if ($message): ?>
                    <div class="message"><?= $message ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
                
                <?php if ($valid && !$message): ?>
                    <form method="POST" action="?action=reset">
                        <input type="hidden" name="reset_action" value="1">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                        
                        <div class="input-field">
                            <input type="password" name="password" placeholder="Новый пароль" required>
                        </div>
                        
                        <div class="input-field">
                            <input type="password" name="confirm_password" placeholder="Подтвердите пароль" required>
                        </div>
                        
                        <button type="submit" class="login-btn">Сохранить пароль</button>
                        
                        <div class="back-link">
                            <a href="?action=login">← Вернуться ко входу</a>
                        </div>
                    </form>
                <?php elseif (!$valid && !$message && !$error && $action == 'reset'): ?>
                    <div class="back-link">
                        <a href="?action=login">← Вернуться ко входу</a>
                    </div>
                <?php endif; ?>
                
            <?php endif; ?>
            
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