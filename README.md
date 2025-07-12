# 👨‍💼 Admin Dashboard - Employee Management (Vue.js + Laravel)

A modern, scalable **Admin Dashboard** built using **Vue.js (Frontend)** and **Laravel (Backend)** for managing employees with full **CRUD**, **search**, **filter**, and **sort** functionality. Designed with **security best practices** and **future-ready API documentation** (Swagger/OpenAPI).

---

## 🚀 Features

- ✅ Full CRUD for Employees
- 🔍 Search by name or email
- 🏷️ Filter by:
  - Department
  - Salary Range
- 🔃 Sort by Joining Date
- 🔐 Security Best Practices (XSS, CSRF, Auth)
- 📊 Dashboard-ready UI (Vue.js)
- 🧾 Swagger-ready API (Laravel backend)
- 👤 Display fields:
  - Name
  - Email
  - Department
  - Designation
  - Joining Date
  - Edit & Delete actions

---

## 🧰 Tech Stack

| Frontend | Backend | Database |
|----------|---------|----------|
| Vue.js 3 | Laravel 12 | MySQL |
| Vue Router | RESTful API | Faker (Seeding) |
| Axios | UUID |

---

## 📦 Installation

### 🔽 Clone Project

```bash
git clone https://github.com/nion-roy/employee-management.git
cd employee-management
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve




## 🛢️ Database Setup

This project includes a pre-built database file for quick setup.

### 📄 File Location:
The database file `employee_management.sql` is included in the root directory of this project.

### 🧭 Import Instructions:

#### 💻 Command Line (MySQL)
```bash
mysql -u root -p employee_management < employee_management.sql
