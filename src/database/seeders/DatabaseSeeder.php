<?php

namespace Database\Seeders;

use App\Models\Evaluation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(3)->create();
        $this->call([
            CategoriesTableSeeder::class,
            StatusesTableSeeder::class,
            ItemsTableSeeder::class,
            PaymentMethodsTableSeeder::class,
            TransactionsTableSeeder::class,
        ]);

        Message::factory(2)->create(
            [
                'transaction_id' => 1,
                'user_id' => 2,
                'created_at' => now()->subDay(),
            ]
        );
        Message::factory(2)->create(
            [
                'transaction_id' => 1,
                'user_id' => 2,
                'is_read' => true,
            ]
        );

        Message::factory(2)->create(
            [
                'transaction_id' => 2,
                'user_id' => 2,
                'created_at' => now(),
            ]
        );

        Message::factory(2)->create(
            [
                'transaction_id' => 2,
                'user_id' => 1,
            ]
        );

        Evaluation::factory(3)->create();
        Evaluation::factory(3)->create([
            'evaluator_id' => 2,
            'evaluatee_id' => 1,
        ]);
    }
}
