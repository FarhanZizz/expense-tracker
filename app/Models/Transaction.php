<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['source_id', 'category_id', 'type', 'amount', 'note', 'date'];

protected $casts = ['date' => 'date'];

public function source()
{
    return $this->belongsTo(PaymentSource::class, 'source_id');
}

public function category()
{
    return $this->belongsTo(Category::class);
}
}
