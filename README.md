# KITsuluh - Learning Management System

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.0-red.svg" alt="Laravel Version">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue.svg" alt="PHP Version">
  <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License">
</p>

## About KITsuluh

KITsuluh is a comprehensive Learning Management System (LMS) designed specifically for agricultural extension services. Built with Laravel 12 and modern web technologies, it provides a robust platform for delivering educational content, managing courses, and tracking learner progress in the agricultural sector.

### Key Features

- **Multi-tenant Architecture**: Support for multiple organizations/institutions
- **Course Management**: Create and manage comprehensive learning courses
- **Video Streaming**: Integrated video content with HLS streaming
- **Progress Tracking**: Detailed analytics and progress monitoring
- **Certificate Generation**: Automated certificate creation upon course completion
- **Role-based Access Control**: Flexible user roles and permissions
- **Mobile Responsive**: Optimized for all devices

## Technology Stack

- **Backend**: Laravel 12.0 Framework
- **Frontend**: Tailwind CSS, Flowbite Components
- **Database**: PostgreSQL with Eloquent ORM
- **Video Processing**: FFmpeg integration
- **Authentication**: Laravel Sanctum (Token-based)
- **File Storage**: Laravel Filesystem with cloud support
- **Background Jobs**: Laravel Queue system
- **Multi-tenancy**: Stancl Tenancy package

## Installation

### Quick Setup (requires PostgreSQL, PHP, Composer, Node.js)

```bash
composer run-script setup
```

### Manual Setup

1. Clone the repository
```bash
git clone <repository-url>
cd kitsuluh
```

2. Install dependencies
```bash
composer install
npm install
```

3. Environment setup
```bash
cp .env.example .env
php artisan key:generate
# Edit .env with your database credentials
```

4. Database setup
```bash
php artisan storage:link
php artisan migrate
php artisan db:seed
```

5. Build assets
```bash
npm run build
```

6. Start the application
```bash
php artisan serve
```

### Docker Deployment

See [Deployment Guide](./DEPLOYMENT.md) for Docker and production setup.

## Usage

### Default Login Credentials

- **Superadmin**: superadmin@kitsuluh.test / password123
- **Instructor**: instructor@kitsuluh.test / password123
- **Student**: student1@kitsuluh.test / password123

## Documentation

- [Deployment Guide](./DEPLOYMENT.md)
- Health check: `php artisan app:health` or `GET /api/health`

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
