<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Transaction;
use App\Http\Requests\ProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('page', 'sell');
        $user = auth()->user();
        $purchasedItems = $user->purchases()->with('item')->get()->pluck('item');
        $soldItems = $user->items()->get();

        $transactions = Transaction::query()
            ->involvingUser($user->id)
            ->unevaluatedBy($user->id)
            ->orderByDesc(
                Message::unread($user->id)
                    ->select('created_at')
                    ->whereColumn('messages.transaction_id', 'transactions.id')
                    ->latest()
                    ->limit(1)
            )
            ->get();

        $totalCount = Message::unread($user->id)->count();
        $count = Message::unread($user->id)
            ->select('transaction_id', DB::raw('COUNT(*) as unread_count'))
            ->groupBy('transaction_id')
            ->pluck('unread_count', 'transaction_id');

        return view('profile', compact('user', 'purchasedItems', 'soldItems', 'tab', 'transactions', 'totalCount', 'count'));
    }

    public function edit()
    {
        $user = auth()->user();

        return view('edit', compact('user'));
    }

    public function update(ProfileRequest $request)
    {
        $user = auth()->user();
        $profile = $request->only([
            'name',
            'zipcode',
            'address',
            'building',
        ]);

        if ($request->hasFile('image')) {
            if ($user->image){
                $imagePath = 'profile-img/' . $user->image;
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('profile-img', 'public');
            $profile['image'] = basename($imagePath);
        }

        $user->update($profile);

        return redirect('/');
    }
}
