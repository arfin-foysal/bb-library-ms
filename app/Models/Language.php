<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Language extends Model
{
    use HasFactory;

    protected $table='languages';
    protected $fillable=['name','sequence','status','created_by','updated_by'];

    protected $casts = [
        'is_active' => 'boolean'
    ];

   
}
