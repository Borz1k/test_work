-- Задание 1
SELECT * FROM fruits WHERE weight>150

-- Задание 2
SELECT books.*, authors.author_name FROM books LEFT JOIN authors ON (books.author_id=authors.id)

-- Задание 3
SELECT authors.author_name, COUNT(books.id) AS _books_count FROM books LEFT JOIN authors ON (books.author_id=authors.id) GROUP BY books.author_id