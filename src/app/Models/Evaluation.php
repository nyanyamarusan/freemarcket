<?php

namespace App\Models;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'evaluator_id',
        'evaluatee_id',
        'rating',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function evaluatee()
    {
        return $this->belongsTo(User::class, 'evaluatee_id');
    }
}
