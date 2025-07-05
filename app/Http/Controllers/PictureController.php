<?php

namespace App\Http\Controllers;

use App\Models\picture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class PictureController extends Controller
{
    public function destroy(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'type' => 'required|in:expense,income',
    ]);

    if ($validator->fails()) {
        return response([
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
            'data' => null
        ], 400);
    }

    try {
        $targetData = Picture::find($id);
        
        switch ($request->type) {
            case 'expense':
                $storagePath = 'public/expense';
                $notFoundMessage = 'Expense pict not found';
                break;
            case 'income':
                $storagePath = 'public/income';
                $notFoundMessage = 'Income pict not found';
                break;
        }

        if (is_null($targetData)) {
            return response([
                'message' => $notFoundMessage,
                'data' => null
            ], 404);
        }

        // Hapus file fisik
        $filePath = $storagePath . '/' . $targetData->generated_name;
        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
        }

        // Hapus record dari database
        if ($targetData->delete()) {  // Hanya panggil delete() sekali
            return response([
                'message' => ucfirst($request->type) . ' picture deleted successfully',
                'data' => $targetData
            ], 200);
        }

        return response([
            'message' => 'Failed to delete ' . $request->type . ' picture',
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
