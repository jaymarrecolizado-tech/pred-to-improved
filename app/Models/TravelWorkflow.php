<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelWorkflow extends Model
{
    protected $fillable = [
        'user_id',
        'workflow_step',
        'approver_id',
        'description',
        'to_code_provider',
        'to_provincial_officer',
        'to_provincial_officer_two',
        'to_ard_initial',
        'to_approve',
        'to_oic_rd',
        'to_recommend',
        'to_admin_initial',
        'to_admin_recommend',
        'notify_email',
        'active',
    ];

    protected $casts = [
        'to_code_provider'          => 'boolean',
        'to_approve'                => 'boolean',
        'to_oic_rd'                 => 'boolean',
        'to_recommend'              => 'boolean',
        'to_hr_route'               => 'boolean',
        'to_admin_initial'          => 'boolean',
        'to_admin_recommend'        => 'boolean',
        'to_provincial_officer'     => 'boolean',
        'to_provincial_officer_two' => 'boolean',
        'to_ard_initial'            => 'boolean',
        'notify_email'              => 'boolean',
        'active'                    => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('workflow_step', 'asc');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function getStepTypeAttribute(): string
    {
        if ($this->to_recommend)              return 'Chief TOD';
        if ($this->to_hr_route)               return 'HR Routing';
        if ($this->to_admin_initial)          return 'Chief Admin Initial';
        if ($this->to_admin_recommend)        return 'Chief Admin Recommend';
        if ($this->to_approve)                return 'Regional Director';
        if ($this->to_oic_rd)                 return 'OIC, Regional Director';
        if ($this->to_code_provider)          return 'HR TO Number';
        return 'Standard Approval';
    }
}