<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>СТО - Система управления записями</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 40px;
        }
        .menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .menu-item {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        .menu-item h2 {
            font-size: 20px;
            margin-bottom: 10px;
        }
        .menu-item p {
            font-size: 14px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚗 Система управления СТО</h1>
        <p class="subtitle">Автосервис - управление мастерами, графиком и записями</p>
        
        <div class="menu">
            <a href="add_master.php" class="menu-item">
                <h2>👨‍🔧 Добавить мастера</h2>
                <p>Регистрация нового мастера и привязка услуг</p>
            </a>
            
            <a href="add_schedule.php" class="menu-item">
                <h2>📅 График работы</h2>
                <p>Управление расписанием работы мастеров</p>
            </a>
            
            <a href="add_appointment.php" class="menu-item">
                <h2>✍️ Записаться</h2>
                <p>Предварительная запись к мастеру</p>
            </a>
        </div>
    </div>
</body>
</html>
