<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class CourseController extends Controller
{
    // =======================
    // USER: Ambil daftar course
    // =======================
    public function index()
    {
        try {
            $courses = Course::with([
                    'creator' => fn($q) => $q->withDefault(),
                    'category' => fn($q) => $q->withDefault()
                ])
                ->withCount('materials')
                ->latest()
                ->paginate(12);

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
            \Log::error('Error fetching courses: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Gagal memuat daftar course',
                'message' => 'Terjadi kesalahan, silakan coba lagi'
            ], 500);
        }
    }

    // =======================
    // USER: Ambil course untuk select/dropdown
    // =======================
    public function select()
    {
        try {
            $courses = Course::select('id', 'title', 'course_category_id')
                ->orderBy('title')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $courses
            ]);

        } catch (Exception $e) {
            \Log::error('Error fetching courses for select: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Gagal mengambil data course'
            ], 500);
        }
    }

    // =======================
    // USER: Detail course
    // =======================
    public function show($id)
    {
        try {
            $course = Course::with(['materials', 'category', 'creator'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $course
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Course tidak ditemukan',
                'message' => 'Course dengan ID tersebut tidak tersedia'
            ], 404);
        } catch (Exception $e) {
            \Log::error('Error fetching course detail: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Gagal memuat detail course',
                'message' => 'Terjadi kesalahan, silakan coba lagi'
            ], 500);
        }
    }

    // =======================
    // USER: Ambil materi course
    // =======================
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
            \Log::error('Error fetching course materials: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Gagal memuat materi course',
                'message' => 'Terjadi kesalahan, silakan coba lagi'
            ], 500);
        }
    }

    // =======================
    // ADMIN: Buat course baru
    // =======================
    public function store(Request $request)
    {
        try {
            // VALIDASI
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'course_category_id' => 'nullable|exists:course_categories,id',
                'description' => 'nullable|string',
                'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ]);

            // HANDLE THUMBNAIL
            if ($request->hasFile('thumbnail')) {
                $path = $request->file('thumbnail')->store('course-thumbnails', 'public');
                $data['thumbnail'] = $path;
            }

            // TAMBAHKAN CREATOR
            $data['created_by'] = auth()->id();

            // CREATE COURSE
            $course = Course::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Course berhasil dibuat',
                'data' => $course->load('category')
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error creating course: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Gagal membuat course',
                'message' => 'Terjadi kesalahan, silakan coba lagi'
            ], 500);
        }
    }


    // =======================
    // ADMIN: Update course
    // =======================
    public function update(Request $request, $id)
    {
        try {
            $course = Course::findOrFail($id);

            $data = $request->validate([
                'title'=>'nullable|string',
                'course_category_id'=>'nullable|exists:course_categories,id',
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
                'data' => $course->load('category')
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Course tidak ditemukan',
                'message' => 'Tidak dapat memperbarui course karena ID tidak valid'
            ], 404);
        } catch (Exception $e) {
            \Log::error('Error updating course: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Gagal memperbarui course',
                'message' => 'Terjadi kesalahan, silakan coba lagi'
            ], 500);
        }
    }

    // =======================
    // ADMIN: Hapus course
    // =======================
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
                'error' => 'Course tidak ditemukan'
            ], 404);
        } catch (Exception $e) {
            \Log::error('Error deleting course: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Gagal menghapus course',
                'message' => 'Terjadi kesalahan, silakan coba lagi'
            ], 500);
        }
    }
}
