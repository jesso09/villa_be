<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class villa extends Model
{
    use HasFactory;

    public function pictures(){
        return $this->hasMany(picture::class, 'id_villa', 'id');
    }
}
