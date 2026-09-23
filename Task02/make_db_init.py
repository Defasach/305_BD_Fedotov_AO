#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""ETL утилита для генерации SQL-скрипта инициализации БД movies_rating.db"""

import csv
import re
from pathlib import Path


def escape_sql_string(s):
    """Экранирование строки для SQL"""
    if s is None:
        return "NULL"
    s = str(s).replace("'", "''")
    return f"'{s}'"


def extract_year_from_title(title):
    """Извлечение года из названия фильма"""
    match = re.search(r'\((\d{4})\)$', title)
    return match.group(1) if match else "NULL"


def read_movies_csv(filepath):
    """Чтение и парсинг movies.csv"""
    movies = []
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            for row in csv.DictReader(f):
                movies.append({
                    'id': row['movieId'],
                    'title': row['title'],
                    'year': extract_year_from_title(row['title']),
                    'genres': row['genres']
                })
    except Exception as e:
        print(f"Ошибка при чтении {filepath}: {e}")
    return movies


def read_ratings_csv(filepath):
    """Чтение и парсинг ratings.csv"""
    ratings = []
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            row_id = 1
            for row in csv.DictReader(f):
                ratings.append({
                    'id': row_id,
                    'user_id': row['userId'],
                    'movie_id': row['movieId'],
                    'rating': row['rating'],
                    'timestamp': row['timestamp']
                })
                row_id += 1
    except Exception as e:
        print(f"Ошибка при чтении {filepath}: {e}")
    return ratings


def read_tags_csv(filepath):
    """Чтение и парсинг tags.csv"""
    tags = []
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            row_id = 1
            for row in csv.DictReader(f):
                tags.append({
                    'id': row_id,
                    'user_id': row['userId'],
                    'movie_id': row['movieId'],
                    'tag': row['tag'],
                    'timestamp': row['timestamp']
                })
                row_id += 1
    except Exception as e:
        print(f"Ошибка при чтении {filepath}: {e}")
    return tags


def read_users_txt(filepath):
    """Чтение и парсинг users.txt (pipe-separated)"""
    users = []
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            for line in f:
                line = line.strip()
                if line:
                    parts = line.split('|')
                    if len(parts) >= 6:
                        users.append({
                            'id': parts[0],
                            'name': parts[1],
                            'email': parts[2],
                            'gender': parts[3],
                            'register_date': parts[4],
                            'occupation': parts[5]
                        })
    except Exception as e:
        print(f"Ошибка при чтении {filepath}: {e}")
    return users


def generate_sql_script(movies, ratings, tags, users, output_file):
    """Генерация SQL-скрипта инициализации БД"""
    sql = []
    sql.append("DROP TABLE IF EXISTS ratings;")
    sql.append("DROP TABLE IF EXISTS tags;")
    sql.append("DROP TABLE IF EXISTS movies;")
    sql.append("DROP TABLE IF EXISTS users;")
    sql.append("")
    sql.append("CREATE TABLE movies (id INTEGER PRIMARY KEY, title TEXT NOT NULL, year INTEGER, genres TEXT);")
    sql.append("CREATE TABLE ratings (id INTEGER PRIMARY KEY, user_id INTEGER NOT NULL, movie_id INTEGER NOT NULL, rating REAL NOT NULL, timestamp INTEGER NOT NULL);")
    sql.append("CREATE TABLE tags (id INTEGER PRIMARY KEY, user_id INTEGER NOT NULL, movie_id INTEGER NOT NULL, tag TEXT NOT NULL, timestamp INTEGER NOT NULL);")
    sql.append("CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT NOT NULL, email TEXT, gender TEXT, register_date TEXT, occupation TEXT);")
    sql.append("")
    
    for m in movies:
        year_val = m['year'] if m['year'] != "NULL" else "NULL"
        sql.append(f"INSERT INTO movies VALUES ({m['id']}, {escape_sql_string(m['title'])}, {year_val}, {escape_sql_string(m['genres'])});")
    sql.append("")
    
    for r in ratings:
        sql.append(f"INSERT INTO ratings VALUES ({r['id']}, {r['user_id']}, {r['movie_id']}, {r['rating']}, {r['timestamp']});")
    sql.append("")
    
    for t in tags:
        sql.append(f"INSERT INTO tags VALUES ({t['id']}, {t['user_id']}, {t['movie_id']}, {escape_sql_string(t['tag'])}, {t['timestamp']});")
    sql.append("")
    
    for u in users:
        sql.append(f"INSERT INTO users VALUES ({u['id']}, {escape_sql_string(u['name'])}, {escape_sql_string(u['email'])}, {escape_sql_string(u['gender'])}, {escape_sql_string(u['register_date'])}, {escape_sql_string(u['occupation'])});")
    
    try:
        with open(output_file, 'w', encoding='utf-8') as f:
            f.write('\n'.join(sql))
        print(f"SQL-скрипт успешно сгенерирован в {output_file}")
        return True
    except Exception as e:
        print(f"Ошибка при записи {output_file}: {e}")
        return False


def main():
    """Основная функция"""
    script_dir = Path(__file__).parent.absolute()
    movies_file = script_dir / "movies.csv"
    ratings_file = script_dir / "ratings.csv"
    tags_file = script_dir / "tags.csv"
    users_file = script_dir / "users.txt"
    output_file = script_dir / "db_init.sql"
    
    for f in [movies_file, ratings_file, tags_file, users_file]:
        if not f.exists():
            print(f"Ошибка: файл не найден {f}")
            return False
    
    print("Чтение исходных файлов...")
    movies = read_movies_csv(str(movies_file))
    print(f"  - movies.csv: {len(movies)} записей")
    ratings = read_ratings_csv(str(ratings_file))
    print(f"  - ratings.csv: {len(ratings)} записей")
    tags = read_tags_csv(str(tags_file))
    print(f"  - tags.csv: {len(tags)} записей")
    users = read_users_txt(str(users_file))
    print(f"  - users.txt: {len(users)} записей")
    print("\nГенерация SQL-скрипта...")
    return generate_sql_script(movies, ratings, tags, users, str(output_file))


if __name__ == "__main__":
    exit(0 if main() else 1)

