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
     * ============================
     * SUBMIT QUIZ
     * Endpoint: POST /api/quiz/{quizId}/submit
     * ============================
     */
    public function submit(Request $request, $quizId)
    {
        // Mulai transaksi database
        DB::beginTransaction();

        try {
            // ============================
            // AMBIL USER YANG LOGIN
            // ============================
            $user = Auth::user();

            // ============================
            // AMBIL QUIZ BESERTA PERTANYAAN
            // ============================
            $quiz = Quiz::with('questions')->findOrFail($quizId);

            // ============================
            // CEGAH SUBMIT ULANG
            // ============================
            if (UserQuizResult::where('user_id', $user->id)
                ->where('quiz_id', $quizId)
                ->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz sudah pernah dikerjakan'
                ], 409);
            }

            // ============================
            // VALIDASI PAYLOAD DARI FRONTEND
            // ============================
            $data = $request->validate([
                'answers' => 'required|array|min:1',
                'answers.*.question_id' => 'required|exists:quiz_questions,id',
                'answers.*.answer' => 'required|in:A,B,C,D', // sesuai pilihan di frontend
            ]);

            // ============================
            // UBAH ARRAY MENJADI KEY BY question_id
            // Supaya mudah diakses
            // ============================
            $answers = collect($data['answers'])->keyBy('question_id');

            $correct = 0;
            $total = $quiz->questions->count();

            // ============================
            // LOOPING SETIAP PERTANYAAN
            // ============================
            foreach ($quiz->questions as $question) {

                // Pastikan semua soal dijawab
                if (!$answers->has($question->id)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Semua soal wajib dijawab'
                    ], 422);
                }

                // Ambil jawaban user & validasi
                $selected = strtoupper($answers[$question->id]['answer']);
                $isCorrect = $selected === $question->correct_answer;

                // Hitung jawaban benar
                if ($isCorrect) {
                    $correct++;
                }

                // Simpan jawaban user
                UserQuizAnswer::create([
                    'user_id' => $user->id,
                    'quiz_id' => $quizId,
                    'question_id' => $question->id,
                    'selected_answer' => $selected,
                    'is_correct' => $isCorrect
                ]);
            }

            // ============================
            // HITUNG SKOR
            // ============================
            $score = (int) round(($correct / $total) * 100);

            // Simpan hasil quiz
            UserQuizResult::create([
                'user_id' => $user->id,
                'quiz_id' => $quizId,
                'score' => $score,
                'correct_count' => $correct,
                'total_questions' => $total
            ]);

            // Commit transaksi
            DB::commit();

            // ============================
            // RETURN RESPONSE SUKSES
            // ============================
            return response()->json([
                'success' => true,
                'message' => 'Quiz berhasil disubmit',
                'data' => [
                    'total_questions' => $total,
                    'correct_answers' => $correct,
                    'score' => $score
                ]
            ], 200);

        } catch (Exception $e) {
            // Rollback transaksi jika ada error
            DB::rollBack();

            // Return error
            return response()->json([
                'success' => false,
                'message' => 'Gagal submit quiz',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
