<?php

namespace App\Http\Controllers;

use App\Models\expense;
use App\Models\income;
use Illuminate\Http\Request;

class PDFController extends Controller
{
    public function getPDFdata($idVilla)
    {
        $currentMonth = now()->month; // Mendapatkan bulan saat ini (1-12)
        $currentYear = now()->year;   // Mendapatkan tahun saat ini

        $incomeData = income::where('id_villa', $idVilla)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->with('pictures')
            ->latest('created_at')
            ->get()
            ->map(function ($item) {
                $item->type = 'income';
                return $item;
            });

        $expenseData = expense::where('id_villa', $idVilla)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->with('pictures')
            ->latest('created_at')
            ->get()
            ->map(function ($item) {
                $item->type = 'expense';
                return $item;
            });


        $totalIncome = 0;
        $totalExpense = 0;

        foreach ($incomeData as $key => $value) {
            // Konversi string ke float, hapus karakter non-numerik jika perlu
            $amount = (float) str_replace(['.', ',', 'Rp', ' '], '', $value->amount);
            $totalIncome += $amount;
        }

        foreach ($expenseData as $key => $value) {
            // Konversi string ke float, hapus karakter non-numerik jika perlu
            $amount = (float) str_replace(['.', ',', 'Rp', ' '], '', $value->amount);
            $totalExpense += $amount;
        }

        // Gabungkan dan urutkan berdasarkan created_at descending
        $combined = $incomeData->collect()
            ->merge($expenseData)
            ->sortByDesc('created_at')
            ->values()
            ->toArray(); // Konversi ke array

        // Tambahkan total sebagai elemen terakhir
        $combined[] = [
            'type' => 'summary',
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_balance' => $totalIncome - $totalExpense
        ];
        return response([
            'message' => 'Successfully',
            'data' => $combined
        ], 200);
    }
}
