<?php

namespace Database\Factories;

use App\Models\Evaluation;
use App\Models\Transaction;
use App\Models\User;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Evaluation>
 */
class EvaluationFactory extends Factory
{
    protected $model = Evaluation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaction_id' => Transaction::factory(),
            'evaluator_id' => 1,
            'evaluatee_id' => 2,
            'rating' => fake()->numberBetween(1, 5),
        ];
    }
}
