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

// Public Auth (with rate limiting)
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:registration');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth');
Route::post('/forgot-password', [App\Http\Controllers\NewPasswordController::class, 'forgotPassword'])->middleware('throttle:password-reset');
Route::post('/reset-password', [App\Http\Controllers\NewPasswordController::class, 'resetPassword'])->name('password.reset')->middleware('throttle:password-reset');

// Social Auth
Route::group(['middleware' => ['web']], function () {
    Route::get('/auth/{provider}/redirect', [App\Http\Controllers\SocialAuthController::class, 'redirect']);
    Route::get('/auth/{provider}/callback', [App\Http\Controllers\SocialAuthController::class, 'callback']);
});

// Debug route removed for security - do not expose config in production

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

    // Video Upload (Chunked)
    Route::post('/upload/video', [\App\Http\Controllers\UploadController::class, 'upload']);

    // Video Streaming (Secure)
    Route::get('/stream/{lecture}/{filename}', [\App\Http\Controllers\VideoController::class, 'stream']);

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
    Route::put('/courses/{course}/quizzes/{quiz}', [\App\Http\Controllers\QuizController::class, 'update']); // Update quiz settings
    Route::post('/courses/{course}/quizzes/{quiz}/questions', [\App\Http\Controllers\QuizController::class, 'storeQuestion']);
    Route::put('/courses/{course}/quizzes/{quiz}/questions/{question}', [\App\Http\Controllers\QuizController::class, 'updateQuestion']);
    Route::delete('/courses/{course}/quizzes/{quiz}/questions/{question}', [\App\Http\Controllers\QuizController::class, 'destroyQuestion']);

    // Quizzes (Student)
    Route::get('/courses/{course}/quizzes/{quiz}', [\App\Http\Controllers\QuizController::class, 'show']);
    Route::post('/courses/{course}/quizzes/{quiz}/submit', [\App\Http\Controllers\QuizController::class, 'submit']);
    Route::get('/courses/{course}/quizzes/{quiz}/attempts', [\App\Http\Controllers\QuizController::class, 'attempts']);

    // Learning Flow (Student)
    Route::get('/student/dashboard-stats', [\App\Http\Controllers\EnrollmentController::class, 'dashboardStats']);
    Route::post('/courses/{course}/enroll', [\App\Http\Controllers\EnrollmentController::class, 'store']);
    Route::get('/my-courses', [\App\Http\Controllers\EnrollmentController::class, 'index']);
    Route::post('/lectures/{lecture}/progress', [\App\Http\Controllers\ProgressController::class, 'update']); // Toggle complete
    Route::get('/courses/{course}/progress', [\App\Http\Controllers\ProgressController::class, 'show']); // Get completed IDs

    // Lecture Resources
    Route::get('/courses/{course}/sections/{section}/lectures/{lecture}/resources', [\App\Http\Controllers\LectureController::class, 'getResources']);
    Route::post('/courses/{course}/sections/{section}/lectures/{lecture}/resources', [\App\Http\Controllers\LectureController::class, 'storeResource']);
    Route::delete('/courses/{course}/sections/{section}/lectures/{lecture}/resources/{resource}', [\App\Http\Controllers\LectureController::class, 'destroyResource']);
    Route::get('/lectures/{lecture}/resources/{resource}/download', [\App\Http\Controllers\LectureController::class, 'downloadResource']);

    // Student Notes
    Route::get('/courses/{course}/notes', [\App\Http\Controllers\NoteController::class, 'index']);
    Route::post('/courses/{course}/notes', [\App\Http\Controllers\NoteController::class, 'store']);
    Route::put('/courses/{course}/notes/{note}', [\App\Http\Controllers\NoteController::class, 'update']);
    Route::delete('/courses/{course}/notes/{note}', [\App\Http\Controllers\NoteController::class, 'destroy']);

    // Social (Reviews & QnA)
    Route::post('/courses/{course}/reviews', [\App\Http\Controllers\ReviewController::class, 'store']);
    Route::post('/courses/{course}/questions', [\App\Http\Controllers\CourseQuestionController::class, 'store']);
    Route::post('/courses/{course}/questions/{question}/answers', [\App\Http\Controllers\CourseQuestionController::class, 'storeAnswer']);

    // Instructor Dashboard
    Route::get('/instructor/dashboard', [\App\Http\Controllers\InstructorController::class, 'dashboard']);
    Route::get('/instructor/courses', [\App\Http\Controllers\InstructorController::class, 'courses']);
    Route::post('/instructor/courses', [\App\Http\Controllers\InstructorController::class, 'store']);
    Route::patch('/instructor/courses/{course}/publish', [\App\Http\Controllers\InstructorController::class, 'publish']);
    Route::get('/instructor/analytics', [\App\Http\Controllers\InstructorController::class, 'analytics']);
    Route::get('/instructor/earnings', [\App\Http\Controllers\PayoutController::class, 'earningsStats']);
    Route::get('/instructor/payouts', [\App\Http\Controllers\PayoutController::class, 'index']);
    Route::post('/instructor/payouts', [\App\Http\Controllers\PayoutController::class, 'store']);

    // Wishlist (backend persistence)
    Route::get('/wishlist', [\App\Http\Controllers\WishlistController::class, 'index']);
    Route::post('/wishlist', [\App\Http\Controllers\WishlistController::class, 'store']);
    Route::delete('/wishlist/{courseId}', [\App\Http\Controllers\WishlistController::class, 'destroy']);
    Route::get('/wishlist/check/{courseId}', [\App\Http\Controllers\WishlistController::class, 'check']);
});

