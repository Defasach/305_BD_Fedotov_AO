-- Лабораторная работа 5: Добавление новых данных
-- Добавление 5 новых пользователей, 3 фильмов и 3 отзывов

-- Добавление 5 новых пользователей (я и 4 соседа по списку группы)
-- Дата регистрации определяется автоматически (DEFAULT date('now'))

INSERT INTO users (name, email, gender, occupation_id)
VALUES 
    ('Федотов Артем', 'fedotov.artem@example.com', 'male', (SELECT id FROM occupations WHERE name = 'student')),
    ('Иванов Иван', 'ivanov.ivan@example.com', 'male', (SELECT id FROM occupations WHERE name = 'student')),
    ('Петрова Мария', 'petrova.maria@example.com', 'female', (SELECT id FROM occupations WHERE name = 'student')),
    ('Сидоров Петр', 'sidorov.petr@example.com', 'male', (SELECT id FROM occupations WHERE name = 'programmer')),
    ('Кузнецова Анна', 'kuznetsova.anna@example.com', 'female', (SELECT id FROM occupations WHERE name = 'artist'));

-- Добавление 3 новых фильмов разных жанров
INSERT INTO movies (title, year) VALUES ('The Matrix Resurrections (2021)', 2021);
INSERT INTO movies (title, year) VALUES ('Dune (2021)', 2021);
INSERT INTO movies (title, year) VALUES ('Everything Everywhere All at Once (2022)', 2022);

-- Связь фильмов с жанрами
-- The Matrix Resurrections: Action, Sci-Fi
INSERT INTO movie_genres (movie_id, genre_id)
SELECT 
    (SELECT id FROM movies WHERE title = 'The Matrix Resurrections (2021)'),
    id 
FROM genres 
WHERE name IN ('Action', 'Sci-Fi');

-- Dune: Adventure, Sci-Fi, Drama
INSERT INTO movie_genres (movie_id, genre_id)
SELECT 
    (SELECT id FROM movies WHERE title = 'Dune (2021)'),
    id 
FROM genres 
WHERE name IN ('Adventure', 'Sci-Fi', 'Drama');

-- Everything Everywhere All at Once: Action, Adventure, Comedy, Sci-Fi
INSERT INTO movie_genres (movie_id, genre_id)
SELECT 
    (SELECT id FROM movies WHERE title = 'Everything Everywhere All at Once (2022)'),
    id 
FROM genres 
WHERE name IN ('Action', 'Adventure', 'Comedy', 'Sci-Fi');

-- Добавление 3 отзывов от Федотова Артема на добавленные фильмы
-- Timestamp = текущее время в Unix формате
INSERT INTO ratings (user_id, movie_id, rating, timestamp)
VALUES 
    (
        (SELECT id FROM users WHERE email = 'fedotov.artem@example.com'),
        (SELECT id FROM movies WHERE title = 'The Matrix Resurrections (2021)'),
        4.0,
        strftime('%s', 'now')
    ),
    (
        (SELECT id FROM users WHERE email = 'fedotov.artem@example.com'),
        (SELECT id FROM movies WHERE title = 'Dune (2021)'),
        5.0,
        strftime('%s', 'now')
    ),
    (
        (SELECT id FROM users WHERE email = 'fedotov.artem@example.com'),
        (SELECT id FROM movies WHERE title = 'Everything Everywhere All at Once (2022)'),
        5.0,
        strftime('%s', 'now')
    );

-- Проверка добавленных данных
SELECT '=== Новые пользователи ===' as info;
SELECT id, name, email, register_date FROM users WHERE email LIKE '%@example.com';

SELECT '=== Новые фильмы ===' as info;
SELECT m.id, m.title, m.year, GROUP_CONCAT(g.name, '|') as genres
FROM movies m
JOIN movie_genres mg ON m.id = mg.movie_id
JOIN genres g ON mg.genre_id = g.id
WHERE m.title IN ('The Matrix Resurrections (2021)', 'Dune (2021)', 'Everything Everywhere All at Once (2022)')
GROUP BY m.id;

SELECT '=== Новые отзывы ===' as info;
SELECT u.name, m.title, r.rating, datetime(r.timestamp, 'unixepoch') as review_date
FROM ratings r
JOIN users u ON r.user_id = u.id
JOIN movies m ON r.movie_id = m.id
WHERE u.email = 'fedotov.artem@example.com';
