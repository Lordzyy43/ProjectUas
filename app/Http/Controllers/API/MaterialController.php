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
        if (!Auth::check() || !Auth::user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        try {
            $validatedData = $request->validate([
                'course_id'            => 'required',
                'material_category_id' => 'nullable',
                'title'                => 'required|string',
                'content'              => 'nullable|string',
                'order'                => 'nullable',
            ]);

            // Tambahkan pengecekan tambahan untuk debugging
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                
                // Nama file unik
                $fileName = time() . '_' . $file->getClientOriginalName();
                
                // Pastikan folder tujuan ada
                $targetDir = public_path('storage/materials');
                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                // Pindahkan file
                $file->move($targetDir, $fileName);
                
                // Isi path ke validatedData agar masuk ke DB
                $validatedData['image'] = 'materials/' . $fileName;
            }

            $material = Material::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Material berhasil ditambahkan!',
                'data' => $material->load('category') // Load agar UI langsung terupdate rapi
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Server Error',
                'message' => $e->getMessage()
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

            // Validasi teks (image dipisah dulu agar tidak kena error validator image bawaan jika MIME bermasalah)
            $request->validate([
                'course_id'            => 'required|exists:courses,id',
                'material_category_id' => 'nullable|exists:material_categories,id',
                'title'                => 'required|string',
                'content'              => 'nullable|string',
                'order'                => 'nullable|integer',
            ]);

            // Siapkan data untuk diupdate
            $updateData = [
                'course_id'            => $request->course_id,
                'material_category_id' => $request->material_category_id,
                'title'                => $request->title,
                'content'              => $request->content,
                'order'                => $request->order,
            ];

            // LOGIKA UPDATE IMAGE
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                
                if ($file->isValid()) {
                    // 1. Hapus image lama dari folder
                    if ($material->image && Storage::disk('public')->exists($material->image)) {
                        Storage::disk('public')->delete($material->image);
                    }

                    // 2. Simpan image baru
                    // Gunakan store() agar konsisten dengan create
                    $path = $file->store('materials', 'public');
                    $updateData['image'] = $path;
                }
            }

            $material->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Material berhasil diperbarui',
                'data' => $material->load('category') // Load kategori agar data terbaru ikut terkirim
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'error' => 'Material tidak ditemukan'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Gagal memperbarui material',
                'message' => $e->getMessage()
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
