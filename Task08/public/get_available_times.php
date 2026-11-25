<?php
require_once 'config.php';

header('Content-Type: application/json');

$masterId = $_GET['master_id'] ?? '';
$date = $_GET['date'] ?? '';

if (empty($masterId) || empty($date)) {
    echo json_encode([]);
    exit;
}

// Определяем день недели (1 = понедельник, 7 = воскресенье)
$timestamp = strtotime($date);
$dayOfWeek = date('N', $timestamp);

// Получаем расписание мастера на этот день
$stmt = $pdo->prepare("
    SELECT start_time, end_time 
    FROM schedules 
    WHERE master_id = ? AND day_of_week = ?
");
$stmt->execute([$masterId, $dayOfWeek]);
$schedule = $stmt->fetch();

if (!$schedule) {
    echo json_encode([]);
    exit;
}

// Получаем уже занятые слоты на эту дату
$stmt = $pdo->prepare("
    SELECT appointment_time 
    FROM appointments 
    WHERE master_id = ? AND appointment_date = ?
");
$stmt->execute([$masterId, $date]);
$bookedTimes = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Генерируем доступные временные слоты
$availableTimes = [];
$startTime = strtotime($schedule['start_time']);
$endTime = strtotime($schedule['end_time']);
$interval = 30 * 60; // 30 минут

for ($time = $startTime; $time < $endTime; $time += $interval) {
    $timeStr = date('H:i', $time);
    if (!in_array($timeStr, $bookedTimes)) {
        $availableTimes[] = $timeStr;
    }
}

echo json_encode($availableTimes);
