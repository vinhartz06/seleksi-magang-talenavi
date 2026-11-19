<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    // kolom mana saja yang boleh diisi secara massal
    protected $fillable = [
        'title',
        'assignee',
        'due_date',
        'time_tracked',
        'status',
        'priority',
    ];

    // konversi tipe data otomatis
    protected $casts = [
        'due_date' => 'date:Y-m-d',
        'time_tracked' => 'float',
    ];
}
