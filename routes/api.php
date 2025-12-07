<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CourseController;
use App\Http\Controllers\API\MaterialController;
use App\Http\Controllers\API\QuizCategoryController;
use App\Http\Controllers\API\QuizController;
use App\Http\Controllers\API\QuizQuestionController;
use App\Http\Controllers\API\EnrollmentController;
use App\Http\Controllers\API\QuizSubmitController;

// AUTH
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// PUBLIC
Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{id}', [CourseController::class, 'show']);
Route::get('/courses/{id}/materials', [CourseController::class, 'materials']);

Route::get('/quiz-category', [QuizCategoryController::class, 'index']);
Route::get('/quiz', [QuizController::class, 'index']);
Route::get('/quiz/{id}', [QuizController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', function (Request $request) {
        return $request->user();
    });

    // Enrollment
    Route::post('/enroll/{course_id}', [EnrollmentController::class, 'enroll']);
    Route::get('/my-courses', [EnrollmentController::class, 'myCourses']);
    Route::put('/my-courses/{course_id}/progress', [EnrollmentController::class, 'updateProgress']);

    // Submit Quiz
    Route::post('/quiz/{id}/submit', [QuizSubmitController::class, 'submit']);

    // ADMIN
    Route::middleware('is_admin')->prefix('admin')->group(function () {

        // COURSE
        Route::post('/courses', [CourseController::class, 'store']);
        Route::put('/courses/{id}', [CourseController::class, 'update']);
        Route::delete('/courses/{id}', [CourseController::class, 'destroy']);

        // MATERIALS
        Route::post('/materials', [MaterialController::class, 'store']);
        Route::put('/materials/{id}', [MaterialController::class, 'update']);
        Route::delete('/materials/{id}', [MaterialController::class, 'destroy']);

        // QUIZ CATEGORY
        Route::apiResource('quiz-category', QuizCategoryController::class)->except(['show', 'index']);

        // QUIZ
        Route::post('/quiz', [QuizController::class, 'store']);
        Route::put('/quiz/{id}', [QuizController::class, 'update']);
        Route::delete('/quiz/{id}', [QuizController::class, 'destroy']);

        // QUIZ QUESTIONS
        Route::post('/quiz/{id}/questions', [QuizQuestionController::class, 'store']);
        Route::put('/questions/{id}', [QuizQuestionController::class, 'update']);
        Route::delete('/questions/{id}', [QuizQuestionController::class, 'destroy']);
    });
});
