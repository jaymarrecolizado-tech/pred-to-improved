<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'division_id',
        'last_name',
        'first_name',
        'middle_name',
        'suffix',
        'position',
        'birth_date',
        'age',
        'gender',
        'place_of_birth',
        'address',
        'religion',
        'phone',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function getFullNameAttribute()
    {
        $name = "{$this->first_name} {$this->middle_name} {$this->last_name}";
        if ($this->suffix) {
            $name .= " {$this->suffix}";
        }
        return $name;
    }
}
