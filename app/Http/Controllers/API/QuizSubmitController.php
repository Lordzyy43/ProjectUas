<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Quiz;
use App\Models\UserQuizAnswer;
use App\Models\UserQuizResult;
use Exception;

class QuizSubmitController extends Controller
{
    /**
     * Submit quiz oleh user
     */
    public function submit(Request $request, $quizId)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();
            $quiz = Quiz::with('questions')->findOrFail($quizId);

            // cek enrollment
            if (!$user->enrollments()->where('course_id', $quiz->course_id)->exists()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Anda harus mendaftar course terlebih dahulu'
                ], 403);
            }

            $questions = $quiz->questions;
            if ($questions->count() === 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz belum memiliki soal'
                ], 422);
            }

            $lastAttempt = UserQuizResult::where('user_id', $user->id)
                                         ->where('quiz_id', $quizId)
                                         ->max('attempt');

            $attempt = ($lastAttempt ?? 0) + 1;

            $data = $request->validate([
                'answers' => 'required|array|min:1',
                'answers.*.question_id' => 'required|exists:quiz_questions,id',
                'answers.*.answer' => 'required|in:a,b,c,d',
            ]);

            $answers = collect($data['answers'])->keyBy('question_id');
            $correctCount = 0;

            foreach ($questions as $question) {
                if (!$answers->has($question->id)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Semua soal wajib dijawab'
                    ], 422);
                }

                $selectedAnswer = strtolower($answers[$question->id]['answer']);
                $isCorrect = $selectedAnswer === $question->correct_answer;

                if ($isCorrect) $correctCount++;

                UserQuizAnswer::create([
                    'user_id' => $user->id,
                    'quiz_id' => $quizId,
                    'attempt' => $attempt,
                    'question_id' => $question->id,
                    'selected_answer' => $selectedAnswer,
                    'is_correct' => $isCorrect
                ]);
            }

            $score = (int) round(($correctCount / $questions->count()) * 100);

            UserQuizResult::create([
                'user_id' => $user->id,
                'quiz_id' => $quizId,
                'attempt' => $attempt,
                'score' => $score,
                'correct_count' => $correctCount,
                'total_questions' => $questions->count()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Quiz berhasil disubmit',
                'data' => [
                    'attempt' => $attempt,
                    'total_questions' => $questions->count(),
                    'correct_answers' => $correctCount,
                    'score' => $score
                ]
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal submit quiz',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detail hasil quiz terakhir
     */
    public function detailResult($quizId)
    {
        try {
            $user = Auth::user();

            $result = UserQuizResult::with('quiz')
                ->where('user_id', $user->id)
                ->where('quiz_id', $quizId)
                ->orderByDesc('attempt')
                ->first();

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hasil quiz tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil hasil quiz'
            ], 500);
        }
    }
}
