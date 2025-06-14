# 🏫 School System Management


![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white) ![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white) ![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white) ![Vite](https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white)


## 📝 Description

School System Management is a modern school management system built with Laravel 11 and TailwindCSS. This project provides a robust backend solution for educational institutions, featuring secure authentication, API endpoints, and a modern frontend interface.

## ✨ Features

- 🔐 JWT Authentication
- 🚀 Laravel 11 Framework
- 🎨 TailwindCSS for styling
- ⚡ Vite for asset bundling
- 📱 Responsive design
- 🔄 Queue system for background jobs
- 🧪 Testing with Pest PHP

## 🛠️ Tech Stack

- **Backend:**
  - PHP 8.2+
  - Laravel 11
  - JWT Authentication
  - Laravel Sanctum

- **Frontend:**
  - TailwindCSS
  - Vite
  - Axios

- **Development Tools:**
  - Laravel Sail
  - Laravel Pint
  - Pest PHP
  - Laravel Pail

## 🚀 Getting Started

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL/PostgreSQL

### Installation

1. Clone the repository
```bash
git clone https://github.com/Yessine-ELEUCHI/School-System-Management.git
cd School-System-Management
```

2. Install PHP dependencies
```bash
composer install
```

3. Install NPM dependencies
```bash
npm install
```

4. Set up environment variables
```bash
cp .env.example .env
php artisan key:generate
```

5. Configure your database in `.env` file

6. Run migrations
```bash
php artisan migrate
```

7. Start the development server
```bash
npm run dev
```

## 🧪 Testing

Run the test suite using Pest PHP:

```bash
php artisan test
```

## 📦 Development

For development, you can use the following command which runs all necessary services:

```bash
composer dev
```

This will start:
- Laravel development server
- Queue worker
- Vite development server

## 👨‍💻 Author

**Yessine ELEUCHI**
- GitHub: [@Yessine-ELEUCHI](https://github.com/Yessine-ELEUCHI)

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🤝 Contributing

Contributions, issues, and feature requests are welcome! Feel free to check the [issues page](https://github.com/Yessine-ELEUCHI/School-System-Management/issues).

## ⭐ Show your support

Give a ⭐️ if this project helped you!
