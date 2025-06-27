<?php

namespace App\Http\Controllers;

use App\Models\villa;
use Illuminate\Http\Request;

class VillaController extends Controller
{
    public function index()
    {
        $incomeData = villa::latest('created_at')->get();

        if (is_null($incomeData)) {
            return response([
                'message' => 'Idea not found',
                'data' => $incomeData
            ], 404);
        }

        return response([
            'message' => 'Successfully',
            'data' => $incomeData
        ], 200);
    }
}
