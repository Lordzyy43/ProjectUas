<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Exception;

class QuizController extends Controller
{
    /**
     * =========================
     * LIST QUIZ
     * Bisa diakses PUBLIC / USER
     * Filter by category opsional
     * =========================
     */
    public function index(Request $request)
    {
        try {
            $query = Quiz::with(['category', 'course']);

            // Filter by category (opsional)
            if ($request->has('category_id')) {
                $query->where('quiz_category_id', $request->category_id);
            }

            $quizzes = $query->paginate(10); // pagination agar tidak berat

            return response()->json([
                'success' => true,
                'message' => 'Data quiz berhasil diambil',
                'data' => $quizzes
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data quiz'
            ], 500);
        }
    }

    /**
     * =========================
     * DETAIL QUIZ
     * Bisa diakses PUBLIC / USER
     * =========================
     */
    public function show($id)
    {
        try {
            $quiz = Quiz::with(['category', 'course', 'questions'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Detail quiz berhasil diambil',
                'data' => $quiz
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Quiz tidak ditemukan'
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail quiz'
            ], 500);
        }
    }

    /**
     * =========================
     * CREATE QUIZ
     * Hanya ADMIN
     * =========================
     */
    public function store(Request $request)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        try {
            $data = $request->validate([
                'course_id' => 'required|exists:courses,id',
                'quiz_category_id' => 'required|exists:quiz_categories,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'time_limit_minutes' => 'nullable|integer'
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
                'message' => 'Gagal membuat quiz, terjadi kesalahan pada database'
            ], 500);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Periksa kembali data quiz yang dikirim'
            ], 500);
        }
    }

    /**
     * =========================
     * UPDATE QUIZ
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
            $quiz = Quiz::findOrFail($id);

            $data = $request->validate([
                'course_id' => 'sometimes|required|exists:courses,id',
                'quiz_category_id' => 'sometimes|required|exists:quiz_categories,id',
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'time_limit_minutes' => 'nullable|integer'
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
                'message' => 'Quiz tidak ditemukan'
            ], 404);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada database saat memperbarui quiz'
            ], 500);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Periksa kembali data quiz yang dikirim'
            ], 500);
        }
    }

    /**
     * =========================
     * DELETE QUIZ
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
            $quiz = Quiz::findOrFail($id);
            $quiz->delete();

            return response()->json([
                'success' => true,
                'message' => 'Quiz berhasil dihapus'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Quiz tidak ditemukan'
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus quiz'
            ], 500);
        }
    }
}
