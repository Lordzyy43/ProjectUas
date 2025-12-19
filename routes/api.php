<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
| Tanpa login, tanpa token
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{id}', [CourseController::class, 'show']);

Route::get('/quiz-categories', [QuizCategoryController::class, 'index']);


Route::get('/quiz', [QuizController::class, 'index']);
Route::get('/quiz/{id}', [QuizController::class, 'show']);

/*
|--------------------------------------------------------------------------
| USER API
| Login + Token (auth:sanctum)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', function (Request $request) {
        return $request->user();
    });

    // Enrollment
    Route::post('/enroll/{course_id}', [EnrollmentController::class, 'enroll']);
    Route::get('/my-courses', [EnrollmentController::class, 'myCourses']);
    Route::put('/my-courses/{course_id}/progress', [EnrollmentController::class, 'updateProgress']);

    // Materials (HARUS SUDAH ENROLL)
    Route::get(
        '/courses/{id}/materials',
        [MaterialController::class, 'byCourse']
    );

    // Quiz
    Route::post('/quiz/{id}/submit', [QuizSubmitController::class, 'submit']);

    //  Menapilakn hasil quiz milik user yang sedang login
    Route::get('/my-quiz-results', [QuizResultController::class, 'myResults']);
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

        // Courses
        Route::post('/courses', [CourseController::class, 'store']);
        Route::get('/courses', [CourseController::class, 'index']);
        Route::put('/courses/{id}', [CourseController::class, 'update']);
        Route::delete('/courses/{id}', [CourseController::class, 'destroy']);

        // Materials
        Route::post('/materials', [MaterialController::class, 'store']);
        Route::get('/materials', [MaterialController::class, 'index']);
        Route::put('/materials/{id}', [MaterialController::class, 'update']);
        Route::delete('/materials/{id}', [MaterialController::class, 'destroy']);
        
        // Quiz Categories
        Route::post('/quiz-categories', [QuizCategoryController::class, 'store']);
        Route::get('/quiz-categories', [QuizcategoryController::class, 'index']);
        Route::put('/quiz-categories/{id}', [QuizCategoryController::class, 'update']);
        Route::delete('/quiz-categories/{id}', [QuizCategoryController::class, 'destroy']);
        
        // Quiz
        Route::post('/quiz', [QuizController::class, 'store']);
        Route::get('/quiz', [QuizController::class, 'index']);
        Route::put('/quiz/{id}', [QuizController::class, 'update']);
        Route::delete('/quiz/{id}', [QuizController::class, 'destroy']);

        // Quiz Questions
        Route::post('/quiz/{id}/questions', [QuizQuestionController::class, 'store']);
        Route::get('//quiz/{id}/questions', [QuizQuestionController::class, 'index']);
        Route::put('/questions/{id}', [QuizQuestionController::class, 'update']);
        Route::delete('/questions/{id}', [QuizQuestionController::class, 'destroy']);
    });
