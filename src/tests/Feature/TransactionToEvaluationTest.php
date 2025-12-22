<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionToEvaluationTest extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase;

    public function test_transaction_can_be_completed_and_evaluated()
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $seller->id]);
    
        $transaction = Transaction::factory()->create([
            'item_id' => $item->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'status' => 'in_progress',
        ]);
    
        $this->actingAs($buyer)
            ->patch("/transaction/{$transaction->id}/completed");
    
        $response = $this->actingAs($buyer)
            ->post("/evaluation/{$transaction->id}", [
                'rating' => 5,
            ]);

        $response->assertRedirect('/');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('evaluations', [
            'transaction_id' => $transaction->id,
            'rating' => 5,
            'evaluator_id' => $buyer->id,
            'evaluatee_id' => $seller->id,
        ]);
    }
}
