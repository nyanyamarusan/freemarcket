<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('page', 'sell');
        $user = auth()->user();
        $purchasedItems = $user->purchases()->with('item')->get()->pluck('item');
        $soldItems = $user->items()->get();

        return view('profile', compact('user', 'purchasedItems', 'soldItems', 'tab'));
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
