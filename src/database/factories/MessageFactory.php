<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    protected $model = Message::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaction_id' => Transaction::inRandomOrder()->value('id'),
            'user_id' => User::inRandomOrder()->value('id'),
            'message' => $this->faker->text(10),
            'image' => $this->faker->imageUrl(640, 480),
            'is_read' => false
        ];
    }
}
