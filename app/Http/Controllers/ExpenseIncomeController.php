<?php

namespace App\Http\Controllers;
use App\Models\expense;
use App\Models\income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ExpenseIncomeController extends Controller
{
    public function expenseIncomeActivity($idVilla)
    {
        $incomeData = income::where('id_villa', $idVilla)
            ->with('pictures')
            ->latest('created_at')
            ->get()
            ->map(function ($item) {
                $item->type = 'income';
                return $item;
            });

        $expenseData = expense::where('id_villa', $idVilla)
            ->with('pictures')
            ->latest('created_at')
            ->get()
            ->map(function ($item) {
                $item->type = 'expense';
                return $item;
            });

        // Gabungkan dan urutkan berdasarkan created_at descending
        $combined = $incomeData->collect()->merge($expenseData)->sortByDesc('created_at')->values();

        return response([
            'message' => 'Successfully',
            'data' => $combined
        ], 200);
    }


    public function indexIncome($idVilla)
    {
        $incomeData = income::where('id_villa', $idVilla)->latest('created_at')->get();

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

    public function indexExpense($idVilla)
    {
        $expenseData = expense::where('id_villa', $idVilla)->latest('created_at')->get();

        if (is_null($expenseData)) {
            return response([
                'message' => 'Idea not found',
                'data' => $expenseData
            ], 404);
        }

        return response([
            'message' => 'Successfully',
            'data' => $expenseData
        ], 200);
    }

    public function store(Request $request)
    {
        // Validasi Formulir
        $validator = Validator::make($request->all(), [
            'id_villa' => 'required',
            'title' => 'required',
            'amount' => 'required',
            'type' => 'required',
            // 'category' => 'required',
            // 'desc' => 'required',
            'picture' => 'mimes:jpeg,png,jpg,gif|max:50000',
        ], [
            'picture.mimes' => 'Format gambar yang diperbolehkan: jpeg, png, jpg, gif.',
        ]);

        if ($validator->fails()) {
            return response(['message' => 'Invalid input data', 'errors' => $validator->errors()], 400);
        }

        switch ($request->type) {
            case 'expense':
                $newData = expense::create([
                    'id_villa' => $request->id_villa,
                    'title' => $request->title,
                    'amount' => $request->amount,
                    'category' => $request->category,
                    'desc' => $request->desc,
                    'created_at' => $request->created_at,
                ]);
                if ($request->picture != null) {
                    $originalName = $request->picture->getClientOriginalName();
                    $generatedName = 'activity' . '-' . time() . '.' . $request->picture->extension();

                    // menyimpan gambar
                    $request->picture->storeAs('public/expense', $generatedName);
                    $newData->pictures()->create([
                        'generated_name' => $generatedName,
                        'title' => $originalName,
                        // tambahkan field lain yang diperlukan untuk picture
                    ]);
                }
                return response([
                    'message' => 'Data added successfully',
                    'data' => $newData
                ], 201);
            case 'income':
                $newData = income::create([
                    'id_villa' => $request->id_villa,
                    'title' => $request->title,
                    'amount' => $request->amount,
                    'name' => $request->name,
                    'nigt_duration' => $request->nigt_duration,
                    'category' => $request->category,
                    'desc' => $request->desc,
                    'created_at' => $request->created_at,
                ]);
                if ($request->picture != null) {
                    $originalName = $request->picture->getClientOriginalName();
                    $generatedName = 'activity' . '-' . time() . '.' . $request->picture->extension();

                    // menyimpan gambar
                    $request->picture->storeAs('public/income', $generatedName);
                    $newData->pictures()->create([
                        'generated_name' => $generatedName,
                        'title' => $originalName,
                        // tambahkan field lain yang diperlukan untuk picture
                    ]);
                }
                return response([
                    'message' => 'Data added successfully',
                    'data' => $newData
                ], 201);
            default:
                return response([
                    'message' => 'Failed Add Data',
                ], status: 400);
        }
    }

    // public function destroy($id)
    // {
    //     $ideaData = Idea::find($id);

    //     if (is_null($ideaData)) {
    //         return response([
    //             'message' => 'Data not found',
    //             'data' => null
    //         ], 404);
    //     }

    //     if ($ideaData->delete()) {
    //         return response([
    //             'message' => 'Successfully delete data',
    //             'data' => $ideaData
    //         ], 200);
    //     }

    //     return response([
    //         'message' => 'Failed to delete data',
    //         'data' => null
    //     ], 400);
    // }

    // public function update(Request $request, $id)
    // {
    //     $id_villa = Auth::user()->id;
    //     $ideaData = Idea::find($id);

    //     if (is_null($ideaData)) {
    //         return response([
    //             'message' => 'Data not found',
    //             'data' => null
    //         ], 404);
    //     }

    //     $update = $request->all();
    //     $validator = Validator::make($update, [
    //         // 'id_user' => 'required',
    //         'title' => 'required',
    //         // 'description' => 'required',
    //         'tgl_pelaksanaan' => 'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return response(['message' => $validator->errors()], 400);
    //     }

    //     $ideaData->id_user = $id_user;
    //     $ideaData->title = $update['title'];
    //     $ideaData->description = $update['description'];
    //     $ideaData->tgl_pelaksanaan = $update['tgl_pelaksanaan'];

    //     if ($ideaData->save()) {
    //         return response([
    //             'message' => 'Data Updated Success',
    //             'data' => $ideaData
    //         ], 200);
    //     }

    //     return response([
    //         'message' => 'Failed to update data',
    //         'data' => null
    //     ], 400);
    // }
}
