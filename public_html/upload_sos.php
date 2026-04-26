<?php
session_start();

// Подключение к базе данных
$host = 'localhost';
$dbname = 'nngasu5804_ecodobro';
$username = 'nngasu5804_ecodobro';
$password = 'Mu6hgKE5';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode(['success' => false, 'error' => 'Ошибка подключения к БД']));
}

// ========== GET-запрос: получение всех меток ==========
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->query("
    SELECT id, latitude, longitude, address, description, photo_path, created_at,
           status, proof_photo, resolved_comment, resolved_at, resolved_by
    FROM sos_reports 
    ORDER BY created_at DESC
");
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'reports' => $reports]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Ошибка загрузки меток']);
    }
    exit;
}

// ========== POST-запрос: добавление новой метки ==========
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'error' => 'Неверный метод запроса']));
}

// Получаем данные
$latitude = $_POST['latitude'] ?? null;
$longitude = $_POST['longitude'] ?? null;
$address = $_POST['address'] ?? null;
$description = trim($_POST['description'] ?? '');
$user_id = $_SESSION['user_id'] ?? null;

// Валидация
if (!$latitude || !$longitude) {
    die(json_encode(['success' => false, 'error' => 'Не выбрано местоположение на карте']));
}

if (empty($description)) {
    die(json_encode(['success' => false, 'error' => 'Введите описание проблемы']));
}

if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    die(json_encode(['success' => false, 'error' => 'Ошибка загрузки фото']));
}

// Обработка фото
$uploadDir = 'uploads/sos/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$fileExt = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
$fileName = time() . '_' . rand(1000, 9999) . '.' . $fileExt;
$filePath = $uploadDir . $fileName;

// Проверка типа файла
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($_FILES['photo']['type'], $allowedTypes)) {
    die(json_encode(['success' => false, 'error' => 'Можно загружать только JPG, PNG, GIF, WEBP']));
}

// Проверка размера (макс 10MB)
if ($_FILES['photo']['size'] > 10 * 1024 * 1024) {
    die(json_encode(['success' => false, 'error' => 'Файл не должен превышать 10MB']));
}

// Перемещаем загруженный файл
if (!move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
    die(json_encode(['success' => false, 'error' => 'Ошибка сохранения фото']));
}

// Сохраняем в базу данных
try {
    $stmt = $pdo->prepare("
    INSERT INTO sos_reports (user_id, latitude, longitude, address, description, photo_path, status) 
    VALUES (?, ?, ?, ?, ?, ?, 'new')
");
    $stmt->execute([$user_id, $latitude, $longitude, $address, $description, $filePath]);
    
    echo json_encode(['success' => true, 'message' => 'Спасибо! Ваше сообщение отправлено.']);
} catch(PDOException $e) {
    // Если ошибка — удаляем загруженное фото
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    echo json_encode(['success' => false, 'error' => 'Ошибка сохранения в базу данных']);
}
?>