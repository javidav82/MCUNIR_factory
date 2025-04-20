# MCUNIR Factory Management System

A comprehensive factory management system for handling print jobs, document validation, and factory status monitoring.

## Features

- **Query Info Data**: View and manage print jobs with detailed information
  - Search and filter jobs by ID, title, and status
  - View detailed job information in an accordion format
  - Track job and document status

- **Query Info Factory**: Monitor factory operations and performance
  - Real-time factory status monitoring
  - Performance statistics and charts
  - Bulk job status management
  - Factory maintenance scheduling

- **Validate Info Documents**: Document validation and feedback system
  - Document status tracking
  - Validation workflow management
  - Feedback submission and review

## Tech Stack

- **Backend**: Laravel 8.x
- **Frontend**: 
  - Bootstrap 5
  - jQuery
  - Chart.js
- **Database**: MySQL
- **Development Environment**: XAMPP

## Prerequisites

- PHP 7.4 or higher
- Composer
- MySQL 5.7 or higher
- XAMPP (for local development)
- Node.js and NPM (for frontend dependencies)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/MCUNIR_factory.git
cd MCUNIR_factory
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install frontend dependencies:
```bash
npm install
```

4. Create a copy of the environment file:
```bash
cp .env.example .env
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Configure your database in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mcunir_factory
DB_USERNAME=root
DB_PASSWORD=
```

7. Run migrations:
```bash
php artisan migrate
```

8. Start the development server:
```bash
php artisan serve
```

## Project Structure

```
MCUNIR_factory/
├── app/
│   ├── Models/              # Eloquent models
│   │   ├── Http/
│   │   │   ├── Controllers/     # Application controllers
│   │   │   └── Middleware/      # Custom middleware
│   │   └── Services/            # Business logic services
│   ├── config/                  # Configuration files
│   │   └── routes/                # Application routes
│   ├── database/
│   │   ├── factories/          # Model factories
│   │   ├── migrations/         # Database migrations
│   │   └── seeders/            # Database seeders
│   ├── public/                 # Publicly accessible files
│   └── resources/
│       ├── views/             # Blade templates
│       │   ├── layouts/       # Layout templates
│       │   ├── query/         # Query-related views
│       │   └── validate/      # Validation-related views
│       └── js/                # JavaScript files
└── tests/                 # Automated tests
```

## API Endpoints

### Factory Status
- `GET /api/factory/status` - Get factory status and statistics
- `GET /api/factory/performance` - Get factory performance data
- `GET /api/factory/jobs` - List factory jobs
- `POST /api/factory/jobs/update-status` - Update job status

### Document Validation
- `GET /api/documents/validation` - List documents for validation
- `GET /api/documents/{id}` - Get document details
- `POST /api/documents/{id}/validate` - Submit document validation

### Print Jobs
- `GET /api/user/print-jobs` - List user's print jobs
- `GET /api/user/print-jobs/{id}` - Get print job details

## Testing

Run the test suite:
```bash
php artisan test
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support, please open an issue in the GitHub repository or contact the development team.
