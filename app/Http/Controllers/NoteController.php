<?php

namespace App\Http\Controllers;

use App\Models\note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NoteController extends Controller
{
    public function index($idVilla)
    {
        $noteData = note::where('id_villa', $idVilla)->latest('created_at')->latest()->get();

        if (is_null($noteData)) {
            return response([
                'message' => 'Data not found',
                'data' => $noteData
            ], 404);
        }

        return response([
            'message' => 'Successfully',
            'data' => $noteData
        ], 200);
    }

    public function store(Request $request)
    {
        // Validasi Formulir
        $validator = Validator::make($request->all(), [
            'id_villa' => 'required',
            'title' => 'required',
            'notes_text' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['message' => 'Invalid input data', 'errors' => $validator->errors()], 400);
        }
        $newData = note::create([
            'id_villa' => $request->id_villa,
            'title' => $request->title,
            'notes_text' => $request->notes_text,
        ]);
        if ($newData) {
            return response([
                'message' => 'Data added successfully',
                'data' => $newData
            ], 201);
        }

        return response([
            'message' => 'Failed Add Data',
        ], status: 400);
    }

    public function update(Request $request, $id)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'id_villa' => 'required',
            'title' => 'required',
            'notes_text' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['message' => 'Invalid input', 'errors' => $validator->errors()], 400);
        }

        try {
            $incomeData = note::find($id);

            $oldData = $incomeData;

            if (!$oldData) {
                return response(['message' => 'Data not found'], 404);
            }

            // Jika type tidak berubah, update normal
            $updateFields = ['id_villa', 'title', 'notes_text',];

            foreach ($updateFields as $field) {
                if ($request->has($field)) {
                    $oldData->{$field} = $request->{$field};
                }
            }

            if ($oldData->save()) {
                return response([
                    'message' => 'Data updated successfully',
                    'data' => $oldData
                ], 200);
            }

            return response(['message' => 'Failed to update data'], 500);

        } catch (\Exception $e) {
            return response([
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $targetData = note::find($id);
            $notFoundMessage = 'Data not found';

            if (is_null($targetData)) {
                return response([
                    'message' => $notFoundMessage,
                    'data' => null
                ], 404);
            }

            if ($targetData->delete()) {
                return response([
                    'message' => 'Data deleted successfully',
                    'data' => $targetData
                ], 200);
            }

            return response([
                'message' => 'Failed to delete data',
                'data' => null
            ], 500);

        } catch (\Exception $e) {
            return response([
                'message' => 'An error occurred while deleting data',
                'error' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
