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
            return Course::withCount('materials')->paginate(12);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Gagal memuat data course',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            return Course::with('materials')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Course tidak ditemukan'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan'], 500);
        }
    }

    public function materials($id)
    {
        try {
            $course = Course::with('materials')->findOrFail($id);
            return $course->materials;
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Course tidak ditemukan'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan'], 500);
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
                $data['thumbnail'] = $request->file('thumbnail')->store('course-thumbnails','public');
            }
            $data['created_by'] = auth()->id();
            Course::create($data);

            return response()->json(['message'=>'Course created'], 201);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Gagal membuat course',
                'message' => $e->getMessage()
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
                if ($course->thumbnail) Storage::disk('public')->delete($course->thumbnail);
                $data['thumbnail'] = $request->file('thumbnail')->store('course-thumbnails','public');
            }

            $course->update($data);

            return response()->json(['message'=>'Course updated', 'data'=>$course]);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Course tidak ditemukan'], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Gagal update course',
                'message' => $e->getMessage()
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

            return response()->json(['message'=>'Course deleted']);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Course tidak ditemukan'], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Gagal menghapus course',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
