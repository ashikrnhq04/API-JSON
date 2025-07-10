# PHP REST API Framework

A lightweight, custom-built PHP framework for creating REST APIs with support for posts and products.

## 🚀 Features

- **Custom PHP Framework** - Built from scratch with modern PHP practices
- **REST API Endpoints** - Full CRUD operations for posts, products, and users
- **Category Management** - Flexible categorization system with many-to-many relationships
- **Database Abstraction** - Custom Database class with query builder and transaction support
- **Request Validation** - Built-in validation system with customizable rules
- **Schema Management** - Automated database table creation and management
- **Routing System** - Clean URL routing with parameter support
- **Container/DI** - Dependency injection container for service management

## 📁 Project Structure

```
commercio/
├── public/                 # Web server document root
│   ├── index.php          # Application entry point
│   └── robots.txt         # SEO robots configuration
├── src/                   # Application source code
│   ├── Core/              # Framework core classes
│   │   ├── App.php        # Application container
│   │   ├── Database.php   # Database abstraction layer
│   │   ├── Router.php     # URL routing system
│   │   ├── Requests.php   # Request handling and validation
│   │   └── Validator.php  # Input validation
│   ├── controller/        # API controllers
│   │   ├── posts/         # Post management endpoints
│   │   └── products/      # Product management endpoints
│   ├── views/             # Response templates
│   ├── helpers/           # Utility functions
│   └── schema/            # Database schema definitions
├── vendor/                # Composer dependencies
├── bootstrap.php          # Application bootstrap
├── config.php             # Configuration settings
├── routes.php             # API route definitions
└── composer.json          # Composer configuration
```

## 🛠️ Installation

### Prerequisites

- PHP 8.0 or higher
- MySQL/MariaDB
- Composer
- Web server (Apache/Nginx) or PHP built-in server

### Setup

1. **Clone the repository**

   ```bash
   git clone <repository-url>
   cd commercio
   ```

2. **Install dependencies**

   ```bash
   composer install
   ```

3. **Configure database**

   - Update `config.php` with your database credentials

   ```php
   return [
       "database" => [
           "dbname" => "your_database_name",
           "host" => "localhost",
           "port" => "3306",
           "charset" => "utf8mb4"
       ]
   ];
   ```

4. **Start the development server**

   ```bash
   php -S localhost:8000 -t public
   ```

5. **Access the application**
   - API Base URL: `http://localhost:8000/api/v1/`

## 🔗 API Endpoints

### Posts

- `GET /api/v1/posts` - List all posts
- `GET /api/v1/posts/{id}` - Get single post
- `POST /api/v1/posts` - Create new post
- `PATCH /api/v1/posts/{id}` - Update post
- `DELETE /api/v1/posts/{id}` - Delete post

### Products

- `GET /api/v1/products` - List all products
- `GET /api/v1/products/{id}` - Get single product
- `POST /api/v1/products` - Create new product
- `PATCH /api/v1/products/{id}` - Update product
- `DELETE /api/v1/products/{id}` - Delete product

## 📝 API Usage Examples

### Create a Post

```bash
curl -X POST http://localhost:8000/api/v1/posts \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "title=My First Post&content=This is the content&categories=technology,programming&image=https://placehold.co/1400x800/FF6B6B/FFFFFF"
```

### Create a Product

```bash
curl -X POST http://localhost:8000/api/v1/products \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Wireless Headphones",
    "description": "High-quality wireless headphones",
    "price": 99.99,
    "categories": "electronics,gadgets",
    "image": "https://placehold.co/1400x800/4ECDC4/FFFFFF"
  }'
```

### Response Format

```json
{
  "version": "1.0.0",
  "status": "success",
  "ok": true,
  "data": [
    {
      "id": 1,
      "title": "Sample Post",
      "content": "Post content...",
      "categories": ["technology", "programming"],
      "image": "https://placehold.co/1400x800/FF6B6B/FFFFFF",
      "url": "sample-post",
      "created_at": "2025-01-01 12:00:00"
    }
  ]
}
```

## 🏗️ Database Schema

### Tables

- **posts** - Blog posts/articles
- **products** - E-commerce products
- **categories** - Category definitions
- **post_category** - Post-category relationships
- **product_category** - Product-category relationships

### Key Features

- **Automatic table creation** - Tables are created automatically based on schema definitions
- **Foreign key constraints** - Proper referential integrity
- **Timestamps** - Automatic created_at and updated_at fields
- **URL slugs** - SEO-friendly URLs for all content

## 🔧 Development Tools

### Bulk Data Generation

The project includes scripts for generating test data:

### Database Management

- **Schema Manager** - Handles table creation and updates
- **Migration System** - Version-controlled database changes
- **Transaction Support** - ACID compliance for data operations

## 🛡️ Security Features

- **Input Validation** - Comprehensive request validation
- **SQL Injection Prevention** - Prepared statements throughout
- **Transaction Safety** - Automatic rollback on errors
- **Error Handling** - Proper exception management

## 📚 Core Components

### Database Class

- Query builder with method chaining
- Transaction management
- Connection pooling
- Error handling

### Router System

- RESTful routing
- Parameter extraction
- Middleware support
- Error responses

### Validation System

- Rule-based validation
- Custom validators
- Error message handling
- Sanitization

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 🔮 Future Enhancements

- [ ] Authentication system (JWT/OAuth)
- [ ] File upload handling
- [ ] Caching layer (Redis/Memcached)
- [ ] API rate limiting
- [ ] Documentation generator
- [ ] Unit test suite
- [ ] Docker containerization
- [ ] CI/CD pipeline

## 📞 Support

For questions, issues, or contributions, please open an issue on GitHub or contact the development team.

---

**Built with ❤️ using modern PHP practices and clean architecture principles.**
