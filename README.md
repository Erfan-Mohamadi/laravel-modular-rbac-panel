# Laravel Modular RBAC Panel

A modular Laravel 10+ admin panel starter built with **Role-Based Access Control (RBAC)** in mind. Designed for scalable backend development using Laravel modules and structured permission management.

---

## ✨ Features

- Modular architecture using [nwidart/laravel-modules](https://github.com/nWidart/laravel-modules)
- Role & Permission management via `spatie/laravel-permission`
- Admin panel boilerplate (Blade-based)
- User & role CRUD system
- Clean separation of logic and resources
- Ready for expansion with new modules

---

## 📦 Tech Stack

- **Laravel 12+**
- **PHP 8.2+**
- **Blade Templating**
- **nwidart/laravel-modules**
- **spatie/laravel-permission**
- Composer, NPM, Vite

---

## 🚀 Installation

```bash
# Clone the repository
git clone https://github.com/Erfan-Mohamadi/laravel-modular-rbac-panel.git
cd laravel-modular-rbac-panel

# Install dependencies
composer install
npm install && npm run dev

# Set environment variables
cp .env.example .env
php artisan key:generate

# Configure DB in .env
# Then run migrations and seeders
php artisan migrate --seed

# Serve the app
php artisan serve
