<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MaterialCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Exception;

class MaterialCategoryController extends Controller
{
    /**
     * =========================
     * LIST SEMUA KATEGORI MATERIAL
     * Bisa diakses semua user (login / guest)
     * =========================
     */
    public function index()
    {
        $categories = MaterialCategory::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * =========================
     * DETAIL KATEGORI MATERIAL
     * Bisa diakses semua user
     * =========================
     */
    public function show($id)
    {
        $category = MaterialCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori material tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    /**
     * =========================
     * CREATE MATERIAL CATEGORY
     * Hanya untuk ADMIN
     * =========================
     */
    public function store(Request $request)
    {
        // Cek admin (Gunakan auth()->user() agar lebih aman)
        if (!auth()->check() || !auth()->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        // Validasi Sederhana: Slug tidak perlu divalidasi karena dibuat otomatis oleh Model
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:material_categories,name'],
            'description' => ['nullable', 'string']
        ]);

        // Kita tidak perlu lagi Str::slug di sini, biarkan Model yang bekerja!
        $category = MaterialCategory::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Kategori material berhasil dibuat',
            'data' => $category
        ], 201);
    }
    /**
     * =========================
     * UPDATE MATERIAL CATEGORY
     * Hanya untuk ADMIN
     * =========================
     */
   public function update(Request $request, $id)
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $category = MaterialCategory::find($id);
        if (!$category) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('material_categories')->ignore($category->id)],
            'description' => ['nullable', 'string']
        ]);

        // Untuk update, jika ingin slug berubah saat nama berubah, tambahkan manual:
        $category->slug = Str::slug($data['name']);
        $category->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil diperbarui',
            'data' => $category
        ]);
    }

    /**
     * =========================
     * DELETE MATERIAL CATEGORY
     * Hanya untuk ADMIN
     * =========================
     */
    public function destroy($id)
    {
        // Cek admin
        if (!Auth::check() || !Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        $category = MaterialCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori material tidak ditemukan'
            ], 404);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori material berhasil dihapus'
        ]);
    }
}
