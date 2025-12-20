<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\Transaction;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    public function store(Request $request, $transaction_id)
    {
        $user = auth()->user();

        $transaction = Transaction::findOrFail($transaction_id);

        abort_unless($transaction->buyer_id === $user->id || $transaction->seller_id === $user->id, 403);

        $partner = $transaction->seller_id === $user->id ? $transaction->buyer : $transaction->seller;

        $unevaluated = Transaction::where('id', $transaction->id)
            ->unevaluatedBy($user->id)
            ->exists();

        if ($transaction->status === 'completed' && $unevaluated) {
            $evaluation = $request->only(['rating']);
            $evaluation['evaluator_id'] = $user->id;
            $evaluation['evaluatee_id'] = $partner->id;
            $evaluation['transaction_id'] = $transaction_id;
            Evaluation::create($evaluation);

            return redirect('/');
        }

        return redirect()->back();
    }
}
