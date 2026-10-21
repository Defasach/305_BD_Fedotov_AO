#!/bin/bash

sqlite3 movies_rating.db < db_init.sql

echo "1. Найти все пары пользователей, оценивших один и тот же фильм. Устранить дубликаты, проверить отсутствие пар с самим собой. Для каждой пары должны быть указаны имена пользователей и название фильма, который они оценивали. В списке оставить первые 100 записей."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "SELECT DISTINCT u1.name as user1, u2.name as user2, m.title FROM ratings r1 JOIN ratings r2 ON r1.movie_id = r2.movie_id AND r1.user_id < r2.user_id JOIN users u1 ON r1.user_id = u1.id JOIN users u2 ON r2.user_id = u2.id JOIN movies m ON r1.movie_id = m.id LIMIT 100;"
echo " "

echo "2. Найти 10 самых свежих оценок от разных пользователей, вывести названия фильмов, имена пользователей, оценку, дату отзыва в формате ГГГГ-ММ-ДД."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "SELECT m.title, u.name, r.rating, date(r.timestamp, 'unixepoch') as review_date FROM ratings r JOIN users u ON r.user_id = u.id JOIN movies m ON r.movie_id = m.id ORDER BY r.timestamp DESC LIMIT 10;"
echo " "

echo "3. Вывести в одном списке все фильмы с максимальным средним рейтингом и все фильмы с минимальным средним рейтингом. Общий список отсортировать по году выпуска и названию фильма. В зависимости от рейтинга в колонке 'Рекомендуем' для фильмов должно быть написано 'Да' или 'Нет'."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "WITH avg_ratings AS (SELECT m.id, m.title, m.year, AVG(r.rating) as avg_rating FROM movies m JOIN ratings r ON m.id = r.movie_id GROUP BY m.id), max_min_ratings AS (SELECT MAX(avg_rating) as max_rating, MIN(avg_rating) as min_rating FROM avg_ratings) SELECT ar.title, ar.year, ar.avg_rating, CASE WHEN ar.avg_rating = mmr.max_rating THEN 'Да' ELSE 'Нет' END as Рекомендуем FROM avg_ratings ar, max_min_ratings mmr WHERE ar.avg_rating = mmr.max_rating OR ar.avg_rating = mmr.min_rating ORDER BY ar.year, ar.title;"
echo " "

echo "4. Вычислить количество оценок и среднюю оценку, которую дали фильмам пользователи-женщины в период с 2010 по 2012 год."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "SELECT COUNT(r.id) as count_ratings, AVG(r.rating) as avg_rating FROM ratings r JOIN users u ON r.user_id = u.id WHERE u.gender = 'female' AND date(r.timestamp, 'unixepoch') BETWEEN '2010-01-01' AND '2012-12-31';"
echo " "

echo "5. Составить список фильмов с указанием их средней оценки и места в рейтинге по средней оценке. Полученный список отсортировать по году выпуска и названиям фильмов. В списке оставить первые 20 записей."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "SELECT m.title, m.year, AVG(r.rating) as avg_rating, RANK() OVER (ORDER BY AVG(r.rating) DESC) as rank FROM movies m JOIN ratings r ON m.id = r.movie_id GROUP BY m.id ORDER BY m.year, m.title LIMIT 20;"
echo " "

echo "6. Вывести список из 10 последних зарегистрированных пользователей в формате 'Фамилия Имя|Дата регистрации' (сначала фамилия, потом имя)."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "SELECT substr(name, instr(name, ' ') + 1) || ' ' || substr(name, 1, instr(name, ' ') - 1) || '|' || register_date as user_info FROM users ORDER BY register_date DESC LIMIT 10;"
echo " "

echo "7. С помощью рекурсивного CTE составить таблицу умножения для чисел от 1 до 10."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "WITH RECURSIVE multiplication(a, b) AS (SELECT 1, 1 UNION ALL SELECT CASE WHEN b = 10 THEN a + 1 ELSE a END, CASE WHEN b = 10 THEN 1 ELSE b + 1 END FROM multiplication WHERE a <= 10 AND NOT (a = 10 AND b = 10)) SELECT a || 'x' || b || '=' || (a * b) as result FROM multiplication;"
echo " "

echo "8. С помощью рекурсивного CTE выделить все жанры фильмов, имеющиеся в таблице movies (каждый жанр в отдельной строке)."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "WITH RECURSIVE split_genres(movie_id, genre, rest) AS (SELECT id, '', genres || '|' FROM movies UNION ALL SELECT movie_id, substr(rest, 1, instr(rest, '|') - 1), substr(rest, instr(rest, '|') + 1) FROM split_genres WHERE rest != '') SELECT DISTINCT genre FROM split_genres WHERE genre != '' ORDER BY genre;"
echo " "
