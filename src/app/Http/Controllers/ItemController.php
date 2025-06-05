<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Http\Requests\CommentRequest;
use App\Http\Requests\ExhibitionRequest;
use App\Http\Requests\PurchaseRequest;
use App\Models\Category;
use App\Models\Item;
use App\Models\PaymentMethod;
use App\Models\Purchase;
use App\Models\Status;
use Illuminate\Http\Request;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Stripe;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $defaultTab = auth()->check() ? 'mylist' : 'recommend';
        $tab = $request->query('page', $defaultTab);
        $keyword = $request->query('keyword');

        if ($tab === 'mylist') {
            $items = auth()->check()
                ? auth()->user()->likes->map(function ($item) use ($keyword) {
                    if ($item->user_id === auth()->id()) {
                        return null;
                    }
                    return $keyword && !str_contains($item->name, $keyword) ? null : $item;
                })->filter()->values()
                : collect();
        } else {
            $userId = auth()->id();
            $items = Item::when($userId, function ($query, $userId) {
                    return $query->where('user_id', '!=', $userId);
                })
                ->keywordSearch($keyword)
                ->get();
        }

        return view('index', compact('items', 'tab', 'keyword'));
    }

    public function show($itemId)
    {
        $item = Item::withCount(['likes', 'comments'])
            ->with(['user', 'status', 'categories'])
            ->findOrFail($itemId);
        $categories = $item->categories;
        $comments = $item->comments()->with(['user'])->get();

        return view('show', compact('item', 'categories', 'comments'));
    }

    public function like($itemId)
    {
        $item = Item::findOrFail($itemId);
        $user = auth()->user()->load('likes');

        if ($item->isLikedBy($user)) {
            $item->likedUsers()->detach($user->id);
        } else {
            $item->likedUsers()->attach($user->id);
        }

        return redirect()->back();
    }

    public function comment(CommentRequest $request, $itemId)
    {
        $item = Item::findOrFail($itemId);
        $user = auth()->user();
        $item->comments()->create([
            'user_id' => $user->id,
            'content' => $request->content,
        ]);

        return redirect('/item/' . $item->id);
    }

    public function purchase($itemId)
    {
        $item = Item::findOrFail($itemId);
        $user = auth()->user();
        $paymentMethods = PaymentMethod::all();

        return view('purchase', compact('item', 'user', 'paymentMethods'));
    }

    public function buy(PurchaseRequest $request, $itemId)
    {
        $item = Item::findOrFail($itemId);
        if ($item->sold) {
            return redirect()->back();
        }

        $user = auth()->user();
        $paymentMethod = PaymentMethod::find($request->payment_method_id);
        $paymentMethodMap = [
            'カード支払い' => 'card',
            'コンビニ支払い' => 'konbini',
        ];
        $stripePaymentMethod = $paymentMethodMap[$paymentMethod->name];

        Stripe::setApiKey(config('services.stripe.secret'));
        $session = CheckoutSession::create([
            'payment_method_types' => [$stripePaymentMethod],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => $item->name,
                    ],
                    'unit_amount' => $item->price * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'customer_email' => $user->email,
            'success_url' => route('purchase.success', [
                'item_id' => $item->id,
                'shipping_zipcode' => $request->shipping_zipcode,
                'shipping_address' => $request->shipping_address,
                'shipping_building' => $request->shipping_building,
                'payment_method_id' => $paymentMethod->id,
            ]),
            'cancel_url' => route('purchase.cancel', ['item_id' => $item->id]),
        ]);
    
        return redirect($session->url);
    }

    public function success(Request $request, $itemId)
    {
        $user = auth()->user();
        $item = Item::findOrFail($itemId);

        Purchase::updateOrCreate(
            [
                'user_id' => $user->id,
                'item_id' => $item->id,
            ],
            [
                'payment_method_id' => $request->payment_method_id,
                'shipping_zipcode' => $request->shipping_zipcode,
                'shipping_address' => $request->shipping_address,
                'shipping_building' => $request->shipping_building,
            ]
        );
        
        $item->update(['sold' => true]);

        return redirect('/');
    }

    public function cancel($purchaseId)
    {
        $purchase = Purchase::find($purchaseId);
        if ($purchase && $purchase->user_id === auth()->id()) {
            $purchase->delete();
        }
        
        return redirect('/');
    }

    public function edit($itemId)
    {
        $item = Item::findOrFail($itemId);
        return view('address', compact('item'));
    }

    public function update(AddressRequest $request, $itemId)
    {
        $item = Item::findOrFail($itemId);
        $purchase = Purchase::updateOrCreate(
            [
                'user_id' => auth()->user()->id,
                'item_id' => $item->id,
            ],
            $request->only([
                'shipping_zipcode',
                'shipping_address',
                'shipping_building',
            ])
        );

        return redirect('/purchase/' . $item->id)
            ->withInput($purchase, $item);
    }

    public function create()
    {
        $categories = Category::all();
        $statuses = Status::all();

        return view('sell', compact('categories', 'statuses'));
    }

    public function store(ExhibitionRequest $request)
    {
        $user = auth()->user();
        $itemData = $request->only([
            'name',
            'description',
            'price',
            'status_id',
        ]);
        $imagePath = $request->file('image')->store('item-img', 'public');
        $itemData['image'] = basename($imagePath);
        $itemData['user_id'] = $user->id;
        $item = Item::create($itemData);

        $categories = $request->input('category_id', []);
        $item->categories()->attach($categories);

        return redirect('/');
    }
}
