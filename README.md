# 📝 Task Manager - Symfony App

A modern task management web application built with **Symfony**, **PostgreSQL**, and **Bootstrap 5**, with a secure authentication system and admin dashboard.

## Features

- User registration and login (with "remember me")
- Admin dashboard to:
  - Block / Unblock users
  - Delete multiple users
  - View last login (with relative timestamps)
- Task list with:
  - Selection via checkboxes
  - Priorities, due dates, and categories
- Clean UI using Bootstrap 5
- CSRF protection and secure sessions
- Deployed on [Render](https://render.com)

## Tech Stack

- PHP 8.4
- Symfony 7.2
- PostgreSQL
- Doctrine ORM
- Bootstrap 5
- JavaScript (vanilla)

## 🛠Setup Instructions

```bash
# Clone the repo
git clone https://github.com/TheMordo26/task_4_GEPP.git
cd task_4_GEPP

# Install dependencies
composer install
npm install && npm run build # (if using Encore)

# Configure environment
cp .env .env.local
# Edit DB URL and APP_SECRET in .env.local

# Create database
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Run server
symfony server:start
