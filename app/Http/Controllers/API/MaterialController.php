<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Material;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    public function store(Request $request)
    {
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

        return Material::create($data);
    }

    public function update(Request $request, $id)
    {
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
        return $material;
    }

    public function destroy($id)
    {
        $material = Material::findOrFail($id);
        if ($material->image) Storage::disk('public')->delete($material->image);
        $material->delete();
        return response()->json(['message'=>'Material deleted']);
    }
}
