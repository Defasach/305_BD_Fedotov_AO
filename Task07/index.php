<?php
/**
 * Web application for displaying services provided by car service masters
 */

// Database connection
function connectDatabase(): PDO {
    $dbPath = __DIR__ . '/sto.db';
    
    if (!file_exists($dbPath)) {
        throw new Exception("Database file not found");
    }
    
    try {
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception("Database connection error: " . $e->getMessage());
    }
}

// Get all masters from database
function getAllMasters(PDO $pdo): array {
    $stmt = $pdo->query("SELECT id, full_name FROM Masters ORDER BY full_name");
    return $stmt->fetchAll();
}

// Get completed services by master (or all masters if masterId is null)
function getCompletedServices(PDO $pdo, ?int $masterId = null): array {
    $sql = "
        SELECT 
            m.id AS master_id,
            m.full_name AS master_name,
            DATE(aps.completed_at) AS work_date,
            s.name AS service_name,
            s.price AS service_price
        FROM AppointmentServices aps
        INNER JOIN Appointments a ON aps.appointment_id = a.id
        INNER JOIN Masters m ON a.master_id = m.id
        INNER JOIN Services s ON aps.service_id = s.id
        WHERE aps.is_completed = 1
    ";
    
    if ($masterId !== null) {
        $sql .= " AND m.id = :master_id";
    }
    
    $sql .= " ORDER BY m.full_name, aps.completed_at";
    
    $stmt = $pdo->prepare($sql);
    
    if ($masterId !== null) {
        $stmt->bindValue(':master_id', $masterId, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    return $stmt->fetchAll();
}

// Initialize variables
$error = null;
$selectedMasterId = null;
$masters = [];
$services = [];

try {
    // Connect to database
    $pdo = connectDatabase();
    
    // Get all masters
    $masters = getAllMasters($pdo);
    
    // Process form submission
    if (isset($_GET['master_id']) && $_GET['master_id'] !== '') {
        $selectedMasterId = filter_var($_GET['master_id'], FILTER_VALIDATE_INT);
        
        if ($selectedMasterId === false) {
            $error = "Некорректный номер мастера";
        } else {
            // Validate that master exists
            $masterExists = false;
            foreach ($masters as $master) {
                if ($master['id'] === $selectedMasterId) {
                    $masterExists = true;
                    break;
                }
            }
            
            if (!$masterExists) {
                $error = "Мастер с указанным номером не найден";
                $selectedMasterId = null;
            }
        }
    }
    
    // Get services
    $services = getCompletedServices($pdo, $selectedMasterId);
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Template starts here
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Услуги СТО</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 30px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
            font-size: 2em;
        }
        
        .filter-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #dee2e6;
        }
        
        .form-group {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        label {
            font-weight: 600;
            color: #495057;
        }
        
        select {
            flex: 1;
            min-width: 200px;
            padding: 10px 15px;
            border: 2px solid #ced4da;
            border-radius: 5px;
            font-size: 1em;
            background: white;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        
        select:hover {
            border-color: #667eea;
        }
        
        select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        button {
            padding: 10px 25px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s, transform 0.1s;
        }
        
        button:hover {
            background: #5568d3;
            transform: translateY(-1px);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #bee5eb;
            text-align: center;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 0.95em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
        }
        
        tbody tr {
            transition: background 0.2s;
        }
        
        tbody tr:hover {
            background: #f8f9fa;
        }
        
        tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        tbody tr:nth-child(even):hover {
            background: #e9ecef;
        }
        
        .price {
            font-weight: 600;
            color: #28a745;
            text-align: right;
        }
        
        .total {
            margin-top: 20px;
            padding: 15px;
            background: #e7f3ff;
            border-left: 4px solid #667eea;
            border-radius: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .master-id {
            text-align: center;
            color: #6c757d;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Услуги станции технического обслуживания</h1>
        
        <?php if ($error): ?>
            <div class="error">
                <strong>Ошибка:</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <div class="filter-form">
            <form method="get" action="">
                <div class="form-group">
                    <label for="master_id">Выберите мастера:</label>
                    <select name="master_id" id="master_id">
                        <option value="">Все мастера</option>
                        <?php foreach ($masters as $master): ?>
                            <option value="<?= $master['id'] ?>" 
                                <?= $selectedMasterId === $master['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($master['full_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Показать</button>
                </div>
            </form>
        </div>
        
        <?php if (empty($services)): ?>
            <div class="info">
                Нет данных для отображения. Выберите мастера или измените критерии фильтрации.
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>№ мастера</th>
                        <th>ФИО мастера</th>
                        <th>Дата работы</th>
                        <th>Услуга</th>
                        <th>Стоимость (руб.)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td class="master-id"><?= htmlspecialchars($service['master_id']) ?></td>
                            <td><?= htmlspecialchars($service['master_name']) ?></td>
                            <td><?= htmlspecialchars($service['work_date']) ?></td>
                            <td><?= htmlspecialchars($service['service_name']) ?></td>
                            <td class="price"><?= number_format($service['service_price'], 2, '.', ' ') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="total">
                Всего записей: <?= count($services) ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
