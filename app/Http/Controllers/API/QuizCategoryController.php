<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\QuizCategory;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Exception;

class QuizCategoryController extends Controller
{
    // PUBLIC
    public function index()
    {
        try {
            $categories = QuizCategory::all();

            return response()->json([
                'success' => true,
                'message' => 'Data kategori quiz berhasil diambil',
                'data' => $categories
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal mengambil data kategori quiz',
                'message' => 'Terjadi kesalahan saat mengambil data kategori'
            ], 500);
        }
    }

    // ADMIN
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string',
                'description' => 'nullable|string'
            ]);

            $category = QuizCategory::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Kategori quiz berhasil dibuat',
                'data' => $category
            ], 201);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal membuat kategori quiz',
                'message' => 'Terjadi kesalahan pada database saat menyimpan kategori'
            ], 500);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal membuat kategori quiz',
                'message' => 'Terjadi kesalahan saat membuat kategori quiz'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $category = QuizCategory::findOrFail($id);

            $data = $request->validate([
                'name' => 'sometimes|required|string',
                'description' => 'nullable|string'
            ]);

            $category->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Kategori quiz berhasil diperbarui',
                'data' => $category
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Kategori quiz tidak ditemukan',
                'message' => 'ID kategori quiz tidak valid'
            ], 404);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal memperbarui kategori quiz',
                'message' => 'Terjadi kesalahan pada database saat update kategori'
            ], 500);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal memperbarui kategori quiz',
                'message' => 'Terjadi kesalahan saat memperbarui kategori quiz'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $category = QuizCategory::findOrFail($id);
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kategori quiz berhasil dihapus'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Kategori quiz tidak ditemukan',
                'message' => 'Kategori quiz dengan ID tersebut tidak ada'
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal menghapus kategori quiz',
                'message' => 'Terjadi kesalahan saat menghapus kategori quiz'
            ], 500);
        }
    }
}
