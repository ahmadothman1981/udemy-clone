<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CourseController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [App\Http\Controllers\NewPasswordController::class, 'forgotPassword']);
Route::post('/reset-password', [App\Http\Controllers\NewPasswordController::class, 'resetPassword'])->name('password.reset');

// Social Auth
Route::group(['middleware' => ['web']], function () {
    Route::get('/auth/{provider}/redirect', [App\Http\Controllers\SocialAuthController::class, 'redirect']);
    Route::get('/auth/{provider}/callback', [App\Http\Controllers\SocialAuthController::class, 'callback']);
});

Route::get('/debug-social-config', function () {
    return response()->json([
        'google_id_set' => !empty(config('services.google.client_id')),
        'google_secret_set' => !empty(config('services.google.client_secret')),
        'google_redirect_set' => !empty(config('services.google.redirect')),
        'google_redirect_value' => config('services.google.redirect'), // Safe to show redirect URL
    ]);
});

// Public Content
Route::get('/courses', [CourseController::class, 'index']); // Search, Filter, Sort
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::get('/levels', function () {
    return \App\Models\CourseLevel::all();
});


// Protected Routes
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead']);

    // Payment Methods
    Route::get('/payment-methods', [\App\Http\Controllers\PaymentMethodController::class, 'index']);
    Route::post('/payment-methods', [\App\Http\Controllers\PaymentMethodController::class, 'store']);
    Route::delete('/payment-methods/{id}', [\App\Http\Controllers\PaymentMethodController::class, 'destroy']);

    // Messages
    Route::get('/messages', [\App\Http\Controllers\MessageController::class, 'index']);
    Route::get('/messages/{id}', [\App\Http\Controllers\MessageController::class, 'show']);
    Route::post('/messages', [\App\Http\Controllers\MessageController::class, 'store']);

    // Instructor / Admin (Policies handle which roles allowed)
    Route::post('/courses', [CourseController::class, 'store']);
    Route::put('/courses/{course}', [CourseController::class, 'update']);
    Route::delete('/courses/{course}', [CourseController::class, 'destroy']);

    // Categories (Admin only via Policy)
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    // Curriculum (Instructor)
    Route::get('/courses/{course}/sections', [\App\Http\Controllers\SectionController::class, 'index']);
    Route::post('/courses/{course}/sections', [\App\Http\Controllers\SectionController::class, 'store']);
    Route::put('/courses/{course}/sections/reorder', [\App\Http\Controllers\SectionController::class, 'reorder']); // Bulk reorder
    Route::put('/courses/{course}/sections/{section}', [\App\Http\Controllers\SectionController::class, 'update']);
    Route::delete('/courses/{course}/sections/{section}', [\App\Http\Controllers\SectionController::class, 'destroy']);

    // Lectures (Instructor)
    Route::post('/courses/{course}/sections/{section}/lectures', [\App\Http\Controllers\LectureController::class, 'store']);
    Route::put('/courses/{course}/sections/{section}/lectures/{lecture}', [\App\Http\Controllers\LectureController::class, 'update']); // Uploads handled here
    Route::delete('/courses/{course}/sections/{section}/lectures/{lecture}', [\App\Http\Controllers\LectureController::class, 'destroy']);

    // Quizzes (Instructor)
    Route::post('/courses/{course}/lectures/{lecture}/quiz', [\App\Http\Controllers\QuizController::class, 'store']);
    Route::post('/courses/{course}/quizzes/{quiz}/questions', [\App\Http\Controllers\QuizController::class, 'storeQuestion']);

    // Learning Flow (Student)
    Route::get('/student/dashboard-stats', [\App\Http\Controllers\EnrollmentController::class, 'dashboardStats']);
    Route::post('/courses/{course}/enroll', [\App\Http\Controllers\EnrollmentController::class, 'store']);
    Route::get('/my-courses', [\App\Http\Controllers\EnrollmentController::class, 'index']);
    Route::post('/lectures/{lecture}/progress', [\App\Http\Controllers\ProgressController::class, 'update']); // Toggle complete
    Route::get('/courses/{course}/progress', [\App\Http\Controllers\ProgressController::class, 'show']); // Get completed IDs

    // Social (Reviews & QnA)
    Route::post('/courses/{course}/reviews', [\App\Http\Controllers\ReviewController::class, 'store']);
    Route::post('/courses/{course}/questions', [\App\Http\Controllers\CourseQuestionController::class, 'store']);
    Route::post('/courses/{course}/questions/{question}/answers', [\App\Http\Controllers\CourseQuestionController::class, 'storeAnswer']);

    // Instructor Dashboard
    Route::get('/instructor/dashboard', [\App\Http\Controllers\InstructorController::class, 'dashboard']);
    Route::get('/instructor/courses', [\App\Http\Controllers\InstructorController::class, 'courses']);
});

// Public Social
Route::get('/courses/{course}/reviews', [\App\Http\Controllers\ReviewController::class, 'index']);
Route::get('/courses/{course}/questions', [\App\Http\Controllers\CourseQuestionController::class, 'index']);

// Admin
Route::prefix('admin')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/stats', [\App\Http\Controllers\AdminController::class, 'stats']);
    Route::get('/users', [\App\Http\Controllers\AdminController::class, 'users']);
    Route::get('/courses/pending', [\App\Http\Controllers\AdminController::class, 'pendingCourses']);
    Route::post('/courses/{course}/approve', [\App\Http\Controllers\AdminController::class, 'approveCourse']);
});

// Payment
Route::post('/courses/{course}/payment-intent', [\App\Http\Controllers\PaymentController::class, 'createIntent']);

// Checkout
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/checkout/preview', [\App\Http\Controllers\CheckoutController::class, 'preview']);
    Route::post('/checkout', [\App\Http\Controllers\CheckoutController::class, 'processOrder']);
});

// Stripe Webhook (no auth - verified by signature)
Route::post('/webhooks/stripe', [\App\Http\Controllers\StripeWebhookController::class, 'handleWebhook']);
