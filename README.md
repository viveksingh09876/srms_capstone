# Student Record Management System (SRMS)

A secure PHP/MySQL web app for managing student records (CRUD operations).

---

## Setup with Docker

1. Clone Repository
bash
git clone https://github.com/viveksingh09876/srms_capstone.git
cd srms_capstone

2. Build & Run with Docker Compose
bash
docker-compose up -d --build

This will start:

PHP + Apache container → http://localhost:8080

MySQL container on localhost:3306

3. Database Initialization

MySQL container is auto-configured with:
Database: srms_db
User: srms_user
Password: srms_pass
Root password: rootpassword

On first startup, schema from ./sql/init.sql will be imported automatically.

4. Access Application
http://localhost:8080

5. Default Login
Username: admin
Password: admin123

## Features

Admin authentication (hashed passwords).
Add/Edit/Delete student records (name, roll, email, course, grades as JSON).
Responsive UI with Bootstrap.

## Tech Stack

Backend: PHP 8 + Apache (PDO for DB connection)
Frontend: HTML, CSS, JavaScript, Bootstrap
Database: MySQL 8.0

## Security

Prepared statements (SQL injection prevention).
Password hashing.
Input validation.