<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CourseController;
use App\Http\Controllers\API\MaterialController;
use App\Http\Controllers\API\QuizController;
use App\Http\Controllers\API\QuizQuestionController;
use App\Http\Controllers\API\EnrollmentController;
use App\Http\Controllers\API\QuizSubmitController;
use App\Http\Controllers\API\QuizResultController;
use App\Http\Controllers\API\QuizCategoryController;

/*
|--------------------------------------------------------------------------
| PUBLIC API
| Bisa diakses TANPA login (guest)
|--------------------------------------------------------------------------
*/

Route::name('auth.')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

// Course publik (landing page)
Route::name('courses.')->group(function () {
    Route::get('/courses', [CourseController::class, 'index'])->name('index');
    Route::get('/courses/{id}', [CourseController::class, 'show'])->name('show');
});

// Quiz publik (preview)
Route::name('quiz.')->group(function () {
    Route::get('/quiz', [QuizController::class, 'index'])->name('index');
    Route::get('/quiz/{id}', [QuizController::class, 'show'])->name('show');
});

// Quiz categories (filtering)
Route::get(
    '/quiz-categories',
    [QuizCategoryController::class, 'index']
)->name('quiz-categories.index');


/*
|--------------------------------------------------------------------------
| USER API
| Login + Token (auth:sanctum)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')
    ->name('user.')
    ->group(function () {

    // Logout & Profile
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/profile', function (Request $request) {
        return $request->user();
    })->name('profile');

    /*
    |--------------------------------------------------------------------------
    | Enrollment (User mendaftar course)
    |--------------------------------------------------------------------------
    */
    Route::name('enrollments.')->group(function () {
        Route::post('/enroll/{course_id}', [EnrollmentController::class, 'enroll'])
            ->name('store');

        Route::get('/my-courses', [EnrollmentController::class, 'myCourses'])
            ->name('my-courses');

        Route::put(
            '/my-courses/{course_id}/progress',
            [EnrollmentController::class, 'updateProgress']
        )->name('progress.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Course Content (HANYA course yang sudah di-enroll)
    |--------------------------------------------------------------------------
    */
    Route::name('course-content.')
    ->middleware('check_enrollment')
    ->group(function () {

        // Materials per course
        Route::get(
            '/courses/{course_id}/materials',
            [MaterialController::class, 'byCourse']
        )->name('materials');

        // Quiz list per course
        Route::get(
            '/courses/{course_id}/quiz',
            [QuizController::class, 'byCourse']
        )->name('quiz.index');

        // Detail quiz dalam course
        Route::get(
            '/courses/{course_id}/quiz/{quiz_id}',
            [QuizController::class, 'showByCourse']
        )->name('quiz.show');
    });

    /*
    |--------------------------------------------------------------------------
    | Quiz Submission & Result
    |--------------------------------------------------------------------------
    */
    Route::post(
        '/quiz/{id}/submit',
        [QuizSubmitController::class, 'submit']
    )->name('quiz.submit');

    Route::get(
        '/my-quiz-results',
        [QuizResultController::class, 'myResults']
    )->name('quiz.results');
});


/*
|--------------------------------------------------------------------------
| ADMIN API
| Login + Token + is_admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'is_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard (statistik)
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', function () {
        return response()->json([
            'users'     => \App\Models\User::count(),
            'courses'   => \App\Models\Course::count(),
            'materials' => \App\Models\Material::count(),
            'quizzes'   => \App\Models\Quiz::count(),
        ]);
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Course Management
    |--------------------------------------------------------------------------
    */
    Route::name('courses.')->group(function () {
        Route::get('/courses', [CourseController::class, 'index'])->name('index');
        Route::post('/courses', [CourseController::class, 'store'])->name('store');
        Route::get('/courses/select', [CourseController::class, 'select'])->name('select');
        Route::put('/courses/{id}', [CourseController::class, 'update'])->name('update');
        Route::delete('/courses/{id}', [CourseController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Material Management
    |--------------------------------------------------------------------------
    */
    Route::name('materials.')->group(function () {
        Route::get('/materials', [MaterialController::class, 'index'])->name('index');
        Route::post('/materials', [MaterialController::class, 'store'])->name('store');
        Route::put('/materials/{id}', [MaterialController::class, 'update'])->name('update');
        Route::delete('/materials/{id}', [MaterialController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Quiz Category Management
    |--------------------------------------------------------------------------
    */
    Route::name('quiz-categories.')->group(function () {
        Route::get('/quiz-categories', [QuizCategoryController::class, 'index'])->name('index');
        Route::post('/quiz-categories', [QuizCategoryController::class, 'store'])->name('store');
        Route::put('/quiz-categories/{id}', [QuizCategoryController::class, 'update'])->name('update');
        Route::delete('/quiz-categories/{id}', [QuizCategoryController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Quiz Management
    |--------------------------------------------------------------------------
    */
    Route::name('quiz.')->group(function () {
        Route::get('/quiz', [QuizController::class, 'index'])->name('index');
        Route::post('/quiz', [QuizController::class, 'store'])->name('store');
        Route::put('/quiz/{id}', [QuizController::class, 'update'])->name('update');
        Route::delete('/quiz/{id}', [QuizController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Quiz Question Management
    |--------------------------------------------------------------------------
    */
    Route::name('quiz-questions.')->group(function () {
        Route::get('/quiz/{id}/questions', [QuizQuestionController::class, 'index'])->name('index');
        Route::post('/quiz/{quiz_id}/questions', [QuizQuestionController::class, 'store'])->name('store');
        Route::put('/questions/{id}', [QuizQuestionController::class, 'update'])->name('update');
        Route::delete('/questions/{id}', [QuizQuestionController::class, 'destroy'])->name('destroy');
    });
});
