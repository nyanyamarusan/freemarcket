<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class FreemarcketPurchaseTest extends TestCase
{
    use DatabaseTransactions;

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
                        'user_id' => $user->id,
                        'item_id' => $item->id,
                        'payment_method_id' => $paymentMethod->id,
                        'shipping_zipcode' => $purchaseData['shipping_zipcode'],
                        'shipping_address' => $purchaseData['shipping_address'],
                        'shipping_building' => $purchaseData['shipping_building'],
                    ],
                ],
            ],
        ];
    
        $response = $this->postJson('/stripe/webhook', $webhookPayload);
        $response->assertStatus(200);
        $item = Item::find($item->id);
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
}
