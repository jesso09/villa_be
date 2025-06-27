<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class expense extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_villa',
        'title',
        'amount',
        'category',
        'desc',
    ];

    public function pictures(){
        return $this->hasMany(picture::class, 'id_expense', 'id');
    }
}