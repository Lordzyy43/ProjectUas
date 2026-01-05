<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CourseCategory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Exception;

class CourseCategoryController extends Controller
{
    /**
     * =========================
     * LIST COURSE CATEGORIES
     * Bisa diakses semua user (login / guest)
     * =========================
     */
    public function index()
    {
        $categories = CourseCategory::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * =========================
     * DETAIL COURSE CATEGORY
     * Bisa diakses semua user
     * =========================
     */
    public function show($id)
    {
        $category = CourseCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    /**
     * =========================
     * CREATE COURSE CATEGORY
     * Hanya untuk ADMIN
     * =========================
     */
    public function store(Request $request)
    {
        // Validasi input
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:course_categories,name'],
            'description' => ['nullable', 'string']
        ]);

        // Auto-generate slug
        $data['slug'] = Str::slug($data['name']);

        $category = CourseCategory::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Category berhasil dibuat',
            'data' => $category
        ], 201);
    }

    /**
     * =========================
     * UPDATE COURSE CATEGORY
     * Hanya untuk ADMIN
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

        $category = CourseCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category tidak ditemukan'
            ], 404);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('course_categories')->ignore($category->id)],
            'description' => ['nullable', 'string']
        ]);

        $data['slug'] = Str::slug($data['name']);

        $category->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Category berhasil diperbarui',
            'data' => $category
        ]);
    }

    /**
     * =========================
     * DELETE COURSE CATEGORY
     * Hanya untuk ADMIN
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

        $category = CourseCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category tidak ditemukan'
            ], 404);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category berhasil dihapus'
        ]);
    }
}
