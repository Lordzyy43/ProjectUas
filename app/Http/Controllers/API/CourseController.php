<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function index()
    {
        return Course::withCount('materials')->paginate(12);
    }

    public function show($id)
    {
        return Course::with('materials')->findOrFail($id);
    }

    public function materials($id)
    {
        $course = Course::with('materials')->findOrFail($id);
        return $course->materials;
    }

    // ADMIN
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'=>'required|string',
            'category'=>'nullable|string',
            'description'=>'nullable|string',
            'thumbnail'=>'nullable|image|max:2048'
        ]);

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('course-thumbnails','public');
        }

        return Course::create($data);
    }

    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $data = $request->validate([
            'title'=>'sometimes|required|string',
            'category'=>'nullable|string',
            'description'=>'nullable|string',
            'thumbnail'=>'nullable|image|max:2048'
        ]);

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail) Storage::disk('public')->delete($course->thumbnail);
            $data['thumbnail'] = $request->file('thumbnail')->store('course-thumbnails','public');
        }

        $course->update($data);
        return $course;
    }

    public function destroy($id)
    {
        $course = Course::findOrFail($id);

        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }

        $course->delete();

        return response()->json(['message'=>'Course deleted']);
    }
}
