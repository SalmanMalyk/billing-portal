<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'full_name',
        'email',
        'phone_number',
        'address',
        'package_id',
        'bill',
        'billing_start_date',
        'status',
    ];
    
    protected $casts = [
        'billing_start_date' => 'date:Y-m-d',
        'status' => 'boolean',
    ];

    /**
     * Get the package that the customer belongs to.
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get the transactions for the customer.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Check if customer has paid for the current billing period.
     */
    public function hasPaidForCurrentPeriod()
    {
        $currentBillingDate = $this->getCurrentBillingDate();
        if (!$currentBillingDate) {
            return false;
        }
        
        // The current billing period starts on the current billing date and ends one month later
        $startDate = $currentBillingDate;
        $endDate = $currentBillingDate->copy()->addMonth()->subDay();
        
        return $this->transactions()
            ->where('status', 'paid')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('billing_period_start', [$startDate, $endDate])
                    ->orWhereBetween('billing_period_end', [$startDate, $endDate]);
            })
            ->exists();
    }

    /**
     * Get the current billing period date based on billing_start_date.
     */
    public function getCurrentBillingDate()
    {
        if (!$this->billing_start_date) {
            return null;
        }

        $billingStartDate = Carbon::parse($this->billing_start_date);
        $today = Carbon::today();
        $billingDay = $billingStartDate->day;
        
        // Adjust for months with fewer days than the billing day
        $daysInCurrentMonth = Carbon::now()->daysInMonth;
        $adjustedBillingDay = min($billingDay, $daysInCurrentMonth);
        
        // Calculate this month's billing date
        $currentMonthBilling = Carbon::createFromDate($today->year, $today->month, $adjustedBillingDay);
        
        // If today is before this month's billing date, the current billing is from last month
        if ($today->lt($currentMonthBilling)) {
            return $currentMonthBilling->copy()->subMonth();
        }
        
        // Otherwise, return this month's billing date
        return $currentMonthBilling;
    }

    /**
     * Get the next billing date based on billing_start_date.
     */
    public function getNextBillingDate()
    {
        if (!$this->billing_start_date) {
            return null;
        }
        
        // Next billing date is one month after the current billing date
        return $this->getCurrentBillingDate()->copy()->addMonth();
    }

    /**
     * Calculate the days remaining until next billing date.
     * Returns negative number for overdue bills.
     */
    public function daysUntilNextBilling()
    {
        $currentBillingDate = $this->getCurrentBillingDate();
        if (!$currentBillingDate) {
            return null;
        }
        
        $today = Carbon::today();
        $hasPaid = $this->hasPaidForCurrentPeriod();
        
        // If not paid for current period and today is after the current billing date,
        // return negative days (overdue)
        if (!$hasPaid && $today->gt($currentBillingDate)) {
            return -(int)$today->diffInDays($currentBillingDate);
        }
        
        // Otherwise return days until next billing date
        $nextBillingDate = $this->getNextBillingDate();
        return (int)$today->diffInDays($nextBillingDate, false);
    }

    /**
     * Get pending transactions.
     */
    public function pendingTransactions()
    {
        return $this->transactions()->where('status', 'pending');
    }
    
    /**
     * Get paid transactions.
     */
    public function paidTransactions()
    {
        return $this->transactions()->where('status', 'paid');
    }
}
