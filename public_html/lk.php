<?php
session_start();

// Настройки базы данных
$host = 'localhost';
$dbname = 'nngasu5804_ecodobro';
$user = 'nngasu5804_ecodobro';
$pass = 'Mu6hgKE5';

$wheelSectors = [];
$username = '';
$email = '';
$calendar_events = []; // 👈 НОВАЯ ПЕРЕМЕННАЯ ДЛЯ МЕРОПРИЯТИЙ КАЛЕНДАРЯ

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: vhod.php");
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.html');
    exit();
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // ========== 1. ПОЛУЧАЕМ ДАННЫЕ ПОЛЬЗОВАТЕЛЯ ==========
    $userId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT login, email FROM vhod WHERE id = ?");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($userData) {
        $username = $userData['login'];
        $email = $userData['email'];
    } else {
        header("Location: vhod.php");
        exit;
    }
    
    // ========== 2. ПОЛУЧАЕМ ЗАДАНИЯ ДЛЯ КОЛЕСА ФОРТУНЫ ==========
    $stmt = $pdo->prepare("SELECT task_short, task_title, task_description FROM wheel_tasks WHERE is_active = 1 ORDER BY RAND()");
    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($tasks as $task) {
        $wheelSectors[] = [
            'short' => $task['task_short'],
            'title' => $task['task_title'],
            'desc' => $task['task_description']
        ];
    }
    
    // ========== 3. НОВОЕ: ПОЛУЧАЕМ МЕРОПРИЯТИЯ ДЛЯ КАЛЕНДАРЯ ==========
    $stmt = $pdo->prepare("SELECT 
                                id, 
                                title, 
                                date, 
                                location, 
                                time, 
                                entry as price, 
                                link 
                            FROM events 
                            WHERE is_active = 1
                            ORDER BY date ASC");
    $stmt->execute();
    $calendar_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Передаём данные о мероприятиях в JavaScript (добавьте эту строку после получения $calendar_events)
    $calendarEventsJson = json_encode($calendar_events, JSON_UNESCAPED_UNICODE);
} catch(PDOException $e) {
    $wheelSectors = [];
    $calendar_events = [];
    error_log($e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ЭкоДобро — Личный кабинет</title>
    <link rel="icon" type="image/png" href="img/logotype.png">
    <link rel="stylesheet" href="css/css.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Arsenal:wght@400;700&family=Montserrat:wght@400;700;800&display=swap" rel="stylesheet">
</head>
<body>

<!-- Фоновый блок -->
<div class="hero-bg">
    <img alt="" src="img/Background.jpg" />
</div>
<div class="gradient-transition"></div>

<div class="content-container">
    <main>
        <nav class="menu-bg">
            <a href="about.html">О проекте</a>
            <a href="volunteer.php">Волонтерство</a>
            <a href="lichniy-vclad.html">Личный вклад</a>
            <a href="index.html" class="logo-link">
                <img alt="" src="img/logo.png" />
            </a>
            <a href="karta-SOS.html">Карта SOS</a>
            <a href="pynkty-priema.html">Пункты приема</a>
            <a href="events.php">Мероприятия</a>
        </nav>

        <div class="container">
            
            <div class="nav-wrapper">
                <a href="index.html" class="breadcrumb">Главная</a>
                <span class="breadcrumb-separator">›</span>
                <span class="breadcrumb-current">Личный кабинет</span>
            </div>
            
            <!-- Заголовок и кнопка выхода -->
            <div class="header-row">
                <h1 class="main-title">ЛИЧНЫЙ КАБИНЕТ</h1>
                <button class="logout-btn" onclick="window.location.href='?logout=1'">
    <img alt="Выход" src="img/дверь.png" />
</button>
            </div>
            
            <!-- ЛИЧНЫЕ ДАННЫЕ + КАЛЕНДАРЬ В ОДНОЙ СТРОКЕ -->
            <div class="profile-with-calendar">
                
                <!-- Блок личных данных -->
                <div id="personal" class="profile-card">
                    <h2 class="profile-title">Личные данные</h2>
                    
                    <div class="profile-field">
                        <label class="profile-label">Ваш логин</label>
                        <div class="profile-input">
                            <?= htmlspecialchars($username) ?>
                        </div>
                    </div>
                    
                    <div class="profile-field">
                        <label class="profile-label">Ваш e-mail</label>
                        <div class="profile-input">
                            <?= htmlspecialchars($email) ?>
                        </div>
                    </div>

                    <button class="change-password" id="changePasswordBtn">Изменить пароль</button>
                </div>
                
                <!-- ==================== КАЛЕНДАРЬ ЭКОДОБРО ==================== -->
                <div class="eco-calendar-container">
                    <div class="eco-calendar glass-panel">
                        
                        <!-- Верхний овал: месяц и год -->
                        <div class="calendar-header">
                            <button id="prevMonth" class="nav-btn">←</button>
                            
                            <div class="month-year-oval">
                                <span id="monthYearDisplay">Месяц Год</span>
                            </div>
                            
                            <button id="nextMonth" class="nav-btn">→</button>
                        </div>

                        <!-- Дни недели -->
                        <div class="weekdays">
                            <div>ПН</div>
                            <div>ВТ</div>
                            <div>СР</div>
                            <div>ЧТ</div>
                            <div>ПТ</div>
                            <div>СБ</div>
                            <div>ВС</div>
                        </div>

                        <!-- Дни месяца -->
                        <div id="calendarDays" class="calendar-days"></div>

                        <!-- Нижний овал: Задание недели -->
                        <div class="week-task-oval">
                            <span>Задание недели</span>
                        </div>
                        
                    </div>
                </div>
                
            </div> <!-- закрываем profile-with-calendar -->
            
            <!-- Интерактивное дерево -->
            <div id="tree" class="tree-section">
                <div id="treeWidget" class="tree-widget"></div>
            </div>

            <!-- КОЛЕСО ФОРТУНЫ -->
            <div id="wheel" class="wheel-section">
                <div id="wheelWidget" style="margin: 40px auto; max-width: 500px;"></div>
            </div>

            <!-- Подвал -->
            <div class="footer">
                <div class="footer-column">
                    <h5>Команда</h5>
                    <p>Анна Аксенова<br>Алина Воробьева<br>Вероника Донскова</p>
                </div>
                <div class="footer-column">
                    <h5>Информация</h5>
                    <p><a href="about.html">О нас</a><br><a href="events.php">Мероприятия</a><br><a href="volouteering.html">Волонтерство</a><br><a href="pynkty-priema.html">Пункты приема</a></p>
                </div>
                <div class="footer-column">
                    <h5>Контакты</h5>
                    <p>Электронная почта:<br>EcoKindness@gmail.com</p>
                </div>
            </div>
        </div> <!-- закрываем container -->
    </main>
</div>

<!-- Модальное окно для смены пароля -->
<div id="passwordModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closePasswordModal()">&times;</span>
        <h3>Смена пароля</h3>
        <form id="passwordForm" method="POST" action="change_password.php">
            <label for="currentPassword">Текущий пароль:</label>
            <input type="password" id="currentPassword" name="currentPassword" required>
            
            <label for="newPassword">Новый пароль:</label>
            <input type="password" id="newPassword" name="newPassword" required>
            
            <label for="confirmNewPassword">Подтвердите новый пароль:</label>
            <input type="password" id="confirmNewPassword" name="confirmNewPassword" required>
            
            <button type="submit" class="submit-btn">Сохранить</button>
        </form>
    </div>
</div>

<script>
    // Передаем данные из PHP в JavaScript
    const WHEEL_SECTORS_FROM_PHP = <?php echo json_encode($wheelSectors, JSON_UNESCAPED_UNICODE); ?>;
    console.log("Задания из БД:", WHEEL_SECTORS_FROM_PHP);
    
    // Если нет заданий из БД, используем стандартные
    if (WHEEL_SECTORS_FROM_PHP.length === 0) {
        window.WHEEL_SECTORS = [
            { short: "Задание 1", title: "Сортировка мусора", desc: "Рассортируй домашний мусор: пластик, бумагу, стекло и органику." },
            { short: "Задание 2", title: "Экономия воды", desc: "Принимай душ на 2 минуты меньше обычного." },
            { short: "Задание 3", title: "Многоразовая бутылка", desc: "Используй многоразовую бутылку для воды." },
            { short: "Задание 4", title: "Сбор батареек", desc: "Найди ближайший пункт приёма батареек." },
            { short: "Задание 5", title: "Посади растение", desc: "Посади цветок или дерево." },
            { short: "Задание 6", title: "Экосумка", desc: "Сходи в магазин с эко-сумкой." },
            { short: "Задание 7", title: "Экономия энергии", desc: "Выключай свет, когда он не нужен." },
            { short: "Задание 8", title: "Уборка территории", desc: "Прими участие в субботнике." },
            { short: "Задание 9", title: "Поделись знаниями", desc: "Расскажи друзьям об экопривычках." }
        ];
    } else {
        window.WHEEL_SECTORS = WHEEL_SECTORS_FROM_PHP;
    }
    
    // Функция выхода
    function logout() {
        localStorage.removeItem('userLoggedIn');
        localStorage.removeItem('userLogin');
        localStorage.removeItem('userEmail');
        window.location.href = 'vhod.php';
    }
    
    // Модальное окно
    function closePasswordModal() {
        document.getElementById('passwordModal').style.display = 'none';
    }
    
    document.getElementById('changePasswordBtn')?.addEventListener('click', function() {
        document.getElementById('passwordModal').style.display = 'block';
    });
    
    document.getElementById('passwordForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmNewPassword').value;
        
        if (newPassword !== confirmPassword) {
            alert('Пароли не совпадают!');
            return;
        }
        
        const formData = new FormData(this);
        const response = await fetch('change_password.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            alert('Пароль успешно изменен!');
            closePasswordModal();
        } else {
            alert(result.error || 'Ошибка при смене пароля');
        }
    });
</script>
<script>
    // Данные о мероприятиях из базы данных для календаря
    const calendarEventsData = <?php echo $calendarEventsJson; ?>;
    console.log("Мероприятия загружены:", calendarEventsData.length);
</script>
<script src="js/lk.js"></script>
</body>
</html>