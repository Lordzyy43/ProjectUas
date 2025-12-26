<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class CourseController extends Controller
{
    public function index()
{
        try {
            $courses = Course::with(['creator'])
                ->withCount('materials')
                ->latest()
                ->paginate(12);

            // Tambahkan URL thumbnail
            $courses->getCollection()->transform(function ($course) {
                $course->thumbnail_url = $course->thumbnail
                    ? asset('storage/' . $course->thumbnail)
                    : null;
                return $course;
            });

            return response()->json([
                'success' => true,
                'data' => $courses
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal memuat daftar course',
                'message' => 'Terjadi kesalahan saat mengambil data course'
            ], 500);
        }
    }

    public function select()
        {
            try {
                $courses = Course::select('id', 'title')
                    ->orderBy('title')
                    ->get();

                return response()->json([
                    'success' => true,
                    'data' => $courses
                ]);

            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'error' => 'Gagal mengambil data course'
                ], 500);
            }
        }



    public function show($id)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => Course::with('materials')->findOrFail($id)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Course tidak ditemukan',
                'message' => 'Course dengan ID tersebut tidak tersedia'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal memuat detail course',
                'message' => 'Terjadi kesalahan saat mengambil detail course'
            ], 500);
        }
    }

    public function materials($id)
    {
        try {
            $course = Course::with('materials')->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $course->materials
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Course tidak ditemukan',
                'message' => 'Tidak dapat menampilkan materi karena course tidak tersedia'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal memuat materi course',
                'message' => 'Terjadi kesalahan saat mengambil daftar materi'
            ], 500);
        }
    }

    // ADMIN
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'title'=>'required|string',
                'category'=>'nullable|string',
                'description'=>'nullable|string',
                'thumbnail'=>'nullable|image|max:2048'
            ]);

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = $request->file('thumbnail')
                    ->store('course-thumbnails','public');
            }

            $data['created_by'] = auth()->id();
            Course::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Course berhasil dibuat'
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal membuat course',
                'message' => 'Periksa kembali data input atau konfigurasi database'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $course = Course::findOrFail($id);

            $data = $request->validate([
                'title'=>'nullable|string',
                'category'=>'nullable|string',
                'description'=>'nullable|string',
                'thumbnail'=>'nullable|image|max:2048'
            ]);

            if ($request->hasFile('thumbnail')) {
                if ($course->thumbnail) {
                    Storage::disk('public')->delete($course->thumbnail);
                }
                $data['thumbnail'] = $request->file('thumbnail')
                    ->store('course-thumbnails','public');
            }

            $course->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Course berhasil diperbarui',
                'data' => $course
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Course tidak ditemukan',
                'message' => 'Tidak dapat memperbarui course karena ID tidak valid'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal memperbarui course',
                'message' => 'Terjadi kesalahan saat menyimpan perubahan course'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $course = Course::findOrFail($id);

            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }

            $course->delete();

            return response()->json([
                'success' => true,
                'message' => 'Course berhasil dihapus'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Course tidak ditemukan',
                'message' => 'Tidak dapat menghapus course karena ID tidak valid'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal menghapus course',
                'message' => 'Terjadi kesalahan saat menghapus data course'
            ], 500);
        }
    }
}
