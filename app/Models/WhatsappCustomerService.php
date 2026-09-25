<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappCustomerService extends Model
{
    protected $table = 'cs_settings';
    
    protected $fillable = [
        'name',
        'phone_number',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}