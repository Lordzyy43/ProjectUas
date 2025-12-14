<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\UserQuizResult;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Exception;

class QuizSubmitController extends Controller
{
    public function submit(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'answers' => 'required|array'
            ]);

            $quiz = Quiz::with('questions')->findOrFail($id);

            if ($quiz->questions->count() === 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Quiz tidak memiliki soal',
                    'message' => 'Quiz ini belum memiliki pertanyaan'
                ], 400);
            }

            $answers = $data['answers'];
            $correct = 0;

            foreach ($quiz->questions as $q) {
                if (
                    isset($answers[$q->id]) &&
                    $answers[$q->id] === $q->correct_answer
                ) {
                    $correct++;
                }
            }

            $total = $quiz->questions->count();
            $score = intval(($correct / $total) * 100);

            $result = UserQuizResult::create([
                'user_id' => $request->user()->id,
                'quiz_id' => $quiz->id,
                'score' => $score,
                'answers' => $answers
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quiz berhasil disubmit',
                'data' => [
                    'score' => $score,
                    'correct' => $correct,
                    'total' => $total,
                    'result_id' => $result->id
                ]
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Quiz tidak ditemukan',
                'message' => 'ID quiz tidak valid'
            ], 404);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal menyimpan hasil quiz',
                'message' => 'Terjadi kesalahan pada database saat menyimpan hasil'
            ], 500);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal submit quiz',
                'message' => 'Terjadi kesalahan saat memproses jawaban quiz'
            ], 500);
        }
    }
}
