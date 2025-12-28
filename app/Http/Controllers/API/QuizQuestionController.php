<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QuizQuestion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Exception;

class QuizQuestionController extends Controller
{
    public function index($id)
    {
        try {
            $questions = QuizQuestion::where('quiz_id', $id)->get();

            return response()->json([
                'success' => true,
                'message' => 'Daftar soal quiz berhasil diambil',
                'data' => $questions
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal mengambil daftar soal quiz',
                'message' => 'Terjadi kesalahan saat mengambil data soal quiz'
            ], 500);
        }
    }


    public function store(Request $request, $quiz_id)
    {
        try {
            $data = $request->validate([
                'question_text' => 'required|string',
                'option_a' => 'required|string',
                'option_b' => 'required|string',
                'option_c' => 'nullable|string',
                'option_d' => 'nullable|string',
                'correct_answer' => 'required|in:a,b,c,d'
            ]);

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
                'error' => 'Gagal menambahkan soal quiz',
                'message' => $e->getMessage()
            ], 500);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal menambahkan soal quiz',
                'message' => 'Terjadi kesalahan saat memproses data soal'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
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

            $question->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Soal quiz berhasil diperbarui',
                'data' => $question
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Soal quiz tidak ditemukan',
                'message' => 'ID soal quiz tidak valid'
            ], 404);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal memperbarui soal quiz',
                'message' => 'Terjadi kesalahan pada database saat update soal'
            ], 500);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal memperbarui soal quiz',
                'message' => 'Terjadi kesalahan saat memperbarui soal quiz'
            ], 500);
        }
    }

    public function destroy($id)
    {
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
                'error' => 'Soal quiz tidak ditemukan',
                'message' => 'Soal quiz dengan ID tersebut tidak ada'
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal menghapus soal quiz',
                'message' => 'Terjadi kesalahan saat menghapus soal quiz'
            ], 500);
        }
    }
}
