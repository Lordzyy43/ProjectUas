<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\QuizSubmit;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Exception;

class QuizPlayController extends Controller
{
    public function questions($quizId)
    {
        try {
            $quiz = Quiz::with(['questions:id,quiz_id,question_text,option_a,option_b,option_c,option_d'])
                ->findOrFail($quizId);

            return response()->json([
                'success' => true,
                'data' => [
                    'quiz_id' => $quiz->id,
                    'title' => $quiz->title,
                    'time_limit_minutes' => $quiz->time_limit_minutes,
                    'questions' => $quiz->questions
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal mengambil soal quiz'
            ], 500);
        }
    }

    public function fullQuiz($quiz_id)
    {
        $quiz = Quiz::with('questions')->find($quiz_id);

        if (!$quiz) {
            return response()->json([
                'success' => false,
                'message' => 'Quiz tidak ditemukan',
            ], 404);
        }

        // Cek apakah user sudah mengerjakan quiz
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
            ],
        ]);
    }

}
