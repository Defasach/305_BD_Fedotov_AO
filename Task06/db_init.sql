-- ============================================================
-- База данных для станции технического обслуживания автомобилей
-- ============================================================

-- Таблица мастеров
CREATE TABLE Masters (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name TEXT NOT NULL,
    phone TEXT NOT NULL,
    salary_percent REAL NOT NULL CHECK(salary_percent > 0 AND salary_percent <= 100),
    hire_date DATE NOT NULL DEFAULT CURRENT_DATE,
    fire_date DATE DEFAULT NULL,
    is_active INTEGER NOT NULL DEFAULT 1 CHECK(is_active IN (0, 1))
);

-- Таблица услуг
CREATE TABLE Services (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    duration_minutes INTEGER NOT NULL CHECK(duration_minutes > 0),
    price REAL NOT NULL CHECK(price >= 0)
);

-- Таблица записей на обслуживание
CREATE TABLE Appointments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    master_id INTEGER NOT NULL,
    client_name TEXT NOT NULL,
    client_phone TEXT NOT NULL,
    car_model TEXT NOT NULL,
    car_number TEXT NOT NULL,
    appointment_datetime DATETIME NOT NULL,
    status TEXT NOT NULL DEFAULT 'planned' CHECK(status IN ('planned', 'in_progress', 'completed', 'cancelled')),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (master_id) REFERENCES Masters(id) ON DELETE RESTRICT
);

-- Таблица связи записей и услуг (многие-ко-многим)
CREATE TABLE AppointmentServices (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    appointment_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    is_completed INTEGER NOT NULL DEFAULT 0 CHECK(is_completed IN (0, 1)),
    completed_at DATETIME DEFAULT NULL,
    FOREIGN KEY (appointment_id) REFERENCES Appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES Services(id) ON DELETE RESTRICT,
    UNIQUE(appointment_id, service_id)
);

-- Индексы для оптимизации запросов
CREATE INDEX idx_masters_is_active ON Masters(is_active);
CREATE INDEX idx_appointments_master_id ON Appointments(master_id);
CREATE INDEX idx_appointments_datetime ON Appointments(appointment_datetime);
CREATE INDEX idx_appointments_status ON Appointments(status);
CREATE INDEX idx_appointment_services_appointment ON AppointmentServices(appointment_id);
CREATE INDEX idx_appointment_services_service ON AppointmentServices(service_id);

-- ============================================================
-- Тестовые данные
-- ============================================================

-- Добавление мастеров
INSERT INTO Masters (full_name, phone, salary_percent, hire_date, is_active) VALUES
    ('Иванов Иван Иванович', '+7-900-123-45-67', 25.0, '2023-01-15', 1),
    ('Петров Петр Петрович', '+7-900-234-56-78', 30.0, '2023-03-20', 1),
    ('Сидоров Сидор Сидорович', '+7-900-345-67-89', 28.0, '2022-11-10', 1),
    ('Алексеев Алексей Алексеевич', '+7-900-456-78-90', 27.0, '2022-06-01', 0);

-- Увольнение одного мастера
UPDATE Masters SET fire_date = '2024-09-30', is_active = 0 WHERE id = 4;

-- Добавление услуг
INSERT INTO Services (name, duration_minutes, price) VALUES
    ('Замена масла', 30, 1500.00),
    ('Замена тормозных колодок', 60, 3000.00),
    ('Развал-схождение', 45, 2500.00),
    ('Диагностика двигателя', 90, 2000.00),
    ('Замена свечей зажигания', 30, 1200.00),
    ('Замена воздушного фильтра', 15, 800.00),
    ('Замена салонного фильтра', 20, 600.00),
    ('Балансировка колес', 40, 1600.00),
    ('Шиномонтаж (4 колеса)', 60, 2000.00),
    ('Мойка двигателя', 45, 1800.00);

-- Добавление записей на обслуживание
INSERT INTO Appointments (master_id, client_name, client_phone, car_model, car_number, appointment_datetime, status) VALUES
    (1, 'Смирнов Андрей', '+7-910-111-22-33', 'Toyota Camry', 'А123БВ777', '2024-11-26 10:00:00', 'planned'),
    (2, 'Козлова Елена', '+7-910-222-33-44', 'Honda CR-V', 'В456ГД777', '2024-11-26 14:00:00', 'planned'),
    (1, 'Николаев Дмитрий', '+7-910-333-44-55', 'Mazda 6', 'С789ЕЖ777', '2024-11-25 09:00:00', 'completed'),
    (3, 'Федорова Ольга', '+7-910-444-55-66', 'Volkswagen Polo', 'Д012ЗИ777', '2024-11-25 15:00:00', 'completed'),
    (2, 'Морозов Сергей', '+7-910-555-66-77', 'Kia Rio', 'Е345КЛ777', '2024-11-24 11:00:00', 'completed'),
    (4, 'Васильева Мария', '+7-910-666-77-88', 'Renault Logan', 'Ж678МН777', '2024-09-15 10:00:00', 'completed'),
    (1, 'Попов Владимир', '+7-910-777-88-99', 'Skoda Octavia', 'З901ОП777', '2024-11-27 10:00:00', 'planned');

-- Добавление услуг к записям
INSERT INTO AppointmentServices (appointment_id, service_id, is_completed, completed_at) VALUES
    -- Запись 1 (запланирована)
    (1, 1, 0, NULL),
    (1, 6, 0, NULL),
    -- Запись 2 (запланирована)
    (2, 3, 0, NULL),
    -- Запись 3 (завершена)
    (3, 1, 1, '2024-11-25 09:30:00'),
    (3, 5, 1, '2024-11-25 10:00:00'),
    (3, 7, 1, '2024-11-25 10:20:00'),
    -- Запись 4 (завершена)
    (4, 2, 1, '2024-11-25 16:00:00'),
    -- Запись 5 (завершена)
    (5, 4, 1, '2024-11-24 12:30:00'),
    (5, 1, 1, '2024-11-24 12:00:00'),
    -- Запись 6 (завершена уволенным мастером)
    (6, 9, 1, '2024-09-15 11:00:00'),
    (6, 8, 1, '2024-09-15 11:40:00'),
    -- Запись 7 (запланирована)
    (7, 1, 0, NULL),
    (7, 3, 0, NULL);
