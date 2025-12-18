<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index($transaction_id)
    {
        $user = auth()->user();

        $baseQuery = Transaction::where(function ($q) use ($user) {
            $q->where('seller_id', $user->id)
            ->orWhere('buyer_id', $user->id);
        });

        $transactions = $baseQuery
            ->where('status', 'in_progress')
            ->with(['seller', 'buyer'])
            ->get();

        $selectedTransaction = $baseQuery
            ->with(['seller', 'buyer'])
            ->where('id', $transaction_id)
            ->firstOrFail();

        $partner = $selectedTransaction->seller_id === $user->id ? $selectedTransaction->buyer : $selectedTransaction->seller;

        $messages = Message::where('transaction_id', $transaction_id)
            ->with('user')
            ->get();

        Message::where('transaction_id', $transaction_id)
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('transaction', compact('transactions', 'selectedTransaction', 'partner', 'messages'));
    }

    public function message(Request $request, $transaction_id)
    {
        $user = auth()->user();
        $transaction = Transaction::findOrFail($transaction_id);

        $messageData = $request->only([
            'message'
        ]);

        $imagePath = $request->file('image')->store('message-img', 'public');
        $messageData['image'] = basename($imagePath);
        $messageData['user_id'] = $user->id;
        $messageData['transaction_id'] = $transaction_id;

        Message::create($messageData);

        return redirect()->back();
    }

    public function update(Request $request, $transaction_id)
    {
        $message = Message::where('transaction_id', $transaction_id)
            ->where('user_id', auth()->user()->id)
            ->firstOrFail();

        $messageData =  $request->only('message', 'image');

        $message->update([
            'message' => $messageData['message'],
            'image' => $messageData['image'],
        ]);

        return redirect()->back();
    }

    public function destroy($transaction_id)
    {
        $message = Message::where('transaction_id', $transaction_id)
            ->where('user_id', auth()->user()->id)
            ->firstOrFail();

        $message->delete();

        return redirect()->back();
    }
}