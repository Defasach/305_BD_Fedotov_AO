<?php
require_once 'config.php';

$success = '';
$error = '';

// Получение списка всех услуг
$stmt = $pdo->query("SELECT * FROM services ORDER BY name");
$services = $stmt->fetchAll();

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $selectedServices = $_POST['services'] ?? [];
    
    if (empty($name)) {
        $error = 'Введите имя мастера';
    } elseif (empty($selectedServices)) {
        $error = 'Выберите хотя бы одну услугу';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Добавление мастера
            $stmt = $pdo->prepare("INSERT INTO masters (name, phone, email) VALUES (?, ?, ?)");
            $stmt->execute([$name, $phone, $email]);
            $masterId = $pdo->lastInsertId();
            
            // Привязка услуг к мастеру
            $stmt = $pdo->prepare("INSERT INTO master_services (master_id, service_id) VALUES (?, ?)");
            foreach ($selectedServices as $serviceId) {
                $stmt->execute([$masterId, $serviceId]);
            }
            
            $pdo->commit();
            $success = 'Мастер успешно добавлен!';
            
            // Очистка формы
            $name = $phone = $email = '';
            $selectedServices = [];
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Ошибка при добавлении мастера: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить мастера - СТО</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>👨‍🔧 Добавить мастера</h1>
        
        <?php if ($success): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="name">ФИО мастера *</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Телефон</label>
                <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($phone ?? '') ?>" placeholder="+7 (900) 123-45-67">
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" placeholder="master@example.com">
            </div>
            
            <div class="form-group">
                <label>Услуги, которые может выполнять мастер *</label>
                <div class="services-group">
                    <?php foreach ($services as $service): ?>
                        <div class="service-item">
                            <label>
                                <input type="checkbox" name="services[]" value="<?= $service['id'] ?>"
                                    <?= in_array($service['id'], $selectedServices ?? []) ? 'checked' : '' ?>>
                                <div>
                                    <div class="service-name"><?= htmlspecialchars($service['name']) ?></div>
                                    <?php if ($service['description']): ?>
                                        <div class="service-desc"><?= htmlspecialchars($service['description']) ?> (<?= $service['duration'] ?> мин.)</div>
                                    <?php endif; ?>
                                </div>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <button type="submit" class="btn">Добавить мастера</button>
        </form>
        
        <a href="index.php" class="back-link">← Вернуться на главную</a>
    </div>
</body>
</html>
