
## 🚨 Limitations & Disclaimers

### What This API DOES:
- ✅ Provides RESTful endpoints for Flutter mobile app
- ✅ requirement based on process interview coding of PT.Tirtakencana Tatawarna

### What This API DOES NOT:
- ❌ **Comprehensive validation** - Limited input sanitization
- ❌ **API authentication** - No token-based auth (JWT/Passport)
- ❌ **Rate limiting** - No request throttling
- ❌ **Advanced error handling** - Basic exception handling only
- ❌ **Data encryption** - No sensitive data encryption
- ❌ **API documentation** - No Swagger/OpenAPI docs
- ❌ **Unit tests** - Limited test coverage
- ❌ **Security headers** - No advanced security measures

### Known Technical Limitations:
- **PHP 7.4**: Using older PHP version (EOL: Nov 2022)
- **Laravel 8.75**: Not the latest LTS version
- **Database**: Basic migrations without advanced constraints
- **Validation**: Minimal request validation
- **Error Responses**: Generic error messages
- **CORS**: Basic CORS setup without domain restrictions

## 🚀 Installation & Setup

``composer install``

``cp .env.example .env``

``php artisan key:generate``

### Database setup

DB_CONNECTION=mysql

DB_HOST=127.0.0.1

DB_PORT=3306

DB_DATABASE=gift_management

DB_USERNAME=your_username

DB_PASSWORD=your_password

Please look import file json ``API.postman_collection.json`` using **Postman**

### Prerequisites
- PHP 7.4.33
- Composer
- MySQL
- Laravel 8.75

