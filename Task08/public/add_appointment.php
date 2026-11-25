<?php
require_once 'config.php';

$success = '';
$error = '';

// Получение списка услуг
$stmt = $pdo->query("SELECT * FROM services ORDER BY name");
$services = $stmt->fetchAll();

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceId = $_POST['service_id'] ?? '';
    $masterId = $_POST['master_id'] ?? '';
    $clientName = trim($_POST['client_name'] ?? '');
    $clientPhone = trim($_POST['client_phone'] ?? '');
    $appointmentDate = $_POST['appointment_date'] ?? '';
    $appointmentTime = $_POST['appointment_time'] ?? '';
    
    if (empty($serviceId)) {
        $error = 'Выберите услугу';
    } elseif (empty($masterId)) {
        $error = 'Выберите мастера';
    } elseif (empty($clientName)) {
        $error = 'Введите ваше имя';
    } elseif (empty($clientPhone)) {
        $error = 'Введите номер телефона';
    } elseif (empty($appointmentDate)) {
        $error = 'Выберите дату';
    } elseif (empty($appointmentTime)) {
        $error = 'Выберите время';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO appointments (master_id, service_id, client_name, client_phone, appointment_date, appointment_time) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$masterId, $serviceId, $clientName, $clientPhone, $appointmentDate, $appointmentTime]);
            $success = 'Запись успешно создана! Ожидайте звонка для подтверждения.';
            
            // Очистка формы
            $serviceId = $masterId = $clientName = $clientPhone = $appointmentDate = $appointmentTime = '';
        } catch (PDOException $e) {
            $error = 'Ошибка при создании записи: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Запись к мастеру - СТО</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>✍️ Записаться к мастеру</h1>
        
        <?php if ($success): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" id="appointmentForm">
            <div class="form-group">
                <label for="service_id">Выберите услугу *</label>
                <select id="service_id" name="service_id" required>
                    <option value="">Выберите услугу</option>
                    <?php foreach ($services as $service): ?>
                        <option value="<?= $service['id'] ?>" <?= (isset($serviceId) && $serviceId == $service['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($service['name']) ?> (<?= $service['duration'] ?> мин.)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="master_id">Выберите мастера *</label>
                <select id="master_id" name="master_id" required disabled>
                    <option value="">Сначала выберите услугу</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="appointment_date">Дата *</label>
                <input type="date" id="appointment_date" name="appointment_date" 
                       value="<?= htmlspecialchars($appointmentDate ?? '') ?>" 
                       min="<?= date('Y-m-d') ?>" required disabled>
            </div>
            
            <div class="form-group">
                <label for="appointment_time">Время *</label>
                <select id="appointment_time" name="appointment_time" required disabled>
                    <option value="">Выберите дату</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="client_name">Ваше имя *</label>
                <input type="text" id="client_name" name="client_name" 
                       value="<?= htmlspecialchars($clientName ?? '') ?>" 
                       placeholder="Иванов Иван Иванович" required>
            </div>
            
            <div class="form-group">
                <label for="client_phone">Телефон *</label>
                <input type="tel" id="client_phone" name="client_phone" 
                       value="<?= htmlspecialchars($clientPhone ?? '') ?>" 
                       placeholder="+7 (900) 123-45-67" required>
            </div>
            
            <button type="submit" class="btn">Записаться</button>
        </form>
        
        <a href="index.php" class="back-link">← Вернуться на главную</a>
    </div>

    
    <script>
        const serviceSelect = document.getElementById('service_id');
        const masterSelect = document.getElementById('master_id');
        const dateInput = document.getElementById('appointment_date');
        const timeSelect = document.getElementById('appointment_time');
        
        // При выборе услуги загружаем мастеров
        serviceSelect.addEventListener('change', function() {
            const serviceId = this.value;
            masterSelect.innerHTML = '<option value="">Загрузка...</option>';
            masterSelect.disabled = true;
            dateInput.disabled = true;
            timeSelect.disabled = true;
            timeSelect.innerHTML = '<option value="">Выберите мастера и дату</option>';
            
            if (serviceId) {
                fetch('get_masters.php?service_id=' + serviceId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            masterSelect.innerHTML = '<option value="">Выберите мастера</option>';
                            data.forEach(master => {
                                const option = document.createElement('option');
                                option.value = master.id;
                                option.textContent = master.name;
                                masterSelect.appendChild(option);
                            });
                            masterSelect.disabled = false;
                        } else {
                            masterSelect.innerHTML = '<option value="">Нет доступных мастеров</option>';
                        }
                    });
            } else {
                masterSelect.innerHTML = '<option value="">Сначала выберите услугу</option>';
            }
        });
        
        // При выборе мастера разблокируем выбор даты
        masterSelect.addEventListener('change', function() {
            if (this.value) {
                dateInput.disabled = false;
            } else {
                dateInput.disabled = true;
                timeSelect.disabled = true;
            }
        });
        
        // При выборе даты загружаем доступное время
        dateInput.addEventListener('change', function() {
            const masterId = masterSelect.value;
            const date = this.value;
            
            if (masterId && date) {
                timeSelect.innerHTML = '<option value="">Загрузка...</option>';
                timeSelect.disabled = true;
                
                fetch(`get_available_times.php?master_id=${masterId}&date=${date}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            timeSelect.innerHTML = '<option value="">Выберите время</option>';
                            data.forEach(time => {
                                const option = document.createElement('option');
                                option.value = time;
                                option.textContent = time;
                                timeSelect.appendChild(option);
                            });
                            timeSelect.disabled = false;
                        } else {
                            timeSelect.innerHTML = '<option value="">Нет доступного времени</option>';
                        }
                    });
            }
        });
    </script>
</body>
</html>

