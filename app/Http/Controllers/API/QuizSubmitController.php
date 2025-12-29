<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Quiz;
use App\Models\UserQuizAnswer;
use App\Models\UserQuizResult;
use Exception;

class QuizSubmitController extends Controller
{

    public function submit(Request $request, $quizId)
        {
            DB::beginTransaction();

            try {
                $user = Auth::user();

                $quiz = Quiz::with('questions')->findOrFail($quizId);

                if (UserQuizResult::where('user_id', $user->id)->where('quiz_id', $quizId)->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Quiz sudah pernah dikerjakan'
                    ], 400);
                }

                $data = $request->validate([
                    'answers' => 'required|array|min:1'
                ]);

                $correct = 0;
                $total = $quiz->questions->count();

                foreach ($quiz->questions as $question) {

                    if (!isset($data['answers'][$question->id])) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Semua soal wajib dijawab'
                        ], 422);
                    }

                    $selected = $data['answers'][$question->id];

                    if (!in_array($selected, ['a','b','c','d'])) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Jawaban tidak valid'
                        ], 422);
                    }

                    $isCorrect = $selected === $question->correct_answer;

                    if ($isCorrect) $correct++;

                    UserQuizAnswer::create([
                        'user_id' => $user->id,
                        'quiz_id' => $quizId,
                        'question_id' => $question->id,
                        'selected_answer' => $selected,
                        'is_correct' => $isCorrect
                    ]);
                }

                $score = intval(($correct / $total) * 100);

                UserQuizResult::create([
                    'user_id' => $user->id,
                    'quiz_id' => $quizId,
                    'score' => $score,
                    'correct_count' => $correct,
                    'total_questions' => $total
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Quiz berhasil disubmit',
                    'data' => [
                        'total_questions' => $total,
                        'correct_answers' => $correct,
                        'score' => $score
                    ]
                ]);

            } catch (Exception $e) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'error' => 'Gagal submit quiz'
                ], 500);
            }
        }
    }