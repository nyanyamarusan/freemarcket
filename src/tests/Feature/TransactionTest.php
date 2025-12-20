<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Evaluation;
use App\Models\User;
use App\Models\Item;
use App\Models\Message;
use App\Models\Transaction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

    public function test_user_can_see_completed_but_unevaluated_transactions_on_mypage()
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $item = Item::factory()->create([
            'user_id' => $partner->id,
        ]);

        $item2 = Item::factory()->create([
            'user_id' => $partner->id,
        ]);

        Transaction::factory()->create([
                'item_id' => $item->id,
                'seller_id' => $partner->id,
                'buyer_id' => $user->id,
                'status' => 'completed',
        ]);

        $evaluatedTransaction = Transaction::factory()->create([
                'item_id' => $item2->id,
                'seller_id' => $partner->id,
                'buyer_id' => $user->id,
                'status' => 'completed',
        ]);

        Evaluation::factory()->create([
            'transaction_id' => $evaluatedTransaction->id,
            'evaluator_id' => $user->id,
            'evaluatee_id' => $partner->id,
            'rating' => 5,
        ]);

        $response = $this->actingAs($user)->get('/mypage?page=transaction');

        $response->assertStatus(200);
        $response->assertSee('storage/item-img/' . $item->image, false);
        $response->assertDontSee('storage/item-img/' . $item2->image, false);
    }

    public function test_transaction_is_visible_when_only_partner_has_evaluated()
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
            'status' => 'completed',
        ]);
    
        Message::factory()->create([
            'transaction_id' => $transaction->id,
            'user_id' => $partner->id,
        ]);
    
        $response = $this->actingAs($user)->get('/mypage?page=transaction');
    
        $response->assertStatus(200);
        $response->assertSee('storage/item-img/' . $item->image, false);
    }

    public function test_user_can_see_unread_message_total_count_on_mypage()
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $item = Item::factory()->create([
            'user_id' => $partner->id,
        ]);
        $item2 = Item::factory()->create([
            'user_id' => $partner->id,
        ]);
    
        $transaction = Transaction::factory()->create([
            'item_id' => $item->id,
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
            'status' => 'completed',
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

    public function test_store_message_success()
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

        $response = $this->actingAs($user)
            ->post('/transaction/' . $transaction->id . '/message', [
                'message' => 'test',
            ]);
        $response = $this->get('/transaction/' . $transaction->id);
        $response->assertSee('test');
        $this->assertDatabaseHas('messages', [
            'transaction_id' => $transaction->id,
            'user_id' => $user->id,
            'message' => 'test',
        ]);
    }

        public function test_store_message_and_image_success()
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $item = Item::factory()->create();
        $image = UploadedFile::fake()->create('test.png');
        $transaction = Transaction::factory()->create([
            'item_id' => $item->id,
            'seller_id' => $partner->id,
            'buyer_id' => $user->id,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)
            ->post('/transaction/' . $transaction->id . '/message', [
                'message' => 'test',
                'image' => $image,
            ]);
        $response = $this->get('/transaction/' . $transaction->id);
        $response->assertSee('test');
        $response->assertSee($image->hashName());

        Storage::disk('public')->assertExists('message-img/' . $image->hashName());

        $this->assertDatabaseHas('messages', [
            'transaction_id' => $transaction->id,
            'user_id' => $user->id,
            'message' => 'test',
            'image' => $image->hashName(),
        ]);
    }

    public function test_validate_message_require()
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

        $response = $this->actingAs($user)
            ->post('/transaction/' . $transaction->id . '/message', [
                'message' => '',
            ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['message']);
        $errors = session('errors')->getMessages();
        $this->assertEquals('本文を入力してください', $errors['message'][0]);
    }

    public function test_validate_message_max_400()
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

        $response = $this->actingAs($user)
            ->post('/transaction/' . $transaction->id . '/message', [
                'message' => 'a'.str_repeat('a', 400),
            ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['message']);
        $errors = session('errors')->getMessages();
        $this->assertEquals('本文は400文字以内で入力してください', $errors['message'][0]);
    }

    public function test_validate_image_mimes()
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

        $response = $this->actingAs($user)
            ->post('/transaction/' . $transaction->id . '/message', [
                'message' => 'test',
                'image' => 'test.txt',
            ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['image']);
        $errors = session('errors')->getMessages();
        $this->assertEquals('「.png」または「.jpeg」形式でアップロードしてください', $errors['image'][0]);
    }

    public function test_session_message()
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

        $response = $this->actingAs($user)
            ->post('/transaction/' . $transaction->id . '/message', [
                'message' => 'test',
            ]);
        $response->assertSessionHas('session_message_' . $transaction->id, 'test');
        $response = $this->get('/transaction/' . $transaction->id);
        $response->assertSee('test');
    }

    public function test_update_message()
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

        $message = Message::factory()->create([
            'transaction_id' => $transaction->id,
            'user_id' => $user->id,
            'message' => 'test',
        ]);

        $response = $this->actingAs($user)
            ->patch('/message/' . $message->id, [
                'message' => 'test2',
            ]);
        $response->assertStatus(302);
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'user_id' => $user->id,
            'transaction_id' => $transaction->id,
            'message' => 'test2',
        ]);
        $response = $this->get('/transaction/' . $transaction->id);
        $response->assertSee('test2');
    }

        public function test_delete_message()
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

        $message = Message::factory()->create([
            'transaction_id' => $transaction->id,
            'user_id' => $user->id,
            'message' => 'test',
        ]);

        $response = $this->actingAs($user)
            ->delete('/message/' . $message->id);
        $response->assertStatus(302);
        $this->assertDatabaseMissing('messages', [
            'id' => $message->id,
        ]);
    }

    public function test_transaction_completed()
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

        $response = $this->actingAs($user)
            ->patch('/transaction/' . $transaction->id . '/completed');
        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'completed',
        ]);
    }
}

