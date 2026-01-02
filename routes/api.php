<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// =======================
// Controllers
// =======================
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CourseController;
use App\Http\Controllers\API\MaterialController;
use App\Http\Controllers\API\QuizController;
use App\Http\Controllers\API\QuizQuestionController;
use App\Http\Controllers\API\EnrollmentController;
use App\Http\Controllers\API\QuizSubmitController;
use App\Http\Controllers\API\QuizResultController;
use App\Http\Controllers\API\QuizCategoryController;
use App\Http\Controllers\API\QuizPlayController;

/*
|--------------------------------------------------------------------------
| PUBLIC API (GUEST)
| Tanpa login – landing page & preview
|--------------------------------------------------------------------------
*/

// ===== Auth =====
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ===== Course Preview =====
Route::get('/courses', [CourseController::class, 'index']);      // list course publik
Route::get('/courses/{id}', [CourseController::class, 'show']);  // detail course publik

// ===== Quiz Preview =====
Route::get('/quiz', [QuizController::class, 'index']);           // list quiz publik
Route::get('/quiz/{id}', [QuizController::class, 'show']);       // detail quiz (tanpa soal)

// ===== Quiz Categories =====
Route::get('/quiz-categories', [QuizCategoryController::class, 'index']); // filter quiz

/*
|--------------------------------------------------------------------------
| USER API (AUTHENTICATED)
| Login + Token (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // ===== Auth User =====
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', fn (Request $r) => $r->user());

    /*
    |--------------------------------------------------------------------------
    | Enrollment
    | User daftar & progres course
    |--------------------------------------------------------------------------
    */
    Route::post('/enroll/{course_id}', [EnrollmentController::class, 'enroll']);          // daftar course
    Route::get('/my-courses', [EnrollmentController::class, 'myCourses']);                // course milik user
    Route::put('/my-courses/{course_id}/progress', [EnrollmentController::class, 'updateProgress']); // progress

    /*
    |--------------------------------------------------------------------------
    | Course Content (Protected)
    | Hanya course yang sudah di-enroll
    |--------------------------------------------------------------------------
    */
    Route::middleware('check_enrollment')->group(function () {

        // ===== Materials =====
        Route::get(
            '/courses/{course_id}/materials',
            [MaterialController::class, 'byCourse']
        );

        // ===== Quiz dalam course =====
        Route::get(
            '/courses/{course_id}/quiz',
            [QuizController::class, 'byCourse']
        );

        Route::get(
            '/courses/{course_id}/quiz/{quiz_id}',
            [QuizController::class, 'showByCourse']
        );
    });

    /*
    |--------------------------------------------------------------------------
    | Quiz Play (USER)
    |--------------------------------------------------------------------------
    */

    // 🔹 Ambil SOAL quiz untuk dikerjakan user
    // Dipakai oleh QuizPlay.jsx
    Route::get(
        '/quiz/{quiz_id}/questions',
        [QuizPlayController::class, 'questions']
    );

    // 🔹 Ambil DETAIL quiz beserta status sudah dikerjakan atau belum
    Route::get(
        '/quiz/{quiz_id}/full',
        [QuizPlayController::class, 'fullQuiz']
    );

    // 🔹 Submit jawaban quiz
    Route::post(
        '/quiz/{quiz_id}/submit',
        [QuizSubmitController::class, 'submit']
    );

    // Detail hasil quiz setelah submit
    Route::get(
        '/quiz/{quiz_id}/result',
        [QuizResultController::class, 'detailResult']
    );

    // 🔹 Riwayat & hasil quiz user
    Route::get(
        '/my-quiz-results',
        [QuizResultController::class, 'myResults']
    );
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
    | Dashboard
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
    | Course Management
    |--------------------------------------------------------------------------
    */
    Route::get('/courses', [CourseController::class, 'index']);
    Route::post('/courses', [CourseController::class, 'store']);
    Route::get('/courses/select', [CourseController::class, 'select']);
    Route::put('/courses/{id}', [CourseController::class, 'update']);
    Route::delete('/courses/{id}', [CourseController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Material Management
    |--------------------------------------------------------------------------
    */
    Route::get('/materials', [MaterialController::class, 'index']);
    Route::post('/materials', [MaterialController::class, 'store']);
    Route::put('/materials/{id}', [MaterialController::class, 'update']);
    Route::delete('/materials/{id}', [MaterialController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Quiz Category Management
    |--------------------------------------------------------------------------
    */
    Route::get('/quiz-categories', [QuizCategoryController::class, 'index']);
    Route::post('/quiz-categories', [QuizCategoryController::class, 'store']);
    Route::put('/quiz-categories/{id}', [QuizCategoryController::class, 'update']);
    Route::delete('/quiz-categories/{id}', [QuizCategoryController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Quiz Management
    |--------------------------------------------------------------------------
    */
    Route::get('/quiz', [QuizController::class, 'index']);
    Route::post('/quiz', [QuizController::class, 'store']);
    Route::put('/quiz/{id}', [QuizController::class, 'update']);
    Route::delete('/quiz/{id}', [QuizController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Quiz Question Management (ADMIN)
    |--------------------------------------------------------------------------
    */
    Route::get('/quiz/{quiz_id}/questions', [QuizQuestionController::class, 'index']);
    Route::post('/quiz/{quiz_id}/questions', [QuizQuestionController::class, 'store']);
    Route::put('/questions/{id}', [QuizQuestionController::class, 'update']);
    Route::delete('/questions/{id}', [QuizQuestionController::class, 'destroy']);
});
