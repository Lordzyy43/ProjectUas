<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Material;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Exception;

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
                $data['image'] = $request->file('image')
                    ->store('materials','public');
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
                'message' => 'Terjadi kesalahan pada database saat menyimpan material'
            ], 500);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal menambahkan material',
                'message' => 'Periksa kembali data input material'
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
                if ($material->image) {
                    Storage::disk('public')->delete($material->image);
                }
                $data['image'] = $request->file('image')
                    ->store('materials','public');
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
                'error' => 'Material tidak ditemukan',
                'message' => 'Tidak dapat memperbarui material karena ID tidak valid'
            ], 404);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal memperbarui material',
                'message' => 'Terjadi kesalahan pada database saat menyimpan perubahan'
            ], 500);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal memperbarui material',
                'message' => 'Periksa kembali data yang dikirim'
            ], 500);
        }
    }

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
                'error' => 'Material tidak ditemukan',
                'message' => 'Tidak dapat menghapus material karena ID tidak valid'
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal menghapus material',
                'message' => 'Terjadi kesalahan saat menghapus data material'
            ], 500);
        }
    }
}
