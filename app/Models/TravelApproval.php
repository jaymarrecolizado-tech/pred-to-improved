<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelApproval extends Model
{
    protected $fillable = [
        'travel_order_id',
        'user_id',
        'approver_id',
        'approver_name_snapshot',
        'approver_position_snapshot',
        'approver_signature_snapshot',
        'workflow_step',
        'to_code',
        'status',
        'reject_reason',
        'revision_reason',
        'approved_at',
        'rejected_at',
        'revised_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'revised_at'  => 'datetime',
    ];

    public function travelOrder()
    {
        return $this->belongsTo(TravelOrder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function workflow()
    {
        return $this->belongsTo(TravelWorkflow::class, 'workflow_step');
    }
}