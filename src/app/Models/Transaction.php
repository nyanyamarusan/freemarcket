<?php

namespace App\Models;

use App\Models\Evaluation;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'seller_id',
        'buyer_id',
        'status',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    public function scopeInvolvingUser($query, int $userId)
    {
        return $query->where(function ($query) use ($userId) {
            $query->where('seller_id', $userId)
                ->orWhere('buyer_id', $userId);
        });
    }

    public function scopeEvaluatedBy($query, int $userId)
    {
        return $query->whereHas('evaluations', function ($query) use ($userId) {
            $query->where('evaluator_id', $userId);
        });
    }

    public function scopeUnevaluatedBy($query, int $userId)
    {
        return $query->whereDoesntHave('evaluations', function ($query) use ($userId) {
            $query->where('evaluator_id', $userId);
        });
    }
}
