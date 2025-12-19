<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Quiz;
use App\Models\UserQuizResult;
use Exception;

class QuizSubmitController extends Controller
{
    public function submit(Request $request, $quizId)
    {
        try {
            $user = Auth::user();

            // pastikan quiz ada + ambil soal
            $quiz = Quiz::with('questions')->findOrFail($quizId);

            // cegah submit ulang
            $alreadySubmitted = UserQuizResult::where('user_id', $user->id)
                ->where('quiz_id', $quizId)
                ->exists();

            if ($alreadySubmitted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz sudah pernah dikerjakan'
                ], 400);
            }

            // validasi input
            $data = $request->validate([
                'answers' => 'required|array'
            ]);

            $correct = 0;
            $total = $quiz->questions->count();

            foreach ($quiz->questions as $question) {
                if (
                    isset($data['answers'][$question->id]) &&
                    $data['answers'][$question->id] === $question->correct_option
                ) {
                    $correct++;
                }
            }

            // hitung skor (0 - 100)
            $score = $total > 0 ? intval(($correct / $total) * 100) : 0;

            // simpan hasil (SATU CARA, JELAS)
            $result = UserQuizResult::create([
                'user_id' => $user->id,
                'quiz_id' => $quizId,
                'score' => $score,
                'correct_count' => $correct,
                'total_questions' => $total
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quiz berhasil disubmit',
                'data' => [
                    'result_id' => $result->id,
                    'total_questions' => $total,
                    'correct_answers' => $correct,
                    'score' => $score
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal submit quiz'
            ], 500);
        }
    }
}
