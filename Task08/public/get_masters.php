<?php
require_once 'config.php';

header('Content-Type: application/json');

$serviceId = $_GET['service_id'] ?? '';

if (empty($serviceId)) {
    echo json_encode([]);
    exit;
}

// Получение мастеров, которые могут выполнять выбранную услугу
$stmt = $pdo->prepare("
    SELECT DISTINCT m.id, m.name, m.phone
    FROM masters m
    JOIN master_services ms ON m.id = ms.master_id
    WHERE ms.service_id = ?
    ORDER BY m.name
");
$stmt->execute([$serviceId]);
$masters = $stmt->fetchAll();

echo json_encode($masters);
