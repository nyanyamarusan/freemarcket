<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_id' => Item::inRandomOrder()->value('id'),
            'seller_id' => User::inRandomOrder()->value('id'),
            'buyer_id' => User::inRandomOrder()->value('id'),
            'status' => 'in_progress',
        ];
    }
}
