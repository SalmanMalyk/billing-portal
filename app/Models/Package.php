<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'title',
        'description',
        'fee',
    ];

    /**
     * Get the customers for the package.
     */
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
