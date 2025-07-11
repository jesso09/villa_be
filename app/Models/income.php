<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class income extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_villa',
        'title',
        'amount',
        'name',
        'nigt_duration',
        'category',
        'desc',
        'created_at',
    ];

    public function pictures(){
        return $this->hasMany(picture::class, 'id_income', 'id');
    }
}