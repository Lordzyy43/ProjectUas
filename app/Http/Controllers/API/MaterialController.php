<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\Material;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Exception;

class MaterialController extends Controller
{
    /**
     * Ambil material berdasarkan course (user harus sudah enroll)
     */
    public function byCourse($course_id)
    {
        try {
            // ambil user login (AMAN untuk IDE & Laravel)
            $user = Auth::user();

            // 1. Pastikan course ada
            $course = Course::findOrFail($course_id);

            // 2. Pastikan user sudah enroll
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

            // 3. Ambil material
            $materials = $course->materials()
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

    /**
     * Tambah material (ADMIN)
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'course_id' => 'required|exists:courses,id',
                'title'     => 'required|string',
                'content'   => 'nullable|string',
                'image'     => 'nullable|image|max:2048',
                'order'     => 'nullable|integer'
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
     * Ambil material (ADMIN)
     */
    public function index()
    {
        try {
            $materials = Material::orderBy('created_at', 'desc')->get();

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
     * Update material (ADMIN)
     */
    public function update(Request $request, $id)
    {
        try {
            $material = Material::findOrFail($id);

            $data = $request->validate([
                'course_id' => 'required|exists:courses,id',
                'title'   => 'nullable|string',
                'content' => 'nullable|string',
                'image'   => 'nullable|image|max:2048',
                'order'   => 'nullable|integer',
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
     * Hapus material (ADMIN)
     */
    public function destroy($id)
    {
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
