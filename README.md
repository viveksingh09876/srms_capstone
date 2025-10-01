# Student Record Management System (SRMS)

A secure PHP/MySQL web app for managing student records (CRUD operations).

## Setup
1. Install XAMPP (or PHP/MySQL).
2. Import `sql/init.sql` to MySQL (database: srms_db).
3. Update `config/database.php` with your DB credentials.
4. Start server: Access at http://localhost/srms/public/index.php.
5. Login: admin / admin123.

## Features
- Admin authentication (hashed passwords).
- Add/Edit/Delete student records (name, roll, email, course, grades as JSON).
- Responsive UI with Bootstrap.

## Tech Stack
- Backend: PHP 8+ with PDO.
- Frontend: HTML/CSS/JS/Bootstrap.
- Database: MySQL.

## Security
- Prepared statements (SQL injection prevention).
- Password hashing.
- Input validation.