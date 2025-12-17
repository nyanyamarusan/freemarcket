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

    public function test_user_can_see_unread_message_count_on_mypage()
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
    
        $transaction = Transaction::factory()->create([
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
    
        $response = $this->actingAs($user)->get('/mypage?page=transaction');
    
        $response->assertStatus(200);
        $response->assertSee('3');
    }

}
