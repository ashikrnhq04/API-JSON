# MockerJSON API

[![Deploy to cPanel](https://github.com/ashikrnhq04/producntuserAPI/actions/workflows/deploy.yml/badge.svg)](https://github.com/ashikrnhq04/producntuserAPI/actions/workflows/deploy.yml)
[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://php.net)
[![Live API](https://img.shields.io/badge/Live%20API-mockerjson.xyz-green.svg)](https://mockerjson.xyz)

> **Free Mock Data REST API for Developers** - High-quality, realistic mock data for testing and development

MockerJSON is a free, fast, and reliable REST API providing realistic mock data for products and blog posts. Perfect for frontend development, testing, prototyping, and learning.

🌐 **Live API**: [https://mockerjson.xyz](https://mockerjson.xyz)

## ✨ Features

- 🎯 **Realistic Data** - High-quality mock products and blog posts
- ⚡ **Fast & Reliable** - Optimized responses with consistent performance
- 🔄 **RESTful Design** - Standard HTTP methods and status codes
- 📊 **Pagination Support** - Built-in pagination with metadata
- 🌐 **CORS Enabled** - Use directly from frontend applications
- 🆓 **Completely Free** - No API keys or registration required
- 🚀 **No Rate Limits** (currently) - Use as much as you need

## 🚀 Quick Start

```javascript
// Fetch products
fetch("https://mockerjson.xyz/api/v1/products?limit=5")
  .then((response) => response.json())
  .then((data) => console.log(data));

// Fetch blog posts
fetch("https://mockerjson.xyz/api/v1/posts?limit=3")
  .then((response) => response.json())
  .then((data) => console.log(data));
```

## 📡 API Endpoints

### Products

- `GET /api/v1/products` - List all products
- `GET /api/v1/products/{id}` - Get product by ID
- `GET /api/v1/products/{slug}` - Get product by URL slug

### Posts

- `GET /api/v1/posts` - List all blog posts
- `GET /api/v1/posts/{id}` - Get post by ID
- `GET /api/v1/posts/{slug}` - Get post by URL slug

### Query Parameters

- `limit` (integer, 1-100) - Number of items to return (default: 10)
- `offset` (integer) - Number of items to skip (default: 0)

## 📝 Example Responses

### Products Response

```json
{
  "status": "success",
  "message": "Products retrieved successfully",
  "pagination": {
    "total": 119,
    "limit": 10,
    "offset": 0,
    "hasMore": true
  },
  "data": [
    {
      "id": 1,
      "title": "Wireless Bluetooth Headphones",
      "description": "High-quality wireless headphones with noise cancellation",
      "price": 99.99,
      "image": "https://placehold.co/400x300/3498DB/FFFFFF",
      "url": "wireless-bluetooth-headphones"
    }
  ]
}
```

### Posts Response

```json
{
  "status": "success",
  "message": "Posts retrieved successfully",
  "pagination": {
    "total": 50,
    "limit": 10,
    "offset": 0,
    "hasMore": true
  },
  "data": [
    {
      "id": 1,
      "title": "Getting Started with React Hooks",
      "content": "React Hooks have revolutionized how we write React components...",
      "image": "https://placehold.co/800x400/E74C3C/FFFFFF",
      "url": "getting-started-with-react-hooks"
    }
  ]
}
```

## 🏗️ Project Structure

```
commercio/
├── .github/
│   └── workflows/
│       └── deploy.yml         # GitHub Actions CI/CD
├── app/
│   ├── Core/                  # Framework core classes
│   │   ├── App.php           # DI Container
│   │   ├── Database.php      # Database abstraction
│   │   ├── Router.php        # URL routing system
│   │   ├── Requests.php      # Request handling
│   │   ├── Validator.php     # Input validation
│   │   ├── SchemaManager.php # Database schema management
│   │   └── Middleware/       # Authentication middleware
│   ├── Http/
│   │   └── Controllers/      # API controllers
│   │       ├── ProductController.php
│   │       ├── PostController.php
│   │       └── index.php     # API documentation page
│   ├── Models/               # Data models
│   │   ├── Product.php
│   │   └── Post.php
│   ├── Views/
│   │   ├── JsonView.php      # JSON response formatter
│   │   └── 404.php           # Error page
│   ├── Schema/
│   │   └── DBSchema.php      # Database schema definitions
│   └── helpers/
│       └── functions.php     # Utility functions
├── bootstrap/
│   └── app.php               # Application bootstrap
├── config/
│   └── database.php          # Database configuration
├── data/                     # Mock data generators
├── public/                   # Web server document root
│   ├── index.php            # Application entry point
│   ├── favicon.ico          # Site favicon
│   └── robots.txt           # SEO configuration
├── routes/
│   └── api.php              # API route definitions
├── storage/                 # Application storage
├── tests/                   # Test suite
│   ├── Feature/             # Feature tests
│   ├── Unit/                # Unit tests
│   └── TestCase.php         # Base test class
├── vendor/                  # Composer dependencies
├── .env                     # Environment variables
├── .htaccess               # Apache configuration
├── composer.json           # Composer configuration
└── run-tests.php           # Custom test runner
```

## 🛠️ Development Setup

### Prerequisites

- PHP 8.1 or higher
- MySQL/MariaDB
- Composer
- Web server (Apache/Nginx) or PHP built-in server

### Local Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/ashikrnhq04/producntuserAPI.git
   cd producntuserAPI
   ```

2. **Install dependencies**

   ```bash
   composer install
   ```

3. **Set up environment**

   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

4. **Configure database**

   ```env
   DB_HOST=localhost
   DB_NAME=your_database_name
   DB_USER=your_username
   DB_PASS=your_password
   APP_KEY=your_app_key_here
   ```

5. **Start development server**

   ```bash
   php -S localhost:8000 -t public
   ```

6. **Access the application**
   - API Documentation: `http://localhost:8000`
   - API Base URL: `http://localhost:8000/api/v1/`

## 🧪 Testing

The project uses PestPHP for testing with a custom test runner for CI/CD compatibility.

```bash
# Run all tests
./run-tests.php

# Run with Composer
composer test

# Run specific test suite
./vendor/bin/pest tests/Feature/
./vendor/bin/pest tests/Unit/
```

### Test Coverage

- ✅ **Feature Tests** - API endpoint functionality
- ✅ **Unit Tests** - Individual component testing
- ✅ **Database Tests** - Database operations
- ✅ **Model Tests** - Data model validation

## 🚀 Deployment

The project includes automated deployment via GitHub Actions to cPanel hosting.

### GitHub Actions Workflow

1. **Checkout** - Clone repository
2. **Setup PHP** - Install PHP 8.3 and extensions
3. **Install Dependencies** - Run `composer install`
4. **Run Tests** - Execute test suite
5. **Setup Environment** - Create production `.env`
6. **Deploy** - SFTP upload to cPanel

### Required GitHub Secrets

- `HOST` - cPanel hostname
- `USERNAME` - cPanel username
- `PASSWORD` - cPanel password
- `APP_KEY` - Application encryption key

## 🏛️ Architecture

### Framework Features

- **Custom MVC Framework** - Built from scratch with modern PHP
- **Dependency Injection** - Service container for loose coupling
- **Database Abstraction** - Custom ORM-like query builder
- **Request Validation** - Built-in validation system
- **Middleware Support** - Authentication and request processing
- **Schema Management** - Automated database table creation

### Design Patterns

- Model-View-Controller (MVC)
- Dependency Injection
- Repository Pattern
- Factory Pattern
- Strategy Pattern (test runner)

## � Security

- **Input Validation** - Comprehensive request validation
- **SQL Injection Prevention** - Prepared statements
- **XSS Protection** - Output escaping
- **Environment Variables** - Secure configuration
- **Error Handling** - Safe error responses

## � Performance

- **Optimized Queries** - Efficient database operations
- **Pagination** - Memory-efficient data loading
- **Caching Headers** - Browser caching support
- **Lightweight Framework** - Minimal overhead

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🔮 Roadmap

### Completed ✅

- [x] Core API endpoints (Products & Posts)
- [x] Pagination system
- [x] Database abstraction layer
- [x] Test suite with PestPHP
- [x] GitHub Actions CI/CD
- [x] Professional API documentation
- [x] Error handling and validation

### Planned 🚧

- [ ] API rate limiting
- [ ] Authentication system (JWT/OAuth)
- [ ] Caching layer (Redis/Memcached)
- [ ] File upload handling
- [ ] API versioning
- [ ] OpenAPI/Swagger documentation
- [ ] Docker containerization
- [ ] Performance monitoring

## 📞 Support

- **Documentation**: [https://mockerjson.xyz](https://mockerjson.xyz)
- **Issues**: [GitHub Issues](https://github.com/ashikrnhq04/producntuserAPI/issues)
- **Email**: [ashikrn.hq04@gmail.com](mailto:ashikrn.hq04@gmail.com)
