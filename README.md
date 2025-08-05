# Restaurant Management System

A comprehensive restaurant management system built with Laravel that includes CMS functionality and advanced analytics.

## Features

### 🏪 Restaurant Management
- Multi-restaurant support
- Menu and category management
- Staff management with role-based permissions
- Table management and reservations

### 📋 Order Management
- Real-time order processing
- Kitchen display system
- Order tracking and history
- Payment integration

### 👥 Customer Management
- Customer profiles and preferences
- Loyalty program integration
- Order history and analytics

### 📊 Analytics Dashboard
- Sales analytics and reporting
- Performance metrics
- Popular items analysis
- Revenue tracking
- Real-time dashboard updates

### 🔧 CMS Features
- Dynamic content management
- Menu item management
- Staff role management
- Restaurant settings configuration

### 🔐 Authentication & Authorization
- Multi-role authentication (Admin, Manager, Staff, Customer)
- Role-based permissions using Spatie Laravel Permission
- Secure API authentication with Laravel Sanctum

### 📱 API Integration
- RESTful API for mobile app integration
- Real-time updates using WebSockets
- Third-party service integrations

## Technology Stack

- **Backend**: Laravel 10.x
- **Authentication**: Laravel Sanctum + Spatie Permission
- **Database**: MySQL/PostgreSQL
- **Real-time**: Laravel WebSockets
- **File Management**: Intervention Image
- **Export/Import**: Maatwebsite Excel
- **PDF Generation**: DomPDF
- **Data Tables**: Yajra DataTables

## Installation

1. Clone the repository
```bash
git clone <repository-url>
cd restaurant-management-system
```

2. Install dependencies
```bash
composer install
npm install
```

3. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

4. Set up database
```bash
php artisan migrate
php artisan db:seed
```

5. Install Laravel UI for authentication scaffolding
```bash
php artisan ui bootstrap --auth
npm run dev
```

6. Start the development server
```bash
php artisan serve
```

## Configuration

### Database Configuration
Update your `.env` file with your database credentials:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=restaurant_management
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Real-time Features
Configure WebSocket settings in `.env`:
```
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=your-cluster
```

## Default Users

After running the seeders, you can login with:

**Admin User:**
- Email: admin@restaurant.com
- Password: password

**Manager User:**
- Email: manager@restaurant.com
- Password: password

**Staff User:**
- Email: staff@restaurant.com
- Password: password

## API Documentation

API endpoints are available at `/api/` with the following main routes:
- `/api/restaurants` - Restaurant management
- `/api/menus` - Menu management
- `/api/orders` - Order processing
- `/api/analytics` - Analytics data

## License

This project is licensed under the MIT License.

## Support

For support and questions, please contact the development team.
