<?php
session_start();

// Настройки базы данных
$host = 'localhost';
$dbname = 'nngasu5804_ecodobro';
$user = 'nngasu5804_ecodobro';
$pass = 'Mu6hgKE5';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $message = "";
    $messageType = "";
    $formSubmitted = false;

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_form'])) {
        
        $last_name = trim($_POST['last_name']);
        $first_name = trim($_POST['first_name']);
        $middle_name = trim($_POST['middle_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $organization = trim($_POST['organization']);
        
        $errors = [];
        
        if (empty($last_name)) $errors[] = "Поле Фамилия обязательно";
        if (empty($first_name)) $errors[] = "Поле Имя обязательно";
        if (empty($email)) $errors[] = "Поле Email обязательно";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Некорректный email";
        if (empty($phone)) $errors[] = "Поле Телефон обязательно";
        if (empty($organization)) $errors[] = "Выберите организацию";
        
        $photo_path = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['photo']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $upload_dir = "uploads/";
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $new_filename = time() . "_" . uniqid() . "." . $ext;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                    $photo_path = $upload_path;
                } else {
                    $errors[] = "Ошибка загрузки фото";
                }
            } else {
                $errors[] = "Разрешены JPG, JPEG, PNG, GIF";
            }
        }
        
        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO volunteers (last_name, first_name, middle_name, email, phone, organization, photo, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$last_name, $first_name, $middle_name, $email, $phone, $organization, $photo_path]);
            
            $message = "Ваша заявка успешно отправлена! Она будет рассмотрена в ближайшее время. С вами свяжутся по указанному телефону или email.";
            $messageType = "success";
            $formSubmitted = true;
        } else {
            $message = implode("<br>", $errors);
            $messageType = "error";
        }
    }
    
} catch(PDOException $e) {
    $message = "Ошибка подключения: " . $e->getMessage();
    $messageType = "error";
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ЭкоДобро — Волонтерство</title>
    <link rel="stylesheet" href="css/css.css">
    <link rel="icon" type="image/png" href="/img/logotype.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Arsenal:wght@400;700&family=Montserrat:wght@400;700;800&family=Grenze:wght@400;700;800;900&display=swap" rel="stylesheet">
    <style>
        .modal-message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .modal-message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .modal-message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .modal-content form input,
        .modal-content form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        .modal-content form input:focus,
        .modal-content form select:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
        }
        .photo-upload-block {
            margin: 10px 0;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        .upload-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
        .upload-btn:active {
            transform: translateY(0);
        }
        #uploadStatus {
            font-size: 13px;
            color: #4CAF50;
            font-weight: 500;
            background: #f0f0f0;
            padding: 8px 15px;
            border-radius: 20px;
            display: inline-block;
        }
        .save-btn {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 10px;
        }
        .save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            background: linear-gradient(135deg, #45a049 0%, #4CAF50 100%);
        }
        
        /* Кастомное диалоговое окно */
        .custom-dialog {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }
        .custom-dialog-content {
            background: white;
            margin: 15% auto;
            padding: 0;
            width: 90%;
            max-width: 450px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            animation: slideDown 0.3s;
            overflow: hidden;
        }
        .dialog-header {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .dialog-header h3 {
            margin: 0;
            font-size: 24px;
        }
        .dialog-body {
            padding: 30px;
            text-align: center;
        }
        .dialog-body p {
            font-size: 16px;
            line-height: 1.5;
            color: #333;
            margin-bottom: 20px;
        }
        .checkmark {
            font-size: 60px;
            color: #4CAF50;
            margin-bottom: 15px;
        }
        .dialog-close-btn {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .dialog-close-btn:hover {
            transform: scale(1.05);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>

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

            <div class="top-bar">
            <div class="nav-wrapper">
            <a href="index.html" class="breadcrumb">Главная</a>
            <span class="breadcrumb-separator">›</span>
            <span class="breadcrumb-current">Волонтерство </span>
        </div>
        
        <!-- ИКОНКА ПРОФИЛЯ -->
        <div class="profile-icon-container">
            <div id="profileIcon" class="profile-icon">
                <img src="img/profile.png" alt="Профиль">
            </div>
            <div id="profileDropdown" class="profile-dropdown">
                <a href="lk.php#personal" class="dropdown-item">
                    Личный кабинет
                </a>
                <a href="lk.php#tree" class="dropdown-item">
                    Интерактивное дерево
                </a>
                <a href="lk.php#wheel" class="dropdown-item">
                    Колесо заданий
                </a>
            </div>
        </div>
    </div>

            <h1 class="volunteer-main-title">ВОЛОНТЕРСТВО</h1>
            
            <div class="org-card">
                <div class="org-header">
                    <h2 class="org-title">Всероссийское общественное объединение волонтеров - экологов "Делай!"</h2>
                </div>
                <div class="org-content">
                    <div class="org-text">
                        <p>Всероссийская общественная организация волонтеров-экологов «Делай!» — одна из крупнейших организаций страны, объединяющая волонтеров-экологов во всех регионах России и помогающая системно развивать практики волонтерства в области охраны окружающей среды.</p>
                    </div>
                    <div class="org-images">
                        <div class="org-img">
                            <img alt="" src="img/делайфото.png" />
                        </div>
                    </div>
                </div>
                
                <div class="contact-wrapper contact-delai">
                    <div class="contact-item">
                        <div class="contact-icon-bg">
                            <img src="img/телефон.png" alt="Телефон" class="contact-icon">
                        </div>
                        <div class="contact-text-overlay">8(904)064-71-08</div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon-bg">
                            <img src="img/круг.png" alt="Сайт" class="contact-icon">
                        </div>
                        <div class="contact-text-overlay"><a href="https://ecodelai.ru/region">ecodelai.ru</a></div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon-bg">
                            <img src="img/почта.png" alt="Email" class="contact-icon">
                        </div>
                        <div class="contact-text-overlay"><a href="mailto:goryachev-97@list.ru">goryachev-97@list.ru</a></div>
                    </div>
                </div>
            </div>
            
            <div class="org-card">
                <h2 class="org-title">Центр просвещения и развития добровольчества «Точка Добра»</h2>
                <div class="org-text">
                    <p>“Мы комплексно подходим к экологическим проблемам: меняем культуру потребления жителей, влияем на создание грамотной инфраструктуры раздельного сбора, сотрудничаем с компаниями и даем рекомендации для законодательных органов.”</p>
                    <p><strong>На данный момент организовали и провели:</strong></p>
                    <ul class="org-list">
                        <li>1 Всероссийский онлайн форум экологических добровольцев «Сделаем!Россия»</li>
                        <li>2 экологических квест-игры «Чистые Игры»</li>
                        <li>более 15 онлайн-лекций на тему экологии и раздельного сбора мусора для студентов и школьников</li>
                        <li>более 5 турниров по экологической настольной игре «Хранители Земли»</li>
                    </ul>
                </div>
                
                <div class="contact-wrapper contact-point">
                    <div class="contact-item">
                        <div class="contact-icon-bg">
                            <img src="img/телефон.png" alt="Телефон" class="contact-icon">
                        </div>
                        <div class="contact-text-overlay">8(982)043-74-27</div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon-bg">
                            <img src="img/круг.png" alt="Сайт" class="contact-icon">
                        </div>
                        <div class="contact-text-overlay"><a href="http://dobropoint.ru">dobropoint.ru</a></div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon-bg">
                            <img src="img/почта.png" alt="Email" class="contact-icon">
                        </div>
                        <div class="contact-text-overlay"><a href="mailto:dobropoint@gmail.com">dobropoint@gmail.com</a></div>
                    </div>
                </div>
            </div>
            
            <div class="org-card">
                <h2 class="org-title">Движение «Экосистема»</h2>
                <div class="org-text">
                    <p>Движение объединяет экологические проекты, акции, мероприятия, сообщества и возможности от партнеров в единую экологическую повестку.</p>
                    <p>Цель движения - формирование у населения экологического мышления через бережное отношение к природе, традициям и своей стране.</p>
                    <p><strong>Члены движения принимали участие в:</strong></p>
                    <ul class="org-list">
                        <li>Международном экологическом клубе</li>
                        <li>Всероссийской акции "БУМБАТЛ"</li>
                        <li>Всероссийской акции "Марафон зеленых дел"</li>
                        <li>Экологической эстафете "ЭкоГТО"</li>
                    </ul>
                </div>
                
                <div class="contact-wrapper contact-eco">
                    <div class="contact-item">
                        <div class="contact-icon-bg">
                            <img src="img/телефон.png" alt="Телефон" class="contact-icon">
                        </div>
                        <div class="contact-text-overlay">8(953)553-01-77</div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon-bg">
                            <img src="img/круг.png" alt="Сайт" class="contact-icon">
                        </div>
                        <div class="contact-text-overlay"><a href="https://xn--80adffaaepcbv0ahr9bbr6q.xn--p1ai/">движениеэкосистема.рф</a></div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon-bg">
                            <img src="img/почта.png" alt="Email" class="contact-icon">
                        </div>
                        <div class="contact-text-overlay"><a href="mailto:saharnaja-kljukva@yandex.ru">saharnaja-kljukva@yandex.ru</a></div>
                    </div>
                </div>
            </div>
            
            <div class="org-card">
                <h2 class="org-title">Экологический центр «Дронт»</h2>
                <div class="org-text">
                    <p>Экологический центр в целом осуществляет координационные функции среди других неправительственных экологических групп Нижнего Новгорода, Нижегородской области и Поволжья. В результате накопленного опыта работы сложилось мощное экологическое информационное поле.</p>
                    <p>Одна из крупных проблем, постоянно находящаяся под пристальным вниманием центра — угроза подъема уровня Чебоксарского водохранилища. Именно благодаря деятельности «Дронта» при каждой попытке заговорить о подъеме его уровня против поднимаются массы нижегородцев.</p>
                </div>
                
                <div class="contact-wrapper contact-dront">
                    <div class="contact-item">
                        <div class="contact-icon-bg">
                            <img src="img/телефон.png" alt="Телефон" class="contact-icon">
                        </div>
                        <div class="contact-text-overlay">8(831)430-28-81</div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon-bg">
                            <img src="img/круг.png" alt="Сайт" class="contact-icon">
                        </div>
                        <div class="contact-text-overlay"><a href="https://dront.ru/">dront.ru</a></div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon-bg">
                            <img src="img/почта.png" alt="Email" class="contact-icon">
                        </div>
                        <div class="contact-text-overlay"><a href="mailto:dront@dront.ru">dront@dront.ru</a></div>
                    </div>
                </div>
            </div>
            
            <div class="volunteer-btn" onclick="openModal()">
                <span>СТАТЬ ВОЛОНТЕРОМ</span>
            </div>
            
        
        
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
        </div>
    </main>
</div>

<div id="volunteerModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h3>Стать волонтером</h3>
        
        <form id="volunteerForm" method="POST" action="" enctype="multipart/form-data">
            <input type="text" name="last_name" placeholder="Ваша фамилия" required>
            <input type="text" name="first_name" placeholder="Ваше имя" required>
            <input type="text" name="middle_name" placeholder="Ваше отчество">
            <input type="email" name="email" placeholder="Ваш email" required>
            <input type="tel" name="phone" placeholder="Ваш телефон" required>
            <select name="organization" required>
                <option value="">Выберите организацию</option>
                <option value="Делай!">Делай!</option>
                <option value="Точка Добра">Точка Добра</option>
                <option value="Экосистема">Экосистема</option>
                <option value="Дронт">Дронт</option>
            </select>
            <div class="photo-upload-block">
                <button type="button" class="upload-btn" onclick="triggerPhotoUpload()">📷 Загрузить фото</button>
                <input type="file" name="photo" id="photoUpload" accept="image/*" style="display:none">
                <span id="uploadStatus"></span>
            </div>
            <input type="hidden" name="submit_form" value="1">
            <button type="submit" class="save-btn">Отправить заявку</button>
        </form>
    </div>
</div>

<!-- Кастомное диалоговое окно -->
<div id="successDialog" class="custom-dialog">
    <div class="custom-dialog-content">
        <div class="dialog-header">
            <h3>✓ Заявка отправлена!</h3>
        </div>
        <div class="dialog-body">
            <div class="checkmark">✓</div>
            <p>Ваша заявка успешно отправлена!<br><br>Она будет рассмотрена в ближайшее время.<br>С вами свяжутся по указанному телефону или email.</p>
            <button class="dialog-close-btn" onclick="closeDialog()">Хорошо</button>
        </div>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById('volunteerModal').style.display = 'block';
    }
    
    function closeModal() {
        document.getElementById('volunteerModal').style.display = 'none';
        document.getElementById('volunteerForm').reset();
        document.getElementById('uploadStatus').innerText = '';
    }
    
    function triggerPhotoUpload() {
        document.getElementById('photoUpload').click();
    }
    
    document.getElementById('photoUpload').addEventListener('change', function(e) {
        if(e.target.files.length > 0) {
            document.getElementById('uploadStatus').innerHTML = '✓ ' + e.target.files[0].name;
        } else {
            document.getElementById('uploadStatus').innerHTML = '';
        }
    });
    
    function showDialog() {
        document.getElementById('successDialog').style.display = 'block';
    }
    
    function closeDialog() {
        document.getElementById('successDialog').style.display = 'none';
        closeModal();
    }
    
    document.getElementById('volunteerForm').addEventListener('submit', function(e) {
        <?php if (!$formSubmitted): ?>
        e.preventDefault();
        
        var formData = new FormData(this);
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data.includes('успешно')) {
                showDialog();
            } else {
                alert('Произошла ошибка. Пожалуйста, проверьте правильность заполнения формы.');
            }
        })
        .catch(error => {
            alert('Ошибка отправки. Пожалуйста, попробуйте еще раз.');
        });
        <?php else: ?>
        e.preventDefault();
        showDialog();
        <?php endif; ?>
    });
    
    window.onclick = function(event) {
        if(event.target == document.getElementById('volunteerModal')) {
            closeModal();
        }
        if(event.target == document.getElementById('successDialog')) {
            closeDialog();
        }
    }
    
    <?php if ($formSubmitted && $messageType == 'success'): ?>
    window.onload = function() {
        showDialog();
    }
    <?php endif; ?>
</script>
<script src="js/lk.js"></script>
</body>
</body>
</html>