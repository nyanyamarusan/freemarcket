<?php

namespace App\Models;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'user_id',
        'message',
        'is_read',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query, int $userId)
    {
        return $query
            ->whereHas('transaction', function ($query) use ($userId) {
                $query->where('seller_id', $userId)->orWhere('buyer_id', $userId);
            })
            ->where('user_id', '!=', $userId)
            ->where('is_read', false);
    }
}
