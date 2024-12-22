<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable =
    [
        'transaction_time',
        'total_price',
        'total_item',
        'kasir_id',
        'payment_method'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function kasir()
    {
        return $this->belongsTo(User::class, 'kasir_id', 'id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
