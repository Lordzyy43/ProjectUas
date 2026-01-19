<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// =======================
// Controllers
// =======================
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PublicStatsController;
use App\Http\Controllers\API\CourseController;
use App\Http\Controllers\API\CourseCategoryController;
use App\Http\Controllers\API\MaterialController;
use App\Http\Controllers\API\MaterialCategoryController;
use App\Http\Controllers\API\QuizController;
use App\Http\Controllers\API\QuizQuestionController;
use App\Http\Controllers\API\EnrollmentController;
use App\Http\Controllers\API\QuizSubmitController;
use App\Http\Controllers\API\QuizResultController;
use App\Http\Controllers\API\QuizCategoryController;
use App\Http\Controllers\API\QuizPlayController;

/*
|--------------------------------------------------------------------------
| PUBLIC API (Guest / No login)
|--------------------------------------------------------------------------
*/

// ===== Auth =====
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ===== Public Stats =====
Route::get('/stats/overview', [PublicStatsController::class, 'overview']);

// ===== Course Preview =====
Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{id}', [CourseController::class, 'show']);

// ===== Quiz Categories (Filter) =====
Route::get('/quiz-categories', [QuizCategoryController::class, 'index']);

// ===== Quiz Preview =====
Route::get('/quiz', [QuizController::class, 'index']);
// CATATAN: Route /quiz/{id} dipindah ke bawah setelah User API agar tidak bentrok dengan /quiz/.../questions

/*
|--------------------------------------------------------------------------
| USER API (Authenticated)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // ===== Auth User =====
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', fn(Request $r) => $r->user());

    /*
    |--------------------------------------------------------------------------
    | Quiz Play (User) 
    | TARUH DI ATAS agar dibaca duluan daripada /quiz/{id}
    |--------------------------------------------------------------------------
    */
    Route::get('/quiz/{quiz_id}/questions', [QuizPlayController::class, 'questions']); 
    Route::get('/quiz/{quiz_id}/full', [QuizPlayController::class, 'fullQuiz']);       
    Route::post('/quiz/{quiz_id}/submit', [QuizSubmitController::class, 'submit']);    
    Route::get('/quiz/{quiz_id}/result', [QuizResultController::class, 'detailResult']); 
    Route::get('/my-quiz-results', [QuizResultController::class, 'myResults']);        

    /*
    |--------------------------------------------------------------------------
    | Enrollment (User)
    |--------------------------------------------------------------------------
    */
    Route::post('/enroll/{course_id}', [EnrollmentController::class, 'enroll']);
    Route::get('/my-courses', [EnrollmentController::class, 'myCourses']);
    Route::put('/my-courses/{course_id}/progress', [EnrollmentController::class, 'updateProgress']);

    /*
    |--------------------------------------------------------------------------
    | Course Content (Protected)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/courses/{course_id}/materials', [MaterialController::class, 'byCourse']);
        Route::get('/materials', [MaterialController::class, 'index']);
        Route::get('/materials/{id}', [MaterialController::class, 'show']);

        Route::get('/courses/{course_id}/quiz', [QuizController::class, 'byCourse']);
        Route::get('/courses/{course_id}/quiz/{quiz_id}', [QuizController::class, 'showByCourse']);
    });
});

// Jalur terakhir untuk Quiz Preview (Guest)
Route::get('/quiz/{id}', [QuizController::class, 'show']);

/*
|--------------------------------------------------------------------------
| ADMIN API
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'is_admin'])
    ->prefix('admin')
    ->group(function () {

    Route::get('/dashboard', function () {
        return response()->json([
            'users'     => \App\Models\User::count(),
            'courses'   => \App\Models\Course::count(),
            'materials' => \App\Models\Material::count(),
            'quizzes'   => \App\Models\Quiz::count(),
        ]);
    });

    // Course Admin
    Route::get('/courses', [CourseController::class, 'index']);
    Route::post('/courses', [CourseController::class, 'store']);
    Route::get('/courses/select', [CourseController::class, 'select']);
    Route::put('/courses/{id}', [CourseController::class, 'update']);
    Route::delete('/courses/{id}', [CourseController::class, 'destroy']);

    // Quiz Admin (Pertahankan urutan spesifik di atas generic)
    Route::get('/quiz/{quiz_id}/questions', [QuizQuestionController::class, 'index']); 
    Route::post('/quiz/{quiz_id}/questions', [QuizQuestionController::class, 'store']);
    Route::get('/quiz', [QuizController::class, 'index']);
    Route::post('/quiz', [QuizController::class, 'store']);
    Route::put('/quiz/{id}', [QuizController::class, 'update']);
    Route::delete('/quiz/{id}', [QuizController::class, 'destroy']);

    // Sisanya (Material, Categories, dll)
    Route::get('/course-categories', [CourseCategoryController::class, 'index']);
    Route::post('/course-categories', [CourseCategoryController::class, 'store']);
    Route::put('/course-categories/{id}', [CourseCategoryController::class, 'update']);
    Route::delete('/course-categories/{id}', [CourseCategoryController::class, 'destroy']);

    Route::get('/materials', [MaterialController::class, 'index']);
    Route::post('/materials', [MaterialController::class, 'store']);
    Route::put('/materials/{id}', [MaterialController::class, 'update']);
    Route::delete('/materials/{id}', [MaterialController::class, 'destroy']);

    Route::get('/material-categories', [MaterialCategoryController::class, 'index']);
    Route::post('/material-categories', [MaterialCategoryController::class, 'store']);
    Route::put('/material-categories/{id}', [MaterialCategoryController::class, 'update']);
    Route::delete('/material-categories/{id}', [MaterialCategoryController::class, 'destroy']);

    Route::get('/quiz-categories', [QuizCategoryController::class, 'index']);
    Route::post('/quiz-categories', [QuizCategoryController::class, 'store']);
    Route::put('/quiz-categories/{id}', [QuizCategoryController::class, 'update']);
    Route::delete('/quiz-categories/{id}', [QuizCategoryController::class, 'destroy']);

    Route::put('/questions/{id}', [QuizQuestionController::class, 'update']);
    Route::delete('/questions/{id}', [QuizQuestionController::class, 'destroy']);
});