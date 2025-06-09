<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Item;
use App\Models\PaymentMethod;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\TestCase;

class FreemarcketTest extends TestCase
{
    use RefreshDatabase;

    public function test_registerNameValidation(): void
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_registerEmailValidation(): void
    {
        $response = $this->post('/register', [
            'name' => '山田',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_registerPasswordValidation(): void
    {
        $response = $this->post('/register', [
            'name' => '山田',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_registerPasswordTooShortValidation(): void
    {
        $response = $this->post('/register', [
            'name' => '山田',
            'email' => 'test@example.com',
            'password' => 'pass',
            'password_confirmation' => 'pass',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_registerPasswordConfirmationValidation(): void
    {
        $response = $this->post('/register', [
            'name' => '山田',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password1234',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_registerSuccess(): void
    {
        $response = $this->post('/register', [
            'name' => '山田',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertRedirect('/email/verify');

        $this->assertDatabaseHas('users', [
            'name' => '山田',
            'email' => 'test@example.com',
        ]);
    }

    public function test_loginNameValidation(): void
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_loginPasswordValidation(): void
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_loginValidation(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
        $response = $this->post('/login', [
            'email' => 'abc@example.com',
            'password' => 'pass12345',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_loginSuccess(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/?page=mylist');
    }

    public function test_logoutSuccess(): void
    {
        $response = $this->post('/logout');

        $response->assertRedirect('/login');
    }

    public function test_itemlist(): void
    {
        Item::factory()->count(5)->create();

        $response = $this->get('/');

        $response->assertStatus(200);

        $items = Item::all();
        foreach ($items as $item) {
            $response->assertSeeText($item->name);
        }
    }

    public function test_itemSold(): void
    {
        Item::factory()->create([
            'sold' => true,
            'name' => 'テスト商品'
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSeeText('Sold');
    }

    public function test_own_items_hidden()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $ownItem = Item::factory()->create([
            'user_id' => $user->id,
            'name' => '自分の商品',
        ]);
        $otherItem = Item::factory()->create([
            'user_id' => $otherUser->id,
            'name' => '他人の商品',
        ]);

        $user->likes()->attach([$ownItem->id, $otherItem->id]);

        $this->actingAs($user);

        $response = $this->get('/');

        $response->assertStatus(200);

        $response->assertDontSeeText($ownItem->name);
        $response->assertSeeText($otherItem->name);
    }

    public function test_only_liked_in_mylist()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $likedItem = Item::factory()->create([
            'name' => 'いいね商品',
        ]);
        $user->likes()->attach($likedItem->id);
        $unlikedItem = Item::factory()->create([
            'name' => 'いいねしていない商品',
        ]);

        $response = $this->get('/?page=mylist');

        $response->assertStatus(200);

        $response->assertSeeText($likedItem->name);
        $response->assertDontSeeText($unlikedItem->name);
    }

    public function test_itemSold_in_mylist(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $item = Item::factory()->create([
            'sold' => true,
            'name' => 'テスト商品'
        ]);
        $user->likes()->attach($item->id);

        $response = $this->get('/?page=mylist');

        $response->assertStatus(200);
        $response->assertSeeText('Sold');
    }

    public function test_own_items_hidden_in_mylist(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $ownItem = Item::factory()->create([
            'user_id' => $user->id,
            'name' => '自分の商品',
        ]);
        $otherItem = Item::factory()->create([
            'name' => '他人の商品',
        ]);
        $user->likes()->attach($otherItem->id);

        $response = $this->get('/?page=mylist');

        $response->assertStatus(200);

        $response->assertDontSeeText($ownItem->name);
        $response->assertSeeText($otherItem->name);
    }

    public function test_guest_cannot_see_mylist_items(): void
    {
        $item = Item::factory()->create([
            'name' => 'いいね商品'
        ]);

        $response = $this->get('/?page=mylist');

        $response->assertStatus(200);
        $response->assertDontSeeText($item->name);
    }

    public function test_search(): void
    {
        Item::factory()->create([
            'name' => '商品1'
        ]);
        Item::factory()->create([
            'name' => 'テスト'
        ]);
        Item::factory()->create([
            'name' => '商品2'
        ]);

        $response = $this->get('/?keyword=商品');

        $response->assertStatus(200);
        $response->assertSeeText('商品1');
        $response->assertSeeText('商品2');
        $response->assertDontSeeText('テスト');
    }

    public function test_search_result_in_mylist(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $keyword = '商品';
        $response = $this->get('/?keyword=' . $keyword);

        $response->assertStatus(200);
        $response->assertSee($keyword);

        $response2 = $this->get('/?page=mylist&keyword=' . $keyword);

        $response2->assertStatus(200);
        $response2->assertSee($keyword);
    }

    public function test_show_item_all_info(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create([
            'name' => 'テストカテゴリー'
        ]);
        $status = Status::factory()->create([
            'name' => '良好'
        ]);

        $item = Item::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'description' => 'テスト商品の説明',
            'price' => 1000,
            'image' => 'test.png',
            'sold' => false,
        ]);

        $item->categories()->attach($category->id);

        $likes = User::factory()->count(2)->create();
        foreach ($likes as $like) {
            $like->likes()->attach($item->id);
        }

        $comments = Comment::factory()->count(2)->create([
            'item_id' => $item->id,
            'user_id' => $user->id,
            'content' => 'テストコメント'
        ]);

        $response = $this->get('/item/' . $item->id);

        $response->assertStatus(200);
        $response->assertSeeText($item->name);
        $response->assertSeeText($item->brand);
        $response->assertSeeText($item->description);
        $response->assertSeeText(number_format($item->price));
        $response->assertSee($item->image);
        $response->assertDontSeeText('Sold');
        foreach ($item->categories as $category) {
            $response->assertSeeText($category->name);
        }
        $response->assertSeeText($item->status->name);
        $response->assertSee((string) $item->likes()->count());
        $response->assertSee((string) $item->comments()->count());

        foreach ($comments as $comment) {
            $response->assertSeeText($comment->content);
            $response->assertSeeText($comment->user->name);
        }

        $response->assertSee('src="' . asset('storage/item-img/' . $item->image) . '"', false);
    }

    public function test_show_item_multiple_categories(): void
{
    $user = User::factory()->create();
    $categories = Category::factory()->count(2)->state(new Sequence(
        ['name' => 'カテゴリー1'],
        ['name' => 'カテゴリー2'],
    ))->create();

    $item = Item::factory()->create(['user_id' => $user->id]);

    $item->categories()->attach($categories->pluck('id'));

    $response = $this->get('/item/' . $item->id);

    $response->assertStatus(200);

    foreach ($categories as $category) {
        $response->assertSeeText($category->name);
    }
}

    public function test_like_item(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
    
        $this->actingAs($user)
            ->get('/item/' . $item->id)
            ->assertStatus(200);
        $initialLikeCount = $item->likes()->count();
    
        $response = $this->actingAs($user)
            ->post('/item/' . $item->id);
        $user->likes()->attach($item->id);
        $response->assertStatus(302);
        $this->assertEquals($initialLikeCount + 1, $item->likes()->count());
    }

    public function test_liked_icon(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $user->likes()->attach($item->id);

        $response = $this->actingAs($user)
            ->get('/item/' . $item->id);
        $response->assertSee('like__icon') && $response->assertSee('liked');
    }

    public function test_user_can_unlike_item(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $this->actingAs($user)
            ->get('/item/' . $item->id)
            ->assertStatus(200);
        $user->likes()->attach($item->id);
        $beforeLikeCount = $item->likes()->count();

        $response = $this->actingAs($user)
            ->post('/item/' . $item->id);
        $user->likes()->detach($item->id);
        $afterLikeCount = $item->likes()->count();
        $response->assertStatus(302);
        $item->refresh();
        $this->assertEquals($beforeLikeCount - 1, $afterLikeCount);
    }

    public function test_post_comment(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $this->actingAs($user)
            ->get('/item/' . $item->id)
            ->assertStatus(200);
        $initialCommentCount = $item->comments()->count();

        $response = $this->actingAs($user)
            ->post('/item/' . $item->id, [
                'content' => 'テストコメント'
            ]);
        $response->assertStatus(302);
        $item->refresh();
        $this->assertEquals($initialCommentCount + 1, $item->comments()->count());

        $this->assertDatabaseHas('comments', [
            'item_id' => $item->id,
            'user_id' => $user->id,
            'content' => 'テストコメント'
        ]);
    }

    public function test_post_comment_guest(): void
    {
        $item = Item::factory()->create();
        $response = $this->post('/item/' . $item->id, [
            'content' => '未ログインコメント'
        ]);
        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('comments', [
            'item_id' => $item->id,
            'content' => '未ログインコメント'
        ]);
    }

    public function test_commentValidate(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $response = $this->actingAs($user)
            ->post('/item/' . $item->id, [
                'content' => ''
            ]);
        $response->assertSessionHasErrors('content');
    }

    public function test_commentValidateTooLong(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $response = $this->actingAs($user)
            ->post('/item/' . $item->id, [
                'content' => str_repeat('a', 256)
            ]);
        $response->assertSessionHasErrors('content');
    }

    public function test_purchase(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->get('/purchase/' . $item->id);
        $response->assertStatus(200);

        $paymentMethod = PaymentMethod::create([
            'name' => 'カード支払い',
        ]);

        $mockSession = Mockery::mock('alias:Stripe\Checkout\Session');
        $mockSession->shouldReceive('create')->andReturn((object)[
            'id' => 'cs_test_123',
            'url' => 'https://checkout.stripe.com/pay/cs_test_123',
        ]);

        $purchaseData = ([
            'payment_method_id' => $paymentMethod->id,
            'shipping_address' => 'test address',
            'shipping_building' => 'test building',
            'shipping_zipcode' => '123-4567',
            'item_id' => $item->id,
            'user_id' => $user->id,
        ]);

        $response = $this->post('/purchase/' . $item->id, $purchaseData);
        $response->assertRedirect('https://checkout.stripe.com/pay/cs_test_123');

        $webhookPayload = [
            'id' => 'evt_test_123',
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_123',
                    'metadata' => (object)[
                        'user_id' => (string)$user->id,
                        'item_id' => (string)$item->id,
                        'payment_method_id' => (string)$paymentMethod->id,
                        'shipping_zipcode' => $purchaseData['shipping_zipcode'],
                        'shipping_address' => $purchaseData['shipping_address'],
                        'shipping_building' => $purchaseData['shipping_building'],
                    ],
                ],
            ],
        ];
    
        $response = $this->postJson('/stripe/webhook', $webhookPayload);
        $response->assertStatus(200);
        $item->refresh();
        $this->assertTrue($item->sold);
    }

    public function test_purchased_item_sold(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/purchase/' . $item->id);
        $response->assertStatus(200);

        $paymentMethod = PaymentMethod::create([
            'name' => 'カード支払い',
        ]);

        $mockSession = Mockery::mock('alias:Stripe\Checkout\Session');
        $mockSession->shouldReceive('create')->andReturn((object)[
            'id' => 'cs_test_123',
            'url' => 'https://checkout.stripe.com/pay/cs_test_123',
        ]);

        $purchaseData = ([
            'payment_method_id' => $paymentMethod->id,
            'shipping_address' => 'test address',
            'shipping_building' => 'test building',
            'shipping_zipcode' => '123-4567',
            'item_id' => $item->id,
            'user_id' => $user->id,
        ]);

        $response = $this->post('/purchase/' . $item->id, $purchaseData);
        $response->assertRedirect('https://checkout.stripe.com/pay/cs_test_123');

        $webhookPayload = [
            'id' => 'evt_test_123',
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_123',
                    'metadata' => (object)[
                        'user_id' => (string)$user->id,
                        'item_id' => (string)$item->id,
                        'payment_method_id' => (string)$paymentMethod->id,
                        'shipping_zipcode' => $purchaseData['shipping_zipcode'],
                        'shipping_address' => $purchaseData['shipping_address'],
                        'shipping_building' => $purchaseData['shipping_building'],
                    ],
                ],
            ],
        ];
    
        $response = $this->postJson('/stripe/webhook', $webhookPayload);
        $response->assertStatus(200);
        $item->refresh();
        $this->assertTrue($item->sold);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSeeText('Sold');
    }

    public function test_purchased_item_in_profile(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/purchase/' . $item->id);
        $response->assertStatus(200);

        $paymentMethod = PaymentMethod::create([
            'name' => 'カード支払い',
        ]);

        $mockSession = Mockery::mock('alias:Stripe\Checkout\Session');
        $mockSession->shouldReceive('create')->andReturn((object)[
            'id' => 'cs_test_123',
            'url' => 'https://checkout.stripe.com/pay/cs_test_123',
        ]);

        $purchaseData = ([
            'payment_method_id' => $paymentMethod->id,
            'shipping_address' => 'test address',
            'shipping_building' => 'test building',
            'shipping_zipcode' => '123-4567',
            'item_id' => $item->id,
            'user_id' => $user->id,
        ]);

        $response = $this->post('/purchase/' . $item->id, $purchaseData);
        $response->assertRedirect('https://checkout.stripe.com/pay/cs_test_123');

        $webhookPayload = [
            'id' => 'evt_test_123',
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_123',
                    'metadata' => (object)[
                        'user_id' => (string)$user->id,
                        'item_id' => (string)$item->id,
                        'payment_method_id' => (string)$paymentMethod->id,
                        'shipping_zipcode' => $purchaseData['shipping_zipcode'],
                        'shipping_address' => $purchaseData['shipping_address'],
                        'shipping_building' => $purchaseData['shipping_building'],
                    ],
                ],
            ],
        ];
    
        $response = $this->postJson('/stripe/webhook', $webhookPayload);
        $response->assertStatus(200);
        $item->refresh();
        $this->assertTrue($item->sold);

        $this->get('/mypage?page=buy')
            ->assertStatus(200)
            ->assertSee($item->name);
    }

    public function test_edit_address():void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $this->actingAs($user);
        $response = $this->get('/purchase/address/' . $item->id);
        $response->assertStatus(200);

        $shippingAddress = [
            'shipping_zipcode' => '987-6543',
            'shipping_address' => 'edit address',
            'shipping_building' => 'edit building',
        ];
        $response = $this->post('/purchase/' . $item->id, $shippingAddress);

        $response = $this->get('/purchase/' . $item->id);
        $response->assertStatus(200)
                ->assertSee($shippingAddress['shipping_zipcode'])
                ->assertSee($shippingAddress['shipping_address'])
                ->assertSee($shippingAddress['shipping_building']);
    }

    public function test_registered_edit_address(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $this->actingAs($user);
        $response = $this->get('/purchase/address/' . $item->id);
        $response->assertStatus(200);

        $shippingAddress = [
            'shipping_zipcode' => '987-6543',
            'shipping_address' => 'edit address',
            'shipping_building' => 'edit building',
        ];
        $response = $this->post('/purchase/' . $item->id, $shippingAddress);

        $paymentMethod = PaymentMethod::create([
            'name' => 'カード支払い',
        ]);

        $mockSession = Mockery::mock('alias:Stripe\Checkout\Session');
        $mockSession->shouldReceive('create')->andReturn((object)[
            'id' => 'cs_test_123',
            'url' => 'https://checkout.stripe.com/pay/cs_test_123',
        ]);

        $purchaseData = ([
            'payment_method_id' => $paymentMethod->id,
            'shipping_zipcode' => $shippingAddress['shipping_zipcode'],
            'shipping_address' => $shippingAddress['shipping_address'],
            'shipping_building' => $shippingAddress['shipping_building'],
            'item_id' => $item->id,
            'user_id' => $user->id,
        ]);

        $response = $this->post('/purchase/' . $item->id, $purchaseData);
        $response->assertRedirect('https://checkout.stripe.com/pay/cs_test_123');

        $webhookPayload = [
            'id' => 'evt_test_123',
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_123',
                    'metadata' => (object)[
                        'user_id' => (string)$user->id,
                        'item_id' => (string)$item->id,
                        'payment_method_id' => (string)$paymentMethod->id,
                        'shipping_zipcode' => $purchaseData['shipping_zipcode'],
                        'shipping_address' => $purchaseData['shipping_address'],
                        'shipping_building' => $purchaseData['shipping_building'],
                    ],
                ],
            ],
        ];
    
        $response = $this->postJson('/stripe/webhook', $webhookPayload);
        $response->assertStatus(200);
        $item->refresh();
        $this->assertTrue($item->sold);

        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment_method_id' => $paymentMethod->id,
            'shipping_zipcode' => $shippingAddress['shipping_zipcode'],
            'shipping_address' => $shippingAddress['shipping_address'],
            'shipping_building' => $shippingAddress['shipping_building'],
        ]);
    }

    public function test_profile(): void
    {
        $user = User::factory()->create([
            'name' => '太郎',
            'image' => 'test.png',
        ]);
        $soldItems = Item::factory()->count(2)->create([
            'user_id' => $user->id,
            'name' => '出品した商品',
        ]);
        $purchasedItems = Item::factory()->count(2)->create([
            'name' => '購入した商品',
            'sold' => true,
        ]);
        $paymentMethod = PaymentMethod::create([
            'name' => 'カード支払い',
        ]);
        foreach ($purchasedItems as $purchasedItem) {
            $user->purchases()->create([
                'item_id' => $purchasedItem->id,
                'payment_method_id' => $paymentMethod->id,
                'shipping_zipcode' => '123-4567',
                'shipping_address' => 'test address',
                'shipping_building' => 'test building',
            ]);
        }
        $this->actingAs($user);
        $response = $this->get('/mypage');
        $response->assertStatus(200);

        $response->assertSee($user->name);
        $response->assertSee($user->image);

        $response = $this->get('/mypage?page=sell');
        $response->assertStatus(200);
        foreach ($soldItems as $soldItem) {
            $response->assertSee($soldItem->name);
        }

        $response = $this->get('/mypage?page=buy');
        $response->assertStatus(200);
        foreach ($purchasedItems as $purchasedItem) {
            $response->assertSee($purchasedItem->name);
        }
    }

    public function test_profile_edit(): void
    {
        $user = User::factory()->create([
            'name' => '太郎',
            'image' => 'test.png',
            'zipcode' => '123-4567',
            'address' => 'test address',
            'building' => 'test building',
        ]);
        $this->actingAs($user);

        $response = $this->get('/mypage/profile');
        $response->assertStatus(200);
        
        $response->assertSee($user->name);
        $response->assertSee($user->image);
        $response->assertSee($user->zipcode);
        $response->assertSee($user->address);
        $response->assertSee($user->building);
    }

    public function test_store_item(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->get('/sell');
        $response->assertStatus(200);

        $category = Category::factory()->create();
        $status = Status::factory()->create();
        $image = UploadedFile::fake()->create('test.png');

        $response = $this->post('/', [
            'name' => '商品',
            'price' => 1000,
            'description' => '商品説明',
            'image' => $image,
            'status_id' => $status->id,
            'category_id' => $category->id,
        ]);

        $response->assertRedirect('/');

        $this->assertDatabaseHas('items', [
            'name' => '商品',
            'price' => 1000,
            'description' => '商品説明',
            'status_id' => $status->id,
        ]);
        $this->assertDatabaseHas('item_category', [
            'item_id' => Item::where('name', '商品')->first()->id,
            'category_id' => $category->id,
        ]);
    }
}

