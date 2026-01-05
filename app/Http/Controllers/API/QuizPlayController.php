<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizSubmit;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Exception;

class QuizPlayController extends Controller
{
    /**
     * Ambil daftar soal untuk dikerjakan user (protected route)
     */
    public function questions($quizId)
    {
        try {
            $quiz = Quiz::with(['questions:id,quiz_id,question_text,option_a,option_b,option_c,option_d,correct_answer'])
                        ->findOrFail($quizId);

            // cek enrollment user
            if (!auth()->user()->enrollments()->where('course_id', $quiz->course_id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda harus mendaftar course terlebih dahulu'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'quiz_id' => $quiz->id,
                    'title' => $quiz->title,
                    'time_limit_minutes' => $quiz->time_limit_minutes,
                    'questions' => $quiz->questions->map(function ($q) {
                        return [
                            'id' => $q->id,
                            'question_text' => $q->question_text,
                            'option_a' => $q->option_a,
                            'option_b' => $q->option_b,
                            'option_c' => $q->option_c,
                            'option_d' => $q->option_d,
                        ];
                    })
                ]
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Quiz tidak ditemukan'
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil soal quiz'
            ], 500);
        }
    }

    /**
     * Ambil quiz lengkap + status pengerjaan user
     */
    public function fullQuiz($quiz_id)
    {
        try {
            $quiz = Quiz::with('questions:id,quiz_id,question_text,option_a,option_b,option_c,option_d')
                        ->findOrFail($quiz_id);

            // cek enrollment user
            if (!auth()->user()->enrollments()->where('course_id', $quiz->course_id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda harus mendaftar course terlebih dahulu'
                ], 403);
            }

            $isCompleted = QuizSubmit::where('user_id', auth()->id())
                                     ->where('quiz_id', $quiz_id)
                                     ->exists();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'description' => $quiz->description,
                    'time_limit_minutes' => $quiz->time_limit_minutes,
                    'is_completed' => $isCompleted,
                    'questions' => $quiz->questions->map(function ($q) {
                        return [
                            'id' => $q->id,
                            'question_text' => $q->question_text,
                            'option_a' => $q->option_a,
                            'option_b' => $q->option_b,
                            'option_c' => $q->option_c,
                            'option_d' => $q->option_d,
                        ];
                    }),
                ]
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Quiz tidak ditemukan'
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil detail quiz'
            ], 500);
        }
    }
}
