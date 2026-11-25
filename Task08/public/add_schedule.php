<?php
require_once 'config.php';

$success = '';
$error = '';

// Получение списка мастеров
$stmt = $pdo->query("SELECT * FROM masters ORDER BY name");
$masters = $stmt->fetchAll();

// Дни недели
$daysOfWeek = [
    1 => 'Понедельник',
    2 => 'Вторник',
    3 => 'Среда',
    4 => 'Четверг',
    5 => 'Пятница',
    6 => 'Суббота',
    7 => 'Воскресенье'
];

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $masterId = $_POST['master_id'] ?? '';
    $dayOfWeek = $_POST['day_of_week'] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $endTime = $_POST['end_time'] ?? '';
    
    if (empty($masterId)) {
        $error = 'Выберите мастера';
    } elseif (empty($dayOfWeek)) {
        $error = 'Выберите день недели';
    } elseif (empty($startTime) || empty($endTime)) {
        $error = 'Укажите время начала и окончания работы';
    } elseif ($startTime >= $endTime) {
        $error = 'Время начала должно быть раньше времени окончания';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO schedules (master_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)");
            $stmt->execute([$masterId, $dayOfWeek, $startTime, $endTime]);
            $success = 'Расписание успешно добавлено!';
        } catch (PDOException $e) {
            $error = 'Ошибка при добавлении расписания: ' . $e->getMessage();
        }
    }
}

// Получение текущих расписаний
$stmt = $pdo->query("
    SELECT s.*, m.name as master_name 
    FROM schedules s 
    JOIN masters m ON s.master_id = m.id 
    ORDER BY m.name, s.day_of_week, s.start_time
");
$schedules = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>График работы - СТО</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>📅 График работы мастеров</h1>
        
        <?php if ($success): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="master_id">Мастер *</label>
                <select id="master_id" name="master_id" required>
                    <option value="">Выберите мастера</option>
                    <?php foreach ($masters as $master): ?>
                        <option value="<?= $master['id'] ?>"><?= htmlspecialchars($master['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="day_of_week">День недели *</label>
                <select id="day_of_week" name="day_of_week" required>
                    <option value="">Выберите день</option>
                    <?php foreach ($daysOfWeek as $num => $day): ?>
                        <option value="<?= $num ?>"><?= $day ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Время работы *</label>
                <div class="time-inputs">
                    <div>
                        <label for="start_time" style="font-weight: normal;">Начало</label>
                        <input type="time" id="start_time" name="start_time" required>
                    </div>
                    <div>
                        <label for="end_time" style="font-weight: normal;">Окончание</label>
                        <input type="time" id="end_time" name="end_time" required>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn">Добавить в расписание</button>
        </form>
        
        <?php if (!empty($schedules)): ?>
            <h2 style="margin-top: 40px; margin-bottom: 20px;">Текущее расписание</h2>
            <?php
            $currentMaster = '';
            foreach ($schedules as $schedule):
                if ($currentMaster !== $schedule['master_name']):
                    if ($currentMaster !== '') echo '</div>';
                    $currentMaster = $schedule['master_name'];
                    echo '<h3 style="margin-top: 20px; color: #667eea;">' . htmlspecialchars($currentMaster) . '</h3>';
                    echo '<div style="margin-left: 20px;">';
                endif;
            ?>
                <div class="schedule-item">
                    <strong><?= $daysOfWeek[$schedule['day_of_week']] ?>:</strong>
                    <?= htmlspecialchars($schedule['start_time']) ?> - <?= htmlspecialchars($schedule['end_time']) ?>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <a href="index.php" class="back-link">← Вернуться на главную</a>
    </div>
</body>
</html>
