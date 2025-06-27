<?php

namespace App\Http\Controllers;

use App\Models\absent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AbsentController extends Controller
{
    public function index($idVilla)
    {
        // $absentData = absent::where('id_villa', $idVilla)->with('villa')->latest('created_at')->latest()->get();
        $absentData = absent::with('villa')->latest('created_at')->latest()->get();

        if (is_null($absentData)) {
            return response([
                'message' => 'Data not found',
                'data' => $absentData
            ], 404);
        }

        return response([
            'message' => 'Successfully',
            'data' => $absentData
        ], 200);
    }

    public function store(Request $request)
    {
        // Validasi Formulir
        $validator = Validator::make($request->all(), [
            'id_villa' => 'required',
            'title' => 'required',
            'date' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['message' => 'Invalid input data', 'errors' => $validator->errors()], 400);
        }
        $newData = absent::create([
            'id_villa' => $request->id_villa,
            'title' => $request->title,
            'date' => $request->date,
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
}