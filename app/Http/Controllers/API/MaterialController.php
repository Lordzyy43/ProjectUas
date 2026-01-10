<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\Material;
use App\Models\MaterialCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Exception;

class MaterialController extends Controller
{
    /**
     * =========================
     * AMBIL MATERIAL BERDASARKAN COURSE
     * USER harus sudah enroll di course
     * =========================
     */
    public function byCourse($course_id)
    {
        try {
            $user = Auth::user(); // user login

            // Pastikan course ada
            $course = Course::findOrFail($course_id);

            // Pastikan user sudah enroll
            $isEnrolled = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course_id)
                ->exists();

            if (!$isEnrolled) {
                return response()->json([
                    'success' => false,
                    'error' => 'Akses ditolak',
                    'message' => 'Anda belum terdaftar pada course ini'
                ], 403);
            }

            // Ambil material, join dengan kategori
            $materials = $course->materials()
                ->with('category') // include material category
                ->orderBy('order')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $materials
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Course tidak ditemukan'
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan',
                'message' => 'Gagal mengambil data material'
            ], 500);
        }
    }

    public function show($id)
{
    try {
        $material = Material::with(['course', 'category'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $material
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Materi tidak ditemukan'], 404);
    }
}

    /**
     * =========================
     * AMBIL SEMUA MATERIAL (ADMIN)
     * Bisa untuk listing materi semua course
     * =========================
     */
    public function index()
    {
        try {
            $materials = Material::with('category', 'course')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $materials
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal mengambil data material',
                'message' => 'Terjadi kesalahan pada server'
            ], 500);
        }
    }

    /**
     * =========================
     * TAMBAH MATERIAL (ADMIN)
     * =========================
     */
    public function store(Request $request)
    {
        // Cek admin
        if (!Auth::check() || !Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        try {
            $data = $request->validate([
                'course_id'         => 'required|exists:courses,id',
                'material_category_id' => 'nullable|exists:material_categories,id', // baru
                'title'             => 'required|string',
                'content'           => 'nullable|string',
                'image'             => 'nullable|image|max:2048',
                'order'             => 'nullable|integer'
            ]);

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')
                    ->store('materials', 'public');
            }

            $material = Material::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Material berhasil ditambahkan',
                'data' => $material
            ], 201);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal menambahkan material',
                'message' => 'Terjadi kesalahan pada database'
            ], 500);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal menambahkan material',
                'message' => 'Periksa kembali data input'
            ], 500);
        }
    }

    /**
     * =========================
     * UPDATE MATERIAL (ADMIN)
     * =========================
     */
    public function update(Request $request, $id)
    {
        // Cek admin
        if (!Auth::check() || !Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        try {
            $material = Material::findOrFail($id);

            $data = $request->validate([
                'course_id'             => 'required|exists:courses,id',
                'material_category_id'  => 'nullable|exists:material_categories,id',
                'title'                 => 'nullable|string',
                'content'               => 'nullable|string',
                'image'                 => 'nullable|image|max:2048',
                'order'                 => 'nullable|integer',
            ]);

            if ($request->hasFile('image')) {
                if ($material->image) {
                    Storage::disk('public')->delete($material->image);
                }
                $data['image'] = $request->file('image')
                    ->store('materials', 'public');
            }

            $material->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Material berhasil diperbarui',
                'data' => $material
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Material tidak ditemukan'
            ], 404);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal memperbarui material',
                'message' => 'Terjadi kesalahan pada database'
            ], 500);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal memperbarui material'
            ], 500);
        }
    }

    /**
     * =========================
     * HAPUS MATERIAL (ADMIN)
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

        try {
            $material = Material::findOrFail($id);

            if ($material->image) {
                Storage::disk('public')->delete($material->image);
            }

            $material->delete();

            return response()->json([
                'success' => true,
                'message' => 'Material berhasil dihapus'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Material tidak ditemukan'
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal menghapus material'
            ], 500);
        }
    }
}
