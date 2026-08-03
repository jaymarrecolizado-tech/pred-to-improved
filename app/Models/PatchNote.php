<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatchNote extends Model
{
    protected $fillable = [
        'version',
        'title',
        'description',
        'is_published',
    ];
}