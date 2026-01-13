# Udemy Clone

A full-featured Learning Management System (LMS) inspired by Udemy, built with **Laravel 11** (Backend) and **Vue 3** (Frontend).

## 🚀 Key Features

### for Students
- **Course Discovery**: Advanced search, filtering (by category, level, price), and sorting.
- **Learning Player**: Interactive video player with progress tracking, autoplay, and speed controls.
- **Student Tools**: Real-time note-taking linked to video timestamps, lecture resources download.
- **Engagement**: Course reviews, Q&A sections, and quizzes with immediate feedback.
- **Certification**: Automated PDF certificate generation upon course completion.
- **Payments**: Secure checkout process integrated with Stripe.

### for Instructors
- **Course Studio**: Comprehensive course creation wizard.
- **Curriculum Management**: Drag-and-drop section/lecture reordering.
- **Rich Media**: Support for video uploads (chunked uploading), thumbnails, and downloadable resources.
- **Analytics**: Dashboard for tracking earnings, enrollments, and student progress.

### for Admins
- **Content Moderation**: Review and approve submitted courses.
- **User Management**: Role-based access control (Student, Instructor, Admin).
- **Configuration**: Manage categories, levels, and system settings.

## 🛠️ Tech Stack

### Backend
- **Framework**: Laravel 11
- **Authentication**: Laravel Sanctum (SPA Authentication)
- **Database**: MySQL / SQLite
- **Architecture**: Service-Repository Pattern (partial), REST API
- **Testing**: PHPUnit (Feature & Unit tests)

### Frontend
- **Framework**: Vue 3 (Composition API)
- **State Management**: Pinia
- **Styling**: Tailwind CSS v4
- **Video Player**: Video.js
- **Routing**: Vue Router

## ⚡ Installation

### Prerequisites
- PHP >= 8.2
- Composer
- Node.js & NPM

### Setup Steps

1.  **Clone the repository**
    ```bash
    git clone https://github.com/yourusername/udemy-clone.git
    cd udemy-clone
    ```

2.  **Install Backend Dependencies**
    ```bash
    composer install
    ```

3.  **Install Frontend Dependencies**
    ```bash
    npm install
    ```

4.  **Environment Configuration**
    Copy the example environment file and configure your database and third-party keys.
    ```bash
    cp .env.example .env
    ```
    Update the `.env` file with your credentials:
    - Database settings (`DB_HOST`, `DB_DATABASE`, etc.)
    - Stripe Keys (`STRIPE_KEY`, `STRIPE_SECRET`)
    - App URL

5.  **Generate Application Key**
    ```bash
    php artisan key:generate
    ```

6.  **Run Migrations & Seeders**
    To set up the database structure and populate it with sample data:
    ```bash
    php artisan migrate --seed
    ```

7.  **Link Storage**
    ```bash
    php artisan storage:link
    ```

## 🏃 Running the Application

1.  **Start the Backend Server**
    ```bash
    php artisan serve
    ```

2.  **Start the Frontend Development Server**
    ```bash
    npm run dev
    ```

Access the application at `http://localhost:8000`.

## 🧪 Testing

Run the automated test suite to ensure system stability.

```bash
php artisan test
```

## 🔒 Security

This project implements:
- **Mass Assignment Protection**: Strict `$fillable` properties on models.
- **XSS Protection**: Sanitization of input fields (especially rich text).
- **Authentication**: Secure token-based auth via Sanctum.

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
