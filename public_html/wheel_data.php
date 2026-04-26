<?php
header('Content-Type: application/javascript');

$host = 'localhost';
$dbname = 'nngasu5804_ecodobro';
$user = 'nngasu5804_ecodobro';
$pass = 'Mu6hgKE5';

$wheelSectors = [];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
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
    
} catch(PDOException $e) {
    // Если ошибка - пустой массив
    $wheelSectors = [];
}

// Выводим JavaScript переменную
echo "const WHEEL_SECTORS_FROM_DB = " . json_encode($wheelSectors, JSON_UNESCAPED_UNICODE) . ";\n";
echo "console.log('✅ Загружено заданий из БД:', WHEEL_SECTORS_FROM_DB.length);\n";
?>