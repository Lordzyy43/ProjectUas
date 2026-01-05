<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QuizQuestion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Exception;

class QuizQuestionController extends Controller
{
    /**
     * =========================
     * LIST SOAL QUIZ
     * Bisa diakses PUBLIC / USER
     * =========================
     */
    public function index($quiz_id)
    {
        try {
            $questions = QuizQuestion::where('quiz_id', $quiz_id)->get();

            return response()->json([
                'success' => true,
                'message' => 'Daftar soal quiz berhasil diambil',
                'data' => $questions
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar soal quiz'
            ], 500);
        }
    }

    /**
     * =========================
     * CREATE SOAL QUIZ
     * Hanya ADMIN
     * =========================
     */
    public function store(Request $request, $quiz_id)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        try {
            $data = $request->validate([
                'question_text' => 'required|string',
                'option_a' => 'required|string',
                'option_b' => 'required|string',
                'option_c' => 'nullable|string',
                'option_d' => 'nullable|string',
                'correct_answer' => 'required|in:a,b,c,d'
            ]);

            $answerKey = 'option_' . $data['correct_answer'];
            if (empty($data[$answerKey])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jawaban benar harus sesuai dengan opsi yang tersedia'
                ], 422);
            }

            $data['quiz_id'] = $quiz_id;
            $question = QuizQuestion::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Soal quiz berhasil ditambahkan',
                'data' => $question
            ], 201);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada database saat menyimpan soal'
            ], 500);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses data soal'
            ], 500);
        }
    }

    /**
     * =========================
     * UPDATE SOAL QUIZ
     * Hanya ADMIN
     * =========================
     */
    public function update(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        try {
            $question = QuizQuestion::findOrFail($id);

            $data = $request->validate([
                'quiz_id' => 'sometimes|required|exists:quizzes,id',
                'question_text' => 'sometimes|required|string',
                'option_a' => 'sometimes|required|string',
                'option_b' => 'sometimes|required|string',
                'option_c' => 'nullable|string',
                'option_d' => 'nullable|string',
                'correct_answer' => 'sometimes|required|in:a,b,c,d'
            ]);

            // Merge data lama + baru untuk validasi jawaban
            $merged = array_merge($question->toArray(), $data);

            if (isset($merged['correct_answer'])) {
                $answerKey = 'option_' . $merged['correct_answer'];
                if (empty($merged[$answerKey])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Jawaban benar tidak boleh menunjuk ke opsi kosong'
                    ], 422);
                }
            }

            $question->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Soal quiz berhasil diperbarui',
                'data' => $question
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Soal quiz tidak ditemukan'
            ], 404);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada database saat update soal'
            ], 500);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui soal quiz'
            ], 500);
        }
    }

    /**
     * =========================
     * DELETE SOAL QUIZ
     * Hanya ADMIN
     * =========================
     */
    public function destroy($id)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        try {
            $question = QuizQuestion::findOrFail($id);
            $question->delete();

            return response()->json([
                'success' => true,
                'message' => 'Soal quiz berhasil dihapus'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Soal quiz tidak ditemukan'
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus soal quiz'
            ], 500);
        }
    }
}
