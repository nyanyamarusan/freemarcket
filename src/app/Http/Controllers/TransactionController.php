<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Transaction;
use App\Http\Requests\MessageRequest;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request, $transaction_id)
    {
        $user = auth()->user();

        $sessionMessage = $request->session()->get("session_message_{$transaction_id}");

        $transactions = Transaction::query()
            ->involvingUser($user->id)
            ->unevaluatedBy($user->id)
            ->with(['seller', 'buyer'])
            ->get();

        $selectedTransaction = Transaction::query()
            ->involvingUser($user->id)
            ->where('id', $transaction_id)
            ->firstOrFail();

        $partner = $selectedTransaction->seller_id === $user->id ? $selectedTransaction->buyer : $selectedTransaction->seller;

        $messages = Message::where('transaction_id', $transaction_id)
            ->with('user')
            ->orderBy('created_at')
            ->get();

        Message::where('transaction_id', $transaction_id)
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('transaction', compact(
            'user',
            'sessionMessage',
            'transactions',
            'selectedTransaction',
            'partner',
            'messages'
        ));
    }

    public function store(MessageRequest $request, $transaction_id)
    {
        $user = auth()->user();
        Transaction::findOrFail($transaction_id);

        session()->put(
            "session_message_{$transaction_id}",
            $request->input('message')
        );

        $messageData = $request->only([
            'message'
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('message-img', 'public');
            $messageData['image'] = basename($imagePath);
        }
        $messageData['user_id'] = $user->id;
        $messageData['transaction_id'] = $transaction_id;

        Message::create($messageData);

        return redirect()->back();
    }

    public function update(Request $request, $message_id)
    {
        $user = auth()->user();
        $message = Message::findOrFail($message_id);
        abort_unless($message->user_id === $user->id, 403);
        $messageData = $request->only('message');
        $message->update($messageData);

        return redirect()->back();
    }

    public function destroy($message_id)
    {
        $user = auth()->user();
        $message = Message::findOrFail($message_id);
        abort_unless($message->user_id === $user->id, 403);
        $message->delete();

        return redirect()->back();
    }

    public function completed($transaction_id)
    {
        $user = auth()->user();
        $transaction = Transaction::findOrFail($transaction_id);
        abort_unless($transaction->buyer_id === $user->id, 403);

        if ($transaction->status === 'in_progress') {
            $transaction->update(['status' => 'completed']);
            //メール送信処理書く
        }

        return redirect()->back();
    }

}