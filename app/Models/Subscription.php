<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    /**
     * 一括割り当て（Mass Assignment）を許可する属性
     */
    protected $fillable = [
        'name',
        'category',
        'price',
        'billing_cycle',
        'next_billing_date',
        'memo',
    ];
}