// Public Social
Route::get('/courses/{course}/reviews', [\App\Http\Controllers\ReviewController::class, 'index']);
Route::get('/courses/{course}/questions', [\App\Http\Controllers\CourseQuestionController::class, 'index']);

// Admin (requires admin role)
Route::prefix('admin')->middleware(['auth:sanctum'])->group(function () {
    // Admin role check applied to all admin routes
    Route::middleware([\App\Http\Middleware\EnsureUserIsAdmin::class])->group(function () {

        // Dashboard & Analytics
        Route::get('/stats', [\App\Http\Controllers\AdminController::class, 'stats']);
        Route::get('/analytics', [\App\Http\Controllers\AdminController::class, 'analytics']);

        // User Management
        Route::get('/users', [\App\Http\Controllers\AdminController::class, 'users']);
        Route::get('/users/{user}', [\App\Http\Controllers\AdminController::class, 'showUser']);
        Route::put('/users/{user}', [\App\Http\Controllers\AdminController::class, 'updateUser']);
        Route::post('/users/{user}/role', [\App\Http\Controllers\AdminController::class, 'updateUserRole']);
        Route::post('/users/{user}/status', [\App\Http\Controllers\AdminController::class, 'toggleUserStatus']);
        Route::post('/users/{user}/ban', [\App\Http\Controllers\AdminController::class, 'toggleBan']);

        // Instructor Management
        Route::get('/instructors', [\App\Http\Controllers\AdminController::class, 'instructors']);
        Route::get('/instructors/pending', [\App\Http\Controllers\AdminController::class, 'pendingInstructors']);
        Route::get('/instructors/{user}/stats', [\App\Http\Controllers\AdminController::class, 'instructorStats']);
        Route::post('/instructors/{user}/verify', [\App\Http\Controllers\AdminController::class, 'verifyInstructor']);
        Route::post('/instructors/{user}/restrict', [\App\Http\Controllers\AdminController::class, 'restrictInstructor']);

        // Course Management
        Route::get('/courses', [\App\Http\Controllers\AdminController::class, 'courses']);
        Route::get('/courses/pending', [\App\Http\Controllers\AdminController::class, 'pendingCourses']);
        Route::put('/courses/{course}', [\App\Http\Controllers\AdminController::class, 'updateCourse']);
        Route::post('/courses/{course}/approve', [\App\Http\Controllers\AdminController::class, 'approveCourse']);
        Route::post('/courses/{course}/hide', [\App\Http\Controllers\AdminController::class, 'hideCourse']);
        Route::post('/courses/{course}/restore', [\App\Http\Controllers\AdminController::class, 'restoreCourse']);
        Route::post('/courses/{course}/toggle-qna', [\App\Http\Controllers\AdminController::class, 'toggleCourseQnA']);
        Route::delete('/courses/{course}/sections/{section}', [\App\Http\Controllers\AdminController::class, 'removeSection']);
        Route::delete('/courses/{course}/lectures/{lecture}', [\App\Http\Controllers\AdminController::class, 'removeLecture']);

        // Enrollment Management
        Route::get('/enrollments', [\App\Http\Controllers\AdminController::class, 'enrollments']);
        Route::post('/enrollments', [\App\Http\Controllers\AdminController::class, 'addEnrollment']);
        Route::delete('/enrollments/{enrollment}', [\App\Http\Controllers\AdminController::class, 'removeEnrollment']);
        Route::get('/students/activity', [\App\Http\Controllers\AdminController::class, 'studentActivity']);

        // Review & Q&A Moderation
        Route::get('/reviews', [\App\Http\Controllers\AdminController::class, 'reviews']);
        Route::delete('/reviews/{review}', [\App\Http\Controllers\AdminController::class, 'removeReview']);
        Route::get('/questions', [\App\Http\Controllers\AdminController::class, 'questions']);
        Route::delete('/questions/{question}', [\App\Http\Controllers\AdminController::class, 'removeQuestion']);
        Route::delete('/answers/{answer}', [\App\Http\Controllers\AdminController::class, 'removeAnswer']);

        // Promo Codes (Admin)
        Route::get('/promo-codes', [\App\Http\Controllers\PromoCodeController::class, 'index']);
        Route::post('/promo-codes', [\App\Http\Controllers\PromoCodeController::class, 'store']);
        Route::put('/promo-codes/{promoCode}', [\App\Http\Controllers\PromoCodeController::class, 'update']);
        Route::delete('/promo-codes/{promoCode}', [\App\Http\Controllers\PromoCodeController::class, 'destroy']);

        // Settings Management
        Route::get('/settings/categories', [\App\Http\Controllers\AdminSettingsController::class, 'categories']);
        Route::post('/settings/categories', [\App\Http\Controllers\AdminSettingsController::class, 'storeCategory']);
        Route::put('/settings/categories/{category}', [\App\Http\Controllers\AdminSettingsController::class, 'updateCategory']);
        Route::delete('/settings/categories/{category}', [\App\Http\Controllers\AdminSettingsController::class, 'destroyCategory']);

        Route::get('/settings/platform', [\App\Http\Controllers\AdminSettingsController::class, 'platformSettings']);
        Route::put('/settings/platform', [\App\Http\Controllers\AdminSettingsController::class, 'updatePlatformSettings']);
        Route::get('/settings/payment', [\App\Http\Controllers\AdminSettingsController::class, 'paymentSettings']);
        Route::put('/settings/payment', [\App\Http\Controllers\AdminSettingsController::class, 'updatePaymentSettings']);
        Route::get('/settings/security', [\App\Http\Controllers\AdminSettingsController::class, 'securitySettings']);
        Route::put('/settings/security', [\App\Http\Controllers\AdminSettingsController::class, 'updateSecuritySettings']);
        Route::get('/settings/localization', [\App\Http\Controllers\AdminSettingsController::class, 'localizationSettings']);
        Route::put('/settings/localization', [\App\Http\Controllers\AdminSettingsController::class, 'updateLocalizationSettings']);
    });
});

