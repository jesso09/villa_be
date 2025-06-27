<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class absent extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_villa',
        'title',
        'date',
    ];

    public function villa(){
        return $this->belongsTo(villa::class, 'id_villa');
    }
}
