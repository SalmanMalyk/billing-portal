<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'customer_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'status',
        'billing_period_start',
        'billing_period_end',
        'notes',
    ];
    
    protected $casts = [
        'payment_date' => 'datetime',
        'billing_period_start' => 'date',
        'billing_period_end' => 'date',
    ];

    /**
     * Get the customer that owns the transaction.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Scope a query to only include paid transactions.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query to only include pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include failed transactions.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to filter by billing period.
     */
    public function scopeForBillingPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('billing_period_start', [$startDate, $endDate])
            ->orWhereBetween('billing_period_end', [$startDate, $endDate]);
    }
}
