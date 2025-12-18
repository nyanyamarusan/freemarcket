<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Message;
use App\Models\Transaction;

class TransactionTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_user_can_see_in_progress_transactions_on_mypage()
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $item = Item::factory()->create([
            'user_id' => $partner->id,
        ]);

        Transaction::factory()->create([
            'item_id' => $item->id,
            'seller_id' => $partner->id,
            'buyer_id' => $user->id,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)->get('/mypage?page=transaction');

        $response->assertStatus(200);
        $response->assertSee('storage/item-img/' . $item->image, false);
    }

    public function test_user_can_see_unread_message_total_count_on_mypage()
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
    
        $transaction = Transaction::factory()->create([
            'seller_id' => $partner->id,
            'buyer_id' => $user->id,
            'status' => 'in_progress',
        ]);

        $transaction2 = Transaction::factory()->create([
            'seller_id' => $partner->id,
            'buyer_id' => $user->id,
            'status' => 'in_progress',
        ]);
    
        Message::factory()->count(3)->create([
            'transaction_id' => $transaction->id,
            'user_id' => $partner->id,
            'is_read' => false,
        ]);
    
        Message::factory()->create([
            'transaction_id' => $transaction->id,
            'user_id' => $partner->id,
            'is_read' => true,
        ]);
        
        Message::factory()->create([
            'transaction_id' => $transaction2->id,
            'user_id' => $partner->id,
            'is_read' => false,
        ]);
    
        $response = $this->actingAs($user)->get('/mypage?page=transaction');
    
        $response->assertStatus(200);
        $response->assertSee('4');
    }

    public function test_transaction_display()
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $item = Item::factory()->create([
            'user_id' => $partner->id,
        ]);

        $transaction = Transaction::factory()->create([
            'item_id' => $item->id,
            'seller_id' => $partner->id,
            'buyer_id' => $user->id,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)->get('/transaction/' . $transaction->id);
        $response->assertStatus(200);

        $response->assertSee($item->name);
        $response->assertSee(number_format($item->price));
        $response->assertSee($partner->name);
    }

    public function test_transaction_display_show_other_transaction_and_go_to_it()
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $item = Item::factory()->create([
            'user_id' => $partner->id,
        ]);

        $otherItem = Item::factory()->create([
            'user_id' => $partner->id,
        ]);

        $transaction = Transaction::factory()->create([
            'item_id' => $item->id,
            'seller_id' => $partner->id,
            'buyer_id' => $user->id,
            'status' => 'in_progress',
        ]);

        $otherTransaction = Transaction::factory()->create([
            'item_id' => $otherItem->id,
            'seller_id' => $partner->id,
            'buyer_id' => $user->id,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)->get('/transaction/' . $transaction->id);
        $response->assertStatus(200);
        $response->assertSee($otherTransaction->item->name);

        $response2 = $this->actingAs($user)->get('/transaction/' . $otherTransaction->id);
        $response2->assertStatus(200);
        $response2->assertSee($otherTransaction->item->name);
        $response2->assertSee(number_format($otherTransaction->item->price));
    }

    public function test_transactions_are_ordered_by_latest_unread_message()
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
    
        $item1 = Item::factory()->create(['user_id' => $partner->id]);
        $item2 = Item::factory()->create(['user_id' => $partner->id]);
    
        $transaction1 = Transaction::factory()->create([
            'item_id' => $item1->id,
            'seller_id' => $partner->id,
            'buyer_id' => $user->id,
            'status' => 'in_progress',
        ]);
    
        $transaction2 = Transaction::factory()->create([
            'item_id' => $item2->id,
            'seller_id' => $partner->id,
            'buyer_id' => $user->id,
            'status' => 'in_progress',
        ]);
    
        Message::factory()->create([
            'transaction_id' => $transaction1->id,
            'user_id' => $partner->id,
            'is_read' => false,
            'created_at' => now()->subMinutes(10),
        ]);
    
        Message::factory()->create([
            'transaction_id' => $transaction2->id,
            'user_id' => $partner->id,
            'is_read' => false,
            'created_at' => now(),
        ]);
    
        $response = $this->actingAs($user)
            ->get('/mypage?page=transaction');
    
        $response->assertStatus(200);
    
        $response->assertSeeInOrder([
            $item2->image,
            $item1->image,
        ]);
    }

    public function test_user_can_see_unread_message_count_on_mypage()
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();

        $item = Item::factory()->create(['user_id' => $partner->id]);
        $otherItem = Item::factory()->create(['user_id' => $partner->id]);

        $transaction = Transaction::factory()->create([
            'item_id' => $item->id,
            'seller_id' => $partner->id,
            'buyer_id' => $user->id,
            'status' => 'in_progress',
        ]);

        $otherTransaction = Transaction::factory()->create([
            'item_id' => $otherItem->id,
            'seller_id' => $partner->id,
            'buyer_id' => $user->id,
            'status' => 'in_progress',
        ]);

        Message::factory(2)->create([
            'transaction_id' => $transaction->id,
            'user_id' => $partner->id,
            'is_read' => false,
        ]);

        Message::factory()->create([
            'transaction_id' => $otherTransaction->id,
            'user_id' => $partner->id,
            'is_read' => false,
        ]);

        $response = $this->actingAs($user)
            ->get('/mypage?page=transaction');
        $response->assertStatus(200);
        $response->assertSee('2');
    }

}
