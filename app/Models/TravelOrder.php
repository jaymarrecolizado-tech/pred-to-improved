<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'to_code',
        'start_date',
        'end_date',
        'travel_location',
        'purpose',
        'remarks',
        'travel_sources',
        'vehicle',
        'other_funds',
        'travelers',
        'attachment',
        'pdf_path',
        'workflow_steps',
        'status',
        'reject_reason',
        'cancel_reason',
        'revision_reason',
        'submitted_at',
        'completed_at',
        'rejected_at',
        'cancelled_at',
        'revised_at',
    ];

    protected function casts(): array
    {
        return [
            'travel_location' => 'array',
            'attachment'      => 'array',
            'travel_sources'  => 'array',
            'status'          => 'string',
            'start_date'      => 'datetime',
            'end_date'        => 'datetime',
            'other_funds'     => 'array',
            'travelers'       => 'array',
            'workflow_steps'  => 'array',
            'submitted_at'    => 'datetime',
            'completed_at'    => 'datetime',
            'rejected_at'     => 'datetime',
            'cancelled_at'    => 'datetime',
            'revised_at'      => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function travelSource()
    {
        return $this->belongsTo(TravelSource::class);
    }

    public function approvals()
    {
        return $this->hasMany(TravelApproval::class)->orderBy('id');
    }

    public function getCurrentApprovalStep()
    {
        return $this->approvals()
            ->where('status', 'PENDING')
            ->orderBy('id')
            ->first();
    }

    public function getRevisionApprovalStep()
    {
        return $this->approvals()
            ->where('status', 'FOR_REVISION')
            ->orderBy('id')
            ->first();
    }

    public function scopeDraft($query) { return $query->where('status', 'DRAFT'); }
    public function scopeSubmitted($query) { return $query->where('status', 'SUBMITTED'); }
    public function scopePending($query) { return $query->where('status', 'PENDING'); }
    public function scopeApproved($query) { return $query->where('status', 'APPROVED'); }
    public function scopeRejected($query) { return $query->where('status', 'REJECTED'); }
    public function scopeCompleted($query) { return $query->where('status', 'COMPLETED'); }
    public function scopeCancelled($query) { return $query->where('status', 'CANCELLED'); }
    public function scopeForRevision($query) { return $query->where('status', 'FOR_REVISION'); }

    public function generateToCode()
    {
        return 'TO-' . now()->format('dmY') . '-' . str_pad($this->id, 2, '0', STR_PAD_LEFT);
    }

    public static function hasConflictingTravelOrder(
        $employeeId, $startDate, $endDate, $excludeTravelOrderId = null
    ) {
        $employee = \App\Models\Employee::find($employeeId);
        if (!$employee) return false;
        $name = $employee->full_name;

        $query = static::whereNotIn('status', ['REJECTED', 'CANCELLED', 'DRAFT'])
            ->where(function ($q) use ($employeeId, $name) {
                $q->whereRaw("JSON_CONTAINS(travelers, JSON_OBJECT('name', ?), '$')", [$name])
                  ->orWhereRaw("JSON_CONTAINS(travelers, JSON_OBJECT('employee_id', ?), '$')", [(int) $employeeId])
                  ->orWhereRaw("JSON_CONTAINS(travelers, JSON_OBJECT('employee_id', ?), '$')", [(string) $employeeId]);
            })
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q2) use ($startDate, $endDate) {
                      $q2->where('start_date', '<=', $startDate)
                         ->where('end_date', '>=', $endDate);
                  });
            });

        if ($excludeTravelOrderId) {
            $query->where('id', '!=', $excludeTravelOrderId);
        }

        return $query->exists();
    }

    public static function getConflictingTravelOrders(
        $employeeId, $startDate, $endDate, $excludeTravelOrderId = null
    ) {
        $employee = \App\Models\Employee::find($employeeId);
        if (!$employee) return collect();
        $name = $employee->full_name;

        $query = static::whereNotIn('status', ['REJECTED', 'CANCELLED', 'DRAFT'])
            ->where(function ($q) use ($employeeId, $name) {
                $q->whereRaw("JSON_CONTAINS(travelers, JSON_OBJECT('name', ?), '$')", [$name])
                  ->orWhereRaw("JSON_CONTAINS(travelers, JSON_OBJECT('employee_id', ?), '$')", [(int) $employeeId])
                  ->orWhereRaw("JSON_CONTAINS(travelers, JSON_OBJECT('employee_id', ?), '$')", [(string) $employeeId]);
            })
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q2) use ($startDate, $endDate) {
                      $q2->where('start_date', '<=', $startDate)
                         ->where('end_date', '>=', $endDate);
                  });
            });

        if ($excludeTravelOrderId) {
            $query->where('id', '!=', $excludeTravelOrderId);
        }

        return $query->get();
    }
}