<?php
session_start();

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'error' => 'Неверный метод запроса']));
}

$report_id = $_POST['report_id'] ?? null;
$comment = trim($_POST['comment'] ?? '');
$user_id = $_SESSION['user_id'] ?? null;

if (!$report_id) {
    die(json_encode(['success' => false, 'error' => 'Не указан ID проблемы']));
}

if (!isset($_FILES['proof_photo']) || $_FILES['proof_photo']['error'] !== UPLOAD_ERR_OK) {
    die(json_encode(['success' => false, 'error' => 'Ошибка загрузки фото подтверждения']));
}

$uploadDir = 'uploads/proofs/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$fileExt = pathinfo($_FILES['proof_photo']['name'], PATHINFO_EXTENSION);
$fileName = 'proof_' . $report_id . '_' . time() . '.' . $fileExt;
$filePath = $uploadDir . $fileName;

$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($_FILES['proof_photo']['type'], $allowedTypes)) {
    die(json_encode(['success' => false, 'error' => 'Можно загружать только JPG, PNG, GIF, WEBP']));
}

if ($_FILES['proof_photo']['size'] > 10 * 1024 * 1024) {
    die(json_encode(['success' => false, 'error' => 'Файл не должен превышать 10MB']));
}

if (!move_uploaded_file($_FILES['proof_photo']['tmp_name'], $filePath)) {
    die(json_encode(['success' => false, 'error' => 'Ошибка сохранения фото подтверждения']));
}

try {
    $stmt = $pdo->prepare("
    UPDATE sos_reports 
    SET status = 'resolved', 
        proof_photo = ?, 
        resolved_comment = ?, 
        resolved_at = NOW(),
        resolved_by = ?
    WHERE id = ?
");
    $stmt->execute([$filePath, $comment, $user_id, $report_id]);
    
    echo json_encode(['success' => true, 'message' => 'Спасибо! Проблема отмечена как решенная.']);
} catch(PDOException $e) {
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    echo json_encode(['success' => false, 'error' => 'Ошибка сохранения: ' . $e->getMessage()]);
}
?>