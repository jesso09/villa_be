<?php

namespace App\Http\Controllers;

use App\Models\schedule;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index($idVilla)
    {
        $scheduleData = schedule::where('id_villa', $idVilla)->get();

        if (is_null($scheduleData)) {
            return response([
                'message' => 'Data not found',
                'data' => $scheduleData
            ], 404);
        }

        return response([
            'message' => 'Successfully',
            'data' => $scheduleData
        ], 200);
    }
}
