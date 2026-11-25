#!/usr/bin/env php
<?php
/**
 * Console application for displaying services provided by car service masters
 */

// Database connection
function connectDatabase(): PDO {
    $dbPath = __DIR__ . '/sto.db';
    
    if (!file_exists($dbPath)) {
        die("Error: Database file not found at {$dbPath}\n");
    }
    
    try {
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection error: " . $e->getMessage() . "\n");
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

// Draw horizontal line for table
function drawLine(array $widths, string $left, string $middle, string $right): void {
    echo $left;
    $first = true;
    foreach ($widths as $width) {
        if (!$first) {
            echo $middle;
        }
        echo str_repeat('─', $width);
        $first = false;
    }
    echo $right . "\n";
}

// Draw table row
function drawRow(array $values, array $widths): void {
    echo '│';
    for ($i = 0; $i < count($values); $i++) {
        $value = mb_substr($values[$i], 0, $widths[$i]);
        $padding = $widths[$i] - mb_strlen($value);
        echo ' ' . $value . str_repeat(' ', $padding) . ' │';
    }
    echo "\n";
}

// Display services table
function displayServicesTable(array $services): void {
    if (empty($services)) {
        echo "\nНет данных для отображения.\n";
        return;
    }
    
    // Define column headers
    $headers = ['№ мастера', 'ФИО мастера', 'Дата работы', 'Услуга', 'Стоимость'];
    
    // Calculate column widths
    $widths = [
        max(mb_strlen($headers[0]), 10),  // master_id
        max(mb_strlen($headers[1]), 30),  // master_name
        max(mb_strlen($headers[2]), 12),  // work_date
        max(mb_strlen($headers[3]), 30),  // service_name
        max(mb_strlen($headers[4]), 10)   // service_price
    ];
    
    // Update widths based on actual data
    foreach ($services as $service) {
        $widths[0] = max($widths[0], mb_strlen((string)$service['master_id']));
        $widths[1] = max($widths[1], mb_strlen($service['master_name']));
        $widths[2] = max($widths[2], mb_strlen($service['work_date']));
        $widths[3] = max($widths[3], mb_strlen($service['service_name']));
        $widths[4] = max($widths[4], mb_strlen(number_format($service['service_price'], 2)));
    }
    
    echo "\n";
    
    // Draw top border
    drawLine($widths, '┌', '┬', '┐');
    
    // Draw header
    drawRow($headers, $widths);
    
    // Draw header separator
    drawLine($widths, '├', '┼', '┤');
    
    // Draw data rows
    foreach ($services as $service) {
        $row = [
            (string)$service['master_id'],
            $service['master_name'],
            $service['work_date'],
            $service['service_name'],
            number_format($service['service_price'], 2)
        ];
        drawRow($row, $widths);
    }
    
    // Draw bottom border
    drawLine($widths, '└', '┴', '┘');
    
    echo "\nВсего записей: " . count($services) . "\n";
}

// Main program
function main(): void {
    echo "=== Система учёта услуг СТО ===\n\n";
    
    // Connect to database
    $pdo = connectDatabase();
    
    // Get all masters
    $masters = getAllMasters($pdo);
    
    if (empty($masters)) {
        die("Ошибка: В базе данных нет мастеров.\n");
    }
    
    // Display masters list
    echo "Доступные мастера:\n";
    foreach ($masters as $master) {
        echo "  {$master['id']}. {$master['full_name']}\n";
    }
    
    // Get user input
    echo "\nВведите номер мастера для фильтрации (или нажмите Enter для всех): ";
    $input = trim(fgets(STDIN));
    
    $masterId = null;
    
    // Validate input
    if ($input !== '') {
        if (!ctype_digit($input)) {
            die("Ошибка: Введите целое число или оставьте поле пустым.\n");
        }
        
        $masterId = (int)$input;
        
        // Check if master exists
        $masterExists = false;
        foreach ($masters as $master) {
            if ($master['id'] === $masterId) {
                $masterExists = true;
                break;
            }
        }
        
        if (!$masterExists) {
            die("Ошибка: Мастер с номером {$masterId} не найден в базе данных.\n");
        }
    }
    
    // Get and display services
    $services = getCompletedServices($pdo, $masterId);
    displayServicesTable($services);
}

// Run the program
main();
