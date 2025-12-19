<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Exception;

class QuizController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Quiz::with(['category', 'course']);

            // filter by category (opsional)
            if ($request->has('category_id')) {
                $query->where('quiz_category_id', $request->category_id);
            }

            // paginate agar tidak terlalu berat saat banyak data
            $quizzes = $query->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Data quiz berhasil diambil',
                'data' => $quizzes
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal mengambil data quiz',
                'message' => 'Terjadi kesalahan saat mengambil data quiz'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $quiz = Quiz::with(['category', 'questions', 'course'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Detail quiz berhasil diambil',
                'data' => $quiz
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Quiz tidak ditemukan',
                'message' => 'ID quiz tidak valid'
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal mengambil detail quiz',
                'message' => 'Terjadi kesalahan saat mengambil detail quiz'
            ], 500);
        }
    }

    // ADMIN
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'course_id'=>'required|exists:courses,id',
                'quiz_category_id'=>'required|exists:quiz_categories,id',
                'title'=>'required|string',
                'description'=>'nullable|string',
                'time_limit_minutes'=>'nullable|integer'
            ]);

            $quiz = Quiz::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Quiz berhasil dibuat',
                'data' => $quiz
            ], 201);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal membuat quiz',
                'message' => 'Terjadi kesalahan pada database saat menyimpan quiz'
            ], 500);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal membuat quiz',
                'message' => 'Periksa kembali data quiz yang dikirim'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $quiz = Quiz::findOrFail($id);

            $data = $request->validate([
                'course_id'=>'sometimes|required|exists:courses,id',
                'quiz_category_id'=>'sometimes|required|exists:quiz_categories,id',
                'title'=>'sometimes|required|string',
                'description'=>'nullable|string',
                'time_limit_minutes'=>'nullable|integer'
            ]);

            $quiz->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Quiz berhasil diperbarui',
                'data' => $quiz
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Quiz tidak ditemukan',
                'message' => 'Tidak dapat memperbarui quiz karena ID tidak valid'
            ], 404);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal memperbarui quiz',
                'message' => 'Terjadi kesalahan pada database saat menyimpan perubahan'
            ], 500);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal memperbarui quiz',
                'message' => 'Periksa kembali data quiz yang dikirim'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $quiz = Quiz::findOrFail($id);
            $quiz->delete();

            return response()->json([
                'success' => true,
                'message' => 'Quiz berhasil dihapus'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Quiz tidak ditemukan',
                'message' => 'Tidak dapat menghapus quiz karena ID tidak valid'
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal menghapus quiz',
                'message' => 'Terjadi kesalahan saat menghapus quiz'
            ], 500);
        }
    }
}
