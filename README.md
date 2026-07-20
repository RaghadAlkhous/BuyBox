<h1 align="center">
BuyBox
</h1>
<p align="center">
A modern multi-vendor e-commerce platform built with Laravel.
</p>

 
<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-red?style=for-the-badge&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2-blue?style=for-the-badge&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-orange?style=for-the-badge&logo=mysql" alt="MySQL">
  <img src="https://img.shields.io/badge/Firebase-yellow?style=for-the-badge&logo=firebase" alt="Firebase">
</p>
---

# About

**BuyBox** is a full-stack multi-vendor e-commerce platform developed with **Laravel**.

The platform connects customers, stores, delivery drivers, and administrators in a single system, allowing users to browse products, manage orders, track deliveries, and receive real-time notifications.

The project follows Laravel best practices including MVC architecture, RESTful APIs, Eloquent ORM, Middleware, Request Validation, and scalable project organization.

---

# Features

### Authentication

- Secure Login & Registration
- Password Reset
- Role-Based Authentication
- Protected Routes

### Customer

- Browse Products
- Shopping Cart
- Favorites
- Order Placement
- Order Tracking
- Notifications

### Store

- Product Management
- Inventory Management
- Order Management
- Store Dashboard

### Driver

- Assigned Orders
- Delivery Updates
- Delivery Tracking

### Administrator

- User Management
- Store Management
- Product Management
- Order Monitoring
- Dashboard

### Additional Features

- Firebase Notifications
- Product Images
- Product Colors & Sizes
- Form Request Validation
- Responsive UI

---

# Tech Stack

| Category | Technologies |
|----------|--------------|
| Backend | Laravel, PHP |
| Database | MySQL |
| ORM | Eloquent |
| Authentication | Laravel Sanctum |
| Notifications | Firebase Cloud Messaging |
| Version Control | Git |

---

# Architecture

```
Client
   │
   ▼
Laravel Routes
   │
Middleware
   │
Controllers
   │
Models
   │
MySQL
   │
Firebase Notifications
```

---

# Project Structure

```
app/
├── Http/
├── Models/
├── Services/

config/

database/
├── migrations/
├── seeders/
└── factories/

resources/
├── css/
├── js/
└── views/

routes/

public/
```

---

# Installation

Clone the repository

```bash
git clone https://github.com/your-username/BuyBox.git
```

Install dependencies

```bash
composer install

npm install
```

Configure the application

```bash
cp .env.example .env

php artisan key:generate
```

Configure your database in `.env`

```env
DB_DATABASE=buybox
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations

```bash
php artisan migrate
```

(Optional)

```bash
php artisan db:seed
```

Build assets

```bash
npm run dev
```

Run the server

```bash
php artisan serve
```
