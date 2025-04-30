<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
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
}
