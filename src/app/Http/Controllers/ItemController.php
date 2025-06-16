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
        $tab = $request->query('page', 'recommend');
        $keyword = $request->query('keyword');
        $queryString = $keyword ? '&keyword=' . $keyword : '';

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

        return view('index', compact('items', 'tab', 'keyword', 'queryString'));
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
            $item->likes()->detach($user->id);
        } else {
            $item->likes()->attach($user->id);
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
            'コンビニ払い' => 'konbini',
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
                    'unit_amount' => $item->price,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'customer_email' => $user->email,
            'metadata' => [
                'user_id' => $user->id,
                'item_id' => $item->id,
                'shipping_zipcode' => $request->shipping_zipcode,
                'shipping_address' => $request->shipping_address,
                'shipping_building' => $request->shipping_building,
                'payment_method_id' => $paymentMethod->id,
            ],
            'success_url' => url('/payment/success?session_id={CHECKOUT_SESSION_ID}'),
        ]);

        return redirect($session->url);
    }

    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return redirect('/');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $session = CheckoutSession::retrieve($sessionId);
        } catch (\Exception $e) {
            return redirect('/');
        }

        if ($session->payment_status !== 'paid') {
            return redirect('/');
        }

        $metadata = $session->metadata;

        Purchase::create([
            'user_id' => $metadata->user_id,
            'item_id' => $metadata->item_id,
            'payment_method_id' => $metadata->payment_method_id,
            'shipping_zipcode' => $metadata->shipping_zipcode,
            'shipping_address' => $metadata->shipping_address,
            'shipping_building' => $metadata->shipping_building,
        ]);

        Item::find($metadata->item_id)->update(['sold' => true]);

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
        $purchase = $request->only([
                'shipping_zipcode',
                'shipping_address',
                'shipping_building',
        ]);

        return redirect('/purchase/' . $item->id)
            ->withInput($purchase);
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
            'category_id'
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
