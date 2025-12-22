<?php

namespace Tests\Feature;

use App\Models\Evaluation;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvaluationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_show_evaluation_on_mypage(): void
    {
        $evaluatee = User::factory()->create();
        $evaluator = User::factory()->create();
        $item = Item::factory()->create();
        $seller = $evaluator;
        $buyer = $evaluatee;

        $transaction = Transaction::factory()->create([
            'item_id' => $item->id,
            'seller_id' => $seller->id,
            'buyer_id' => $buyer->id,
        ]);

        Evaluation::factory()->create([
                'transaction_id' => $transaction->id,
                'evaluatee_id' => $evaluatee->id,
                'evaluator_id' => $evaluator->id,
                'rating' => 4,
        ]);

        $response = $this->actingAs($evaluatee)->get('/mypage');
        $response->assertStatus(200);
        $response->assertSee('class="rating"', false);
    }

    public function test_no_evaluation_shows_no_stars()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get("/mypage");

        $response->assertStatus(200);
        $response->assertDontSee('class="rating"', false);
    }

        public function test_average_rating_calculation(): void
    {
        $evaluatee = User::factory()->create();
        $evaluator = User::factory()->create();
        $item = Item::factory()->create();
        $item2 = Item::factory()->create();
        $seller = $evaluator;
        $buyer = $evaluatee;

        $transaction = Transaction::factory()->create([
            'item_id' => $item->id,
            'seller_id' => $seller->id,
            'buyer_id' => $buyer->id,
        ]);

        $transaction2 = Transaction::factory()->create([
            'item_id' => $item2->id,
            'seller_id' => $seller->id,
            'buyer_id' => $buyer->id,
        ]);

        Evaluation::factory()->createMany([
            [
                'transaction_id' => $transaction->id,
                'evaluatee_id' => $evaluatee->id,
                'evaluator_id' => $evaluator->id,
                'rating' => 5,
            ],
            [
                'transaction_id' => $transaction2->id,
                'evaluatee_id' => $evaluatee->id,
                'evaluator_id' => $evaluator->id,
                'rating' => 4,
            ],
        ]);

        $response = $this->actingAs($evaluatee)->get('/mypage');
        $response->assertStatus(200);
        $response->assertSee('data-rating="5"', false);
    }

    public function test_show_evaluation_modal()
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $item = Item::factory()->create();
        $transaction = Transaction::factory()->create([
            'item_id' => $item->id,
            'seller_id' => $partner->id,
            'buyer_id' => $user->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user)->get('/transaction/' . $transaction->id);
        $response->assertStatus(200);

        $response->assertSee('取引が完了しました。');

        $response = $this->actingAs($partner)->get('/transaction/' . $transaction->id);
        $response->assertStatus(200);

        $response->assertSee('取引が完了しました。');
    }

    public function test_evaluation_modal_not_shown_for_in_progress_transaction()
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $item = Item::factory()->create();
        $transaction = Transaction::factory()->create([
            'item_id' => $item->id,
            'seller_id' => $partner->id,
            'buyer_id' => $user->id,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)->get('/transaction/' . $transaction->id);
        $response->assertStatus(200);

        $response->assertDontSee('取引が完了しました。');

        $response = $this->actingAs($partner)->get('/transaction/' . $transaction->id);
        $response->assertStatus(200);

        $response->assertDontSee('取引が完了しました。');
    }

    public function test_store_evaluation()
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $item = Item::factory()->create();
        $transaction = Transaction::factory()->create([
            'item_id' => $item->id,
            'seller_id' => $partner->id,
            'buyer_id' => $user->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user)->post('/evaluation/' . $transaction->id, [
            'rating' => 5,
        ]);
        $response->assertRedirect('/');

        $this->assertDatabaseHas('evaluations', [
            'transaction_id' => $transaction->id,
            'evaluator_id' => $user->id,
            'evaluatee_id' => $partner->id,
            'rating' => 5,
        ]);
    }

    public function test_store_evaluation_only_for_unevaluated_transaction()
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $item = Item::factory()->create();
        $transaction = Transaction::factory()->create([
            'item_id' => $item->id,
            'seller_id' => $partner->id,
            'buyer_id' => $user->id,
            'status' => 'completed',
        ]);

        Evaluation::factory()->create([
            'transaction_id' => $transaction->id,
            'evaluator_id' => $user->id,
            'evaluatee_id' => $partner->id,
            'rating' => 4,
        ]);

        $response = $this->actingAs($user)->post('/evaluation/' . $transaction->id, [
            'rating' => 5,
        ]);
        $response->assertRedirect('/');

        $this->assertDatabaseMissing('evaluations', [
            'transaction_id' => $transaction->id,
            'evaluator_id' => $user->id,
            'evaluatee_id' => $partner->id,
            'rating' => 5,
        ]);
    }
}