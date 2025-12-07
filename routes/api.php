<?php
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CourseController;
use App\Http\Controllers\API\MaterialController;
use App\Http\Controllers\API\QuizCategoryController;
use App\Http\Controllers\API\QuizController;
use App\Http\Controllers\API\QuizQuestionController;
use App\Http\Controllers\API\EnrollmentController;
use App\Http\Controllers\API\QuizSubmitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class,'register']);
Route::post('/login', [AuthController::class,'login']);

Route::get('/courses', [CourseController::class,'index']);
Route::get('/courses/{id}', [CourseController::class,'show']);
Route::get('/courses/{id}/materials', [CourseController::class,'show']); // show includes materials

Route::get('/quiz-category', [QuizCategoryController::class,'index']);
Route::get('/quiz', [QuizController::class,'index']);
Route::get('/quiz/{id}', [QuizController::class,'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class,'logout']);
    Route::get('/profile', function(Request $r){ return $r->user(); });

    // Enrollment & user actions
    Route::post('/enroll/{course_id}', [EnrollmentController::class,'enroll']);
    Route::get('/my-courses', [EnrollmentController::class,'myCourses']);
    Route::put('/my-courses/{course_id}/progress', [EnrollmentController::class,'updateProgress']);

    // Submit quiz
    Route::post('/quiz/{id}/submit', [QuizSubmitController::class,'submit']);
    
    // Admin routes
    Route::middleware('is_admin')->prefix('admin')->group(function () {
        // course
        Route::post('/courses', [CourseController::class,'store']);
        Route::put('/courses/{id}', [CourseController::class,'update']);
        Route::delete('/courses/{id}', [CourseController::class,'destroy']);

        // materials
        Route::post('/materials', [MaterialController::class,'store']);
        Route::put('/materials/{id}', [MaterialController::class,'update']);
        Route::delete('/materials/{id}', [MaterialController::class,'destroy']);

        // quiz categories
        Route::apiResource('quiz-category', QuizCategoryController::class)->except(['show','index']);

        // quiz
        Route::post('/quiz', [QuizController::class,'store']);
        Route::put('/quiz/{id}', [QuizController::class,'update']);
        Route::delete('/quiz/{id}', [QuizController::class,'destroy']);

        // questions
        Route::post('/quiz/{id}/questions', [QuizQuestionController::class,'store']);
        Route::put('/questions/{id}', [QuizQuestionController::class,'update']);
        Route::delete('/questions/{id}', [QuizQuestionController::class,'destroy']);
    });
});
