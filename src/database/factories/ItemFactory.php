<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Item;
use App\Models\Status;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'brand' => $this->faker->company(),
            'price' => $this->faker->numberBetween(100, 100000),
            'image' => $this->faker->imageUrl(640, 480, 'items', true),
            'user_id' => User::inRandomOrder()->value('id'),
            'status_id' => Status::factory(),
            'sold' => false,
            'description' => $this->faker->text(255),
        ];
    }
}