// Promo Code Validation (for checkout)
Route::middleware(['auth:sanctum'])->post('/promo-codes/validate', [\App\Http\Controllers\PromoCodeController::class, 'validate']);

// Payment & Checkout (requires auth)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/courses/{course}/payment-intent', [\App\Http\Controllers\PaymentController::class, 'createIntent']);
    Route::post('/checkout/preview', [\App\Http\Controllers\CheckoutController::class, 'preview']);
    Route::post('/checkout', [\App\Http\Controllers\CheckoutController::class, 'processOrder']);
});

// Stripe Webhook (no auth - verified by signature)
Route::post('/webhooks/stripe', [\App\Http\Controllers\StripeWebhookController::class, 'handleWebhook']);

// Certificates
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/certificates', [\App\Http\Controllers\CertificateController::class, 'index']);
    Route::post('/courses/{course}/certificate', [\App\Http\Controllers\CertificateController::class, 'generate']);
    // Route::get('/certificates/{certificate}/download', [\App\Http\Controllers\CertificateController::class, 'download']);
    Route::get('/certificates/{certificate}/download-url', [\App\Http\Controllers\CertificateController::class, 'getDownloadUrl']);
});

// Public certificate verification
Route::get('/verify/{certificateNumber}', [\App\Http\Controllers\CertificateController::class, 'verify']);
Route::get('/certificates/signed-download/{certificate}/{user}', [\App\Http\Controllers\CertificateController::class, 'downloadSigned'])
    ->name('certificates.download.signed')
    ->middleware('signed');

// Public Profiles
Route::get('/profile/{identifier}', [\App\Http\Controllers\ProfileController::class, 'show']);

// Bundles
Route::get('/bundles', [\App\Http\Controllers\BundleController::class, 'index']);
Route::get('/bundles/{bundle}', [\App\Http\Controllers\BundleController::class, 'show']);

// Authenticated Profile & Gifts
Route::middleware(['auth:sanctum'])->group(function () {
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update']);
    Route::get('/my-badges', [\App\Http\Controllers\ProfileController::class, 'badges']);

    // Gifts
    Route::post('/gifts', [\App\Http\Controllers\GiftController::class, 'create']);
    Route::post('/gifts/redeem', [\App\Http\Controllers\GiftController::class, 'redeem']);
    Route::get('/gifts/sent', [\App\Http\Controllers\GiftController::class, 'sentGifts']);
    Route::get('/gifts/received', [\App\Http\Controllers\GiftController::class, 'receivedGifts']);

    // Referrals (Affiliate)
    Route::get('/referral/code', [\App\Http\Controllers\ReferralController::class, 'getCode']);
    Route::get('/referral/stats', [\App\Http\Controllers\ReferralController::class, 'stats']);

    // Assignments
    Route::get('/courses/{course}/assignments/{assignment}', [\App\Http\Controllers\AssignmentController::class, 'show']);
    Route::post('/courses/{course}/assignments/{assignment}/submit', [\App\Http\Controllers\AssignmentController::class, 'submit']);
    Route::post('/courses/{course}/submissions/{submission}/grade', [\App\Http\Controllers\AssignmentController::class, 'grade']);
    Route::get('/courses/{course}/assignments/{assignment}/submissions', [\App\Http\Controllers\AssignmentController::class, 'submissions']);
});
