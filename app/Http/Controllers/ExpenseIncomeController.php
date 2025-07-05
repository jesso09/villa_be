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

    public function update(Request $request, $id)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'id_villa' => 'required',
            'title' => 'required',
            'amount' => 'required|numeric',
            'type' => 'required|in:expense,income',
            'picture' => 'nullable|mimes:jpeg,png,jpg,gif|max:50000',
        ], [
            'picture.mimes' => 'Format gambar yang diperbolehkan: jpeg, png, jpg, gif.',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Invalid input data',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Tentukan model berdasarkan type
            $model = $request->type === 'income' ? Income::class : Expense::class;
            $data = $model::with('pictures')->find($id);

            if (!$data) {
                return response([
                    'message' => 'Data not found',
                    'data' => null
                ], 404);
            }

            // Update data utama
            $updateFields = [
                'id_villa',
                'title',
                'amount',
                'category',
                'desc',
                'created_at'
            ];

            if ($request->type === 'income') {
                $updateFields = array_merge($updateFields, ['name', 'nigt_duration']);
            }

            foreach ($updateFields as $field) {
                if ($request->has($field)) {
                    $data->{$field} = $request->{$field};
                }
            }

            // Handle gambar
            if ($request->hasFile('picture')) {
                $file = $request->file('picture');
                $originalName = $file->getClientOriginalName();
                $generatedName = 'activity-' . time() . '.' . $file->extension();

                // Jika sudah ada gambar, update yang sudah ada
                if ($data->pictures->isNotEmpty()) {
                    $picture = $data->pictures->first();

                    // Hapus file lama
                    $oldFilePath = "public/{$request->type}/{$picture->generated_name}";
                    if (Storage::exists($oldFilePath)) {
                        Storage::delete($oldFilePath);
                    }

                    // Update record gambar yang sudah ada
                    $picture->update([
                        'generated_name' => $generatedName,
                        'title' => $originalName,
                        // field lain yang perlu diupdate
                    ]);
                } else {
                    // Jika belum ada gambar, buat baru
                    $data->pictures()->create([
                        'generated_name' => $generatedName,
                        'title' => $originalName,
                    ]);
                }

                // Simpan file baru
                $file->storeAs("public/{$request->type}", $generatedName);
            }

            if ($data->save()) {
                return response([
                    'message' => ucfirst($request->type) . ' updated successfully',
                    'data' => $data
                ], 200);
            }

            return response([
                'message' => 'Failed to update ' . $request->type,
                'data' => null
            ], 500);

        } catch (\Exception $e) {
            return response([
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

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
            switch ($request->type) {
                case 'expense':
                    $targetData = Expense::with('pictures')->find($id);
                    $storagePath = 'public/expense';
                    $notFoundMessage = 'Expense data not found';
                    break;

                case 'income':
                    $targetData = Income::with('pictures')->find($id);
                    $storagePath = 'public/income';
                    $notFoundMessage = 'Income data not found';
                    break;
            }

            if (is_null($targetData)) {
                return response([
                    'message' => $notFoundMessage,
                    'data' => null
                ], 404);
            }

            // Delete associated pictures
            if ($targetData->pictures->isNotEmpty()) {
                foreach ($targetData->pictures as $picture) {
                    $filePath = $storagePath . '/' . $picture->generated_name;
                    if (Storage::exists($filePath)) {
                        Storage::delete($filePath);
                    }
                    $picture->delete();
                }
            }

            if ($targetData->delete()) {
                return response([
                    'message' => ucfirst($request->type) . ' deleted successfully',
                    'data' => $targetData
                ], 200);
            }

            return response([
                'message' => 'Failed to delete ' . $request->type,
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
