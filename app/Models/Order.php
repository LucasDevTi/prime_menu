<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'id',
        'total_value',
        'status_payment',
        'description_status'
    ];

    public function table()
    {
        return $this->belongsTo(Table::class);
    }
}
