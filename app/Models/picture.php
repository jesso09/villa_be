<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class picture extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_villa',
        'id_expense',
        'id_income',
        'generated_name',
        'title',
    ];

    public function expenses(){
        return $this->belongsTo(expense::class, 'id_expense');
    }
    
    public function incomes(){
        return $this->belongsTo(income::class, 'id_income');
    }
    
    public function villas(){
        return $this->belongsTo(villa::class, 'id_villa');
    }

}
