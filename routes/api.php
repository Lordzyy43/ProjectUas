<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// =======================
// Controllers
// =======================
use App\Http\Controllers\API\AuthController;
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

// ===== Course Preview =====
// Bisa diakses guest
// Bisa filter per category: ?category=slug
Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{id}', [CourseController::class, 'show']);

// ===== Quiz Preview =====
// Bisa diakses guest
Route::get('/quiz', [QuizController::class, 'index']);
Route::get('/quiz/{id}', [QuizController::class, 'show']);

// ===== Quiz Categories (Filter) =====
// Bisa diakses guest
Route::get('/quiz-categories', [QuizCategoryController::class, 'index']);

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
    | Enrollment (User)
    | User daftar course & lihat progres
    |--------------------------------------------------------------------------
    */
    Route::post('/enroll/{course_id}', [EnrollmentController::class, 'enroll']);           // daftar course
    Route::get('/my-courses', [EnrollmentController::class, 'myCourses']);                // lihat course milik user
    Route::put('/my-courses/{course_id}/progress', [EnrollmentController::class, 'updateProgress']); // update progres

    /*
    |--------------------------------------------------------------------------
    | Course Content (Protected)
    | Hanya course yang sudah di-enroll
    |--------------------------------------------------------------------------
    */
    Route::middleware('check_enrollment')->group(function () {
        // Ambil materi course
        Route::get('/courses/{course_id}/materials', [MaterialController::class, 'byCourse']);

        // Quiz dalam course
        Route::get('/courses/{course_id}/quiz', [QuizController::class, 'byCourse']);
        Route::get('/courses/{course_id}/quiz/{quiz_id}', [QuizController::class, 'showByCourse']);
    });

    /*
    |--------------------------------------------------------------------------
    | Quiz Play (User)
    |--------------------------------------------------------------------------
    */
    Route::get('/quiz/{quiz_id}/questions', [QuizPlayController::class, 'questions']); // ambil soal untuk dikerjakan
    Route::get('/quiz/{quiz_id}/full', [QuizPlayController::class, 'fullQuiz']);       // detail quiz + status
    Route::post('/quiz/{quiz_id}/submit', [QuizSubmitController::class, 'submit']);    // submit jawaban
    Route::get('/my-quiz-results', [QuizResultController::class, 'myResults']);        // riwayat hasil quiz
    Route::get('/quiz/{quiz_id}/result', [QuizResultController::class, 'detailResult']); // detail hasil setelah submit
});

/*
|--------------------------------------------------------------------------
| ADMIN API
| Login + Token + is_admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'is_admin'])
    ->prefix('admin')
    ->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard Summary
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', function () {
        return response()->json([
            'users'     => \App\Models\User::count(),
            'courses'   => \App\Models\Course::count(),
            'materials' => \App\Models\Material::count(),
            'quizzes'   => \App\Models\Quiz::count(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | Course Management (CRUD)
    |--------------------------------------------------------------------------
    */
    Route::get('/courses', [CourseController::class, 'index']);      // list all courses
    Route::post('/courses', [CourseController::class, 'store']);     // create course
    Route::get('/courses/select', [CourseController::class, 'select']); // untuk select input
    Route::put('/courses/{id}', [CourseController::class, 'update']);   // update course
    Route::delete('/courses/{id}', [CourseController::class, 'destroy']); // delete course

    /*
    |--------------------------------------------------------------------------
    | Course Category Management (CRUD)
    |--------------------------------------------------------------------------
    */
    Route::get('/course-categories', [CourseCategoryController::class, 'index']); // list categories
    Route::post('/course-categories', [CourseCategoryController::class, 'store']); // create
    Route::put('/course-categories/{id}', [CourseCategoryController::class, 'update']); // update
    Route::delete('/course-categories/{id}', [CourseCategoryController::class, 'destroy']); // delete

    /*
    |--------------------------------------------------------------------------
    | Material Management (CRUD)
    |--------------------------------------------------------------------------
    */
    Route::get('/materials', [MaterialController::class, 'index']); // list all materials
    Route::post('/materials', [MaterialController::class, 'store']); // create
    Route::put('/materials/{id}', [MaterialController::class, 'update']); // update
    Route::delete('/materials/{id}', [MaterialController::class, 'destroy']); // delete

    /*
    |--------------------------------------------------------------------------
    | Material Category Management (CRUD)
    |--------------------------------------------------------------------------
    */
    Route::get('/material-categories', [MaterialCategoryController::class, 'index']);
    Route::post('/material-categories', [MaterialCategoryController::class, 'store']);
    Route::put('/material-categories/{id}', [MaterialCategoryController::class, 'update']);
    Route::delete('/material-categories/{id}', [MaterialCategoryController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Quiz Category Management (CRUD)
    |--------------------------------------------------------------------------
    */
    Route::get('/quiz-categories', [QuizCategoryController::class, 'index']);
    Route::post('/quiz-categories', [QuizCategoryController::class, 'store']);
    Route::put('/quiz-categories/{id}', [QuizCategoryController::class, 'update']);
    Route::delete('/quiz-categories/{id}', [QuizCategoryController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Quiz Management (CRUD)
    |--------------------------------------------------------------------------
    */
    Route::get('/quiz', [QuizController::class, 'index']);
    Route::post('/quiz', [QuizController::class, 'store']);
    Route::put('/quiz/{id}', [QuizController::class, 'update']);
    Route::delete('/quiz/{id}', [QuizController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Quiz Question Management (CRUD)
    |--------------------------------------------------------------------------
    */
    Route::get('/quiz/{quiz_id}/questions', [QuizQuestionController::class, 'index']); // list questions
    Route::post('/quiz/{quiz_id}/questions', [QuizQuestionController::class, 'store']); // create
    Route::put('/questions/{id}', [QuizQuestionController::class, 'update']);           // update
    Route::delete('/questions/{id}', [QuizQuestionController::class, 'destroy']);       // delete
});
