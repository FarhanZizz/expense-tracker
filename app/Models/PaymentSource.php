<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSource extends Model
{
    protected $fillable = ['name', 'type', 'balance'];

public function transactions()
{
    return $this->hasMany(Transaction::class, 'source_id');
}
}
