<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Material;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;

class MaterialController extends Controller
{
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'course_id'=>'required|exists:courses,id',
                'title'=>'required|string',
                'content'=>'nullable|string',
                'image'=>'nullable|image|max:2048',
                'order'=>'nullable|integer'
            ]);

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('materials','public');
            }

            $material = Material::create($data);
            return response()->json(['message' => 'Material created', 'data' => $material], 201);

        } catch (QueryException $e) {
            // Menangkap error database, misal field required tidak ada
            return response()->json([
                'error' => 'Gagal membuat material',
                'message' => $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            // Menangkap error umum lainnya
            return response()->json([
                'error' => 'Terjadi kesalahan',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $material = Material::findOrFail($id);

            $data = $request->validate([
                'title'=>'nullable|string',
                'content'=>'nullable|string',
                'image'=>'nullable|image|max:2048',
                'order'=>'nullable|integer',
            ]);

            if ($request->hasFile('image')) {
                if ($material->image) Storage::disk('public')->delete($material->image);
                $data['image'] = $request->file('image')->store('materials','public');
            }

            $material->update($data);
            return response()->json(['message' => 'Material updated', 'data' => $material]);

        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Gagal memperbarui material',
                'message' => $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $material = Material::findOrFail($id);
            if ($material->image) Storage::disk('public')->delete($material->image);
            $material->delete();
            return response()->json(['message'=>'Material deleted']);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal menghapus material',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
