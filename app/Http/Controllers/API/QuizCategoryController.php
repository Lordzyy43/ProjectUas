<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\QuizCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class QuizCategoryController extends Controller
{
    /**
     * Ambil semua kategori quiz (PUBLIC / USER)
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => QuizCategory::orderBy('name')->get()
        ]);
    }

    /**
     * Tambah kategori quiz (ADMIN)
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:100|unique:quiz_categories,name',
                'description' => 'nullable|string'
            ]);

            $data['slug'] = Str::slug($data['name']);

            $category = QuizCategory::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Kategori quiz berhasil dibuat',
                'data' => $category
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat kategori quiz'
            ], 500);
        }
    }

    /**
     * Update kategori quiz (ADMIN)
     */
    public function update(Request $request, $id)
    {
        try {
            $category = QuizCategory::findOrFail($id);

            $data = $request->validate([
                'name' => 'required|string|max:100|unique:quiz_categories,name,' . $id,
                'description' => 'nullable|string'
            ]);

            $data['slug'] = Str::slug($data['name']);

            $category->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Kategori quiz berhasil diperbarui',
                'data' => $category
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori quiz tidak ditemukan'
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui kategori quiz'
            ], 500);
        }
    }

    /**
     * Hapus kategori quiz (ADMIN)
     */
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
                'message' => 'Kategori quiz tidak ditemukan'
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kategori quiz'
            ], 500);
        }
    }
}
