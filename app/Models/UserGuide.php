<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGuide extends Model
{
    protected $fillable = [
        'title',
        'category',
        'sort_order',
        'content',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function stepLabel(): string
    {
        return 'Step ' . (int) $this->sort_order;
    }

    public function stepHeading(): string
    {
        return $this->stepLabel() . ' — ' . $this->title;
    }
}
