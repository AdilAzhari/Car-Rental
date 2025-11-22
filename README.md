# Car Rental Management System

A comprehensive car rental management system built with Laravel 12 and Filament v4, featuring multi-role access, booking management, payment processing, and vehicle tracking.

## 📋 Table of Contents

- [Features](#features)
- [Technology Stack](#technology-stack)
- [System Requirements](#system-requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Architecture](#architecture)
- [User Roles](#user-roles)
- [Documentation](#documentation)
- [Testing](#testing)
- [Deployment](#deployment)
- [License](#license)

## ✨ Features

### Core Features
- **Multi-Role System**: Admin, Owner (Vehicle Provider), and Renter roles
- **Vehicle Management**: Complete CRUD operations with image galleries
- **Booking System**: Real-time availability checking with conflict prevention
- **Payment Processing**: Multiple payment methods with transaction tracking
- **Review System**: Customer ratings and feedback for vehicles
- **Activity Logging**: Comprehensive audit trail using Spatie Activity Log
- **Search & Favorites**: User preferences and recent searches
- **Notifications**: Real-time notifications for booking status changes

### Admin Panel Features (Filament v4)
- **Dashboard**: Revenue analytics, booking statistics, vehicle utilization
- **Resource Management**: Users, Vehicles, Bookings, Payments, Reviews
- **Relation Managers**: Embedded management of related records
- **Export Functionality**: Export data to Excel, CSV, and PDF
- **Advanced Filters**: Multi-field filtering and search
- **Bulk Actions**: Perform actions on multiple records
- **Custom Pages**: Profile management, login customization
- **Table Summarizers**: Real-time statistics (sum, average, count)
- **Activity Logs**: System-wide activity tracking

### API Features
- **RESTful API**: Complete REST API for mobile/web integration
- **Authentication**: Sanctum-based token authentication
- **Resource Endpoints**: Full CRUD for all entities
- **Search & Filter**: Advanced query parameters
- **Pagination**: Efficient data loading

## 🛠 Technology Stack

### Backend
- **Framework**: Laravel 12.37.0
- **PHP**: 8.4.1
- **Database**: MySQL 8.0+
- **Admin Panel**: Filament v4
- **Authentication**: Laravel Sanctum
- **Activity Log**: Spatie Laravel Activity Log

### Frontend
- **Admin UI**: Filament v4 (TALL Stack)
- **JavaScript**: Inertia.js for SPA capabilities
- **Styling**: Tailwind CSS via Filament
- **Icons**: Heroicons

### Additional Packages
- **Export**: alperenersoy/filament-export
- **Calendar**: guava/calendar
- **Testing**: Pest PHP
- **Code Quality**: Laravel Pint, Rector

## 💻 System Requirements

- PHP 8.2 or higher
- Composer 2.x
- MySQL 8.0+ or MariaDB 10.3+
- Node.js 18.x or higher (for asset compilation)
- NPM or Yarn

### PHP Extensions
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- GD or Imagick (for image processing)

## 📦 Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd car-rental
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE car_rental CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Configure .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=car_rental
DB_USERNAME=root
DB_PASSWORD=your_password

# Run migrations
php artisan migrate --seed
```

### 5. Storage Setup
```bash
php artisan storage:link
```

### 6. Build Assets
```bash
npm run build
```

### 7. Start Development Server
```bash
php artisan serve
```

Visit `http://localhost:8000/admin` to access the admin panel.

### Default Credentials
After seeding, you can login with:
- **Admin**: admin@example.com / password
- **Owner**: owner@example.com / password
- **Renter**: renter@example.com / password

## ⚙️ Configuration

### Application Settings
Edit `.env` file for basic configuration:

```env
APP_NAME="Car Rental System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
APP_LOCALE=en
APP_CURRENCY=MYR
```

### Mail Configuration
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS="noreply@carental.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Queue Configuration
For production, use Redis or Database queue driver:
```env
QUEUE_CONNECTION=redis
```

Then run the queue worker:
```bash
php artisan queue:work
```

### File Storage
For production, configure S3 or other cloud storage:
```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket
```

## 🏗 Architecture

### Design Patterns
- **Repository Pattern**: Data access abstraction
- **Service Layer**: Business logic separation
- **DTO Pattern**: Data transfer objects for API responses
- **Observer Pattern**: Model event handling
- **Policy Pattern**: Authorization logic

### Project Structure
```
car-rental/
├── app/
│   ├── Actions/          # Single-purpose action classes
│   ├── Commands/         # Artisan console commands
│   ├── DTOs/            # Data Transfer Objects
│   ├── Enums/           # Enumeration classes
│   ├── Events/          # Event classes
│   ├── Exceptions/      # Custom exceptions
│   ├── Filament/        # Filament admin panel
│   │   ├── Pages/       # Custom pages
│   │   ├── Resources/   # CRUD resources
│   │   └── Widgets/     # Dashboard widgets
│   ├── Helpers/         # Helper functions
│   ├── Http/            # Controllers, Middleware, Requests
│   │   ├── Controllers/
│   │   │   ├── Api/     # API controllers
│   │   │   ├── Auth/    # Authentication controllers
│   │   │   └── Web/     # Web controllers
│   │   ├── Middleware/
│   │   ├── Requests/    # Form requests
│   │   └── Resources/   # API resources
│   ├── Listeners/       # Event listeners
│   ├── Models/          # Eloquent models
│   ├── Notifications/   # Notification classes
│   ├── Observers/       # Model observers
│   ├── Policies/        # Authorization policies
│   ├── Repositories/    # Data repositories
│   └── Services/        # Business logic services
├── database/
│   ├── factories/       # Model factories
│   ├── migrations/      # Database migrations
│   └── seeders/         # Database seeders
├── docs/                # Documentation
├── public/              # Public assets
├── resources/
│   ├── lang/           # Translations (en, ar)
│   ├── views/          # Blade templates
│   └── js/             # JavaScript files
├── routes/
│   ├── api.php         # API routes
│   ├── web.php         # Web routes
│   └── console.php     # Console routes
└── tests/              # Tests
```

## 👥 User Roles

### Admin
- Full system access
- User management
- Vehicle management
- Booking management
- Payment management
- System configuration
- Activity log viewing
- Analytics and reporting

### Owner (Vehicle Provider)
- Manage own vehicles
- View bookings for own vehicles
- View vehicle reviews
- Upload vehicle images
- Track vehicle revenue
- View activity logs for own vehicles

### Renter (Customer)
- Browse available vehicles
- Create bookings
- Make payments
- Leave reviews
- View booking history
- Manage favorites
- Track recent searches

## 📚 Documentation

Comprehensive documentation is available in the `/docs` directory:

- [Architecture](./ARCHITECTURE.md) - System architecture and design patterns
- [Models](docs/MODELS.md) - Database models and relationships
- [API Documentation](./API.md) - REST API endpoints and usage
- [Enums & DTOs](docs/ENUMS_DTOS.md) - Enumeration and data transfer objects
- [Services](./SERVICES.md) - Business logic services
- [Deployment](./DEPLOYMENT.md) - Production deployment guide
- [Testing](./TESTING.md) - Testing guide

## 🧪 Testing

### Running Tests
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage
```

### Test Structure
- **Feature Tests**: Test complete features and workflows
- **Unit Tests**: Test individual classes and methods

## 🚀 Deployment

### Production Checklist
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate application key
- [ ] Configure database
- [ ] Set up queue worker
- [ ] Configure file storage (S3)
- [ ] Set up mail service
- [ ] Configure caching (Redis)
- [ ] Set up SSL certificate
- [ ] Configure backup system
- [ ] Set up monitoring

### Deployment Commands
```bash
# Optimize application
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

See [DEPLOYMENT.md](./DEPLOYMENT.md) for detailed deployment instructions.

## 🔒 Security

- **Authentication**: Laravel Sanctum for API, Session for web
- **Authorization**: Policy-based access control
- **CSRF Protection**: Enabled for all forms
- **SQL Injection**: Protected via Eloquent ORM
- **XSS Protection**: Blade template escaping
- **Password Hashing**: Bcrypt with configurable rounds
- **Rate Limiting**: API and login rate limiting

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 📧 Support

For support and questions:
- Email: support@carental.com
- Documentation: https://docs.carental.com
- Issues: GitHub Issues

## 🙏 Acknowledgments

- Laravel Framework
- Filament Admin Panel
- Spatie Packages
- All contributors

---

**Version**: 1.0.0
**Last Updated**: 2025-11-11
**Maintained by**: Development Team
