# Laravel-Vue Project

This project is a full-stack application built with Laravel for the backend and Vue.js for the frontend.

## Project Structure

- **backend/**: Contains the Laravel backend application.
  - **app/**: Core application files.
  - **bootstrap/**: Files for bootstrapping the application.
  - **config/**: Configuration files.
  - **database/**: Database-related files including migrations, seeders, and factories.
  - **public/**: Publicly accessible files.
  - **resources/**: Views and other resources.
  - **routes/**: Route definitions for the application.
  - **storage/**: Storage files including logs and cached files.
  - **tests/**: Test files for the application.
  - **.env.example**: Example environment configuration file.
  - **artisan**: Command-line interface for Laravel.
  - **composer.json**: Composer configuration file.

- **frontend/**: Contains the Vue.js frontend application.
  - **public/**: Publicly accessible files including the main HTML file.
  - **src/**: Source code for the Vue.js application.
  - **.env.example**: Example environment configuration file for the frontend.
  - **package.json**: npm configuration file.
  - **vite.config.js**: Configuration file for Vite.

## Getting Started

### Backend Setup

1. Navigate to the `backend` directory.
2. Install dependencies using Composer:
   ```
   composer install
   ```
3. Copy the `.env.example` to `.env` and configure your environment variables.
4. Generate the application key:
   ```
   php artisan key:generate
   ```
5. Run migrations to set up the database:
   ```
   php artisan migrate
   ```
6. Start the Laravel server:
   ```
   php artisan serve
   ```

### Frontend Setup

1. Navigate to the `frontend` directory.
2. Install dependencies using npm:
   ```
   npm install
   ```
3. Start the development server:
   ```
   npm run dev
   ```

## License

This project is licensed under the MIT License.