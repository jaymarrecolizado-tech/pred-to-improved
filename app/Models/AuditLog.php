<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'model',
        'model_id',
        'old_value',
        'new_value',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'old_value' => 'array',
            'new_value' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function logOverride(
        string $action,
        string $model,
        int $modelId,
        array $oldValue = [],
        array $newValue = [],
        ?string $notes = null
    ): void {
        static::create([
            'user_id'   => auth()->id(),
            'action'    => $action,
            'model'     => $model,
            'model_id'  => $modelId,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'notes'     => $notes,
        ]);
    }
}
